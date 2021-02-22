<?php namespace Gdoo\Order\Hooks;

use DB;
use Exception;
use Gdoo\Order\Models\CustomerOrder;

class OrderHook
{
    public function onBeforeForm($params) {
        $permission = $params['permission'];
        $data = $permission['customer_order_data'];

        // 是客户不能编辑产品类型
        $role_ids = [2, 83, 84, 85];
        if (in_array(auth()->user()->role_id, $role_ids)) {
            // 不能编辑类型
            unset($data['type_id']);
            // 不能编辑价格
            unset($data['price']);
            // 不编辑实发数量
            unset($data['delivery_quantity']);
        }

        $permission['customer_order_data'] = $data;
        $params['permission'] = $permission;
        return $params;
    }

    public function onAfterForm($params) {
        $options = $params['options'];
        if ($options['action'] == 'print') {
            return $params;
        }
        $tpls = $params['tpls'];
        //$tpls[0]['tpl'] = $tpls[0]['tpl'].view('order/bank');
        $params['tpls'] = $tpls;
        return $params;
    }

    public function onBeforeStore($params) {

        $master = $params['master'];

        // 只限制内销和直营
        if (in_array($master['type_id'], [1, 3])) {
            // 客户和区域经理和业务员限制下单数和倍数
            $role_ids = [2, 83, 84];

            // 成品数量
            $product_quantity = $product_money = 0;

            if (in_array(auth()->user()->role_id, $role_ids)) {

                $_product_ids = DB::table('product')
                ->whereRaw('product_type = 1')
                ->pluck('id', 'id')->toArray();

                $quantitys = [];
                $product_ids = [];
                foreach($params['datas'] as $i => $datas) {
                    if ($datas['table'] == 'customer_order_data') {
                        foreach($datas['data'] as $j => $row) {
                            $quantitys[$row['product_id']][] = $row['quantity'];
                            $product_ids[] = $row['product_id'];

                            // 获取产成品数量
                            if (isset($_product_ids[$row['product_id']])) {
                                if ($row['type_id'] == 1) {
                                    $product_quantity += $row['quantity'];
                                    $product_money += $row['money'];
                                }
                            }

                        }
                    }
                }

                $products = DB::table('product')
                ->whereIn('id', $product_ids)
                ->whereRaw('scale_quantity > 0 or mini_quantity > 0')
                ->get()->keyBy('id');

                foreach($quantitys as $_product_id => $_products) {
                    $product = $products[$_product_id];
                    foreach($_products as $_quantity) {
                        // 检查倍数
                        if ($product['scale_quantity'] > 0) {
                            $has = $_quantity % $product['scale_quantity'];
                            if ($has > 0) {
                                abort_error($product['name'].' - '.$product['spec'].' 下单数量必须是['.$product['scale_quantity'].']的倍数');
                            }
                        }
                        // 检查最低下单数
                        if ($product['mini_quantity'] > 0) {
                            if ($_quantity < $product['mini_quantity']) {
                                abort_error($product['name'].' - '.$product['spec'].' 最低下单数不能小于['.$product['mini_quantity'].']');
                            }
                        }
                    }
                }
            }
        }

        $params['master'] = $master;

        $fee = 0;
        $amount = 0;

        foreach($params['datas'] as $i => $datas) {
            if ($datas['table'] == 'customer_order_data') {

                foreach($datas['data'] as $j => $row) {

                    // 产品类型是赠品
                    if ($row['type_id'] == 2) {
                        if (empty($row['promotion_sn'])) {
                            abort_error('赠品必须有编号。');
                        }
                        // 赠品修改客户促销开票单位
                        if ($row['fee_src_id'] > 0) {
                            // 检查赠品是否在其他订单里已经使用过了
                            $count = DB::table('customer_order_data')
                            ->where('order_id', '<>', $row['order_id'])
                            ->where('fee_src_id', $row['fee_src_id'])
                            ->count('id');
                            if ($count == 0) {
                                DB::table('promotion')->where('id', $row['fee_src_id'])->update([
                                    'tax_id' => $master['tax_id'],
                                ]);
                            }
                        }
                    }

                    // 是外贸订单检查生产批号
                    if ($master['type_id'] == 2) {
                        $row = get_batch_sn($row);
                        if (empty($row['batch_sn'])) {
                            abort_error('外贸订单必须填写生产批号。');
                        }
                    }

                    // 判断费用比例
                    $money = floatval($row['money']);
                    if ($row['product_code'] == '99001') {
                        $fee += $money;
                    } else {
                        $amount += $money;
                    }

                    $datas['data'][$j] = $row;
                }
                $params['datas'][$i] = $datas;
            }
        }

        // 费用不能大于20%
        /*
        $feeAmount = $amount * 0.2;
        if (abs($fee) > $feeAmount) {
            abort_error('费用金额不能大于'.$feeAmount);
        }
        */
        
        return $params;
    }

    /**
     * 回退流程
     */
    public function onBeforeReturn($params) {
        return $params;
    }

    public function onAfterStore($params) {
        foreach($params['datas'] as $datas) {
            if ($datas['table'] == 'customer_order_data') {
                foreach($datas['data'] as $row) {
                    // 计算费用使用情况
                    if ($row['fee_data_id'] > 0) {
                        $use_money = DB::table('customer_order_data')->where('fee_data_id', $row['fee_data_id'])->sum('money');
                        $cost = DB::table('customer_cost_data')->where('id', $row['fee_data_id'])->first();
                        $cost['use_money'] = abs($use_money);
                        $cost['remain_money'] = $cost['money'] - $cost['use_money'];
                        DB::table('customer_cost_data')->where('id', $row['fee_data_id'])->update($cost);
                    }
                }

                // 费用删除时重新计算使用
                foreach($datas['deleteds'] as $row) {
                    // 计算费用使用情况
                    if ($row['fee_data_id'] > 0) {
                        $use_money = DB::table('customer_order_data')->where('fee_data_id', $row['fee_data_id'])->sum('money');
                        $cost = DB::table('customer_cost_data')->where('id', $row['fee_data_id'])->first();
                        $cost['use_money'] = abs($use_money);
                        $cost['remain_money'] = $cost['money'] - $cost['use_money'];
                        DB::table('customer_cost_data')->where('id', $row['fee_data_id'])->update($cost);
                    }
                }
                
            }
        }
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }

    public function onAfterDelete($params) {

        foreach($params['datas'] as $datas) {
            if ($datas['table'] == 'customer_order_data') {
                foreach($datas['data'] as $row) {
                    // 计算费用使用情况
                    if ($row['fee_data_id'] > 0) {
                        $use_money = DB::table('customer_order_data')->where('fee_data_id', $row['fee_data_id'])->sum('money');
                        $cost = DB::table('customer_cost_data')->where('id', $row['fee_data_id'])->first();
                        $cost['use_money'] = abs($use_money);
                        $cost['remain_money'] = $cost['money'] - $cost['use_money'];
                        DB::table('customer_cost_data')->where('id', $row['fee_data_id'])->update($cost);
                    }
                }
            }
        }

        return $params;
    }
}
