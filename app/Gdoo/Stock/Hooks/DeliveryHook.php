<?php namespace Gdoo\Stock\Hooks;

use Log;
use DB;
use Exception;

use Gdoo\Stock\Services\StockService;

class DeliveryHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) {
        $datas = $params['datas'];
        foreach($datas as $data) {
            if ($data['table'] == 'stock_delivery_data') {
                foreach($data['data'] as $row) {
                    if ($row['product_id'] == '20226') {
                        continue;
                    }
                    // 检查库存
                    $exec = StockService::verfyInvoiceBatch($row['warehouse_id'], $row['product_id'], $row['batch_sn'], $row['poscode'], $row['id'], 0, 0);
                    if ($exec[0]['ky_num'] < $row['quantity']) {
                        $error = [];
                        $error[] = '存货编码为：'.$row['product_code'];
                        $error[] = '仓库名称为：'.$row['warehouse_id_name'];
                        $error[] = '批次为：'.$row['batch_sn'];
                        $error[] = '货位为：'.$row['poscode'];
                        $error[] = '发货数量：'.$row['quantity'];
                        $error[] = '可用量为：'.$exec[0]['ky_num'];
                        abort_error(join("<br>", $error));
                    }
                }
            }
        }
        return $params;
    }

    public function onBeforeAudit($params) {
        $id = $params['id'];
        $master = DB::table('stock_delivery')
        ->leftJoin('customer', 'customer.id', '=', 'stock_delivery.customer_id')
        ->leftJoin('customer_tax', 'customer_tax.id', '=', 'stock_delivery.tax_id')
        ->leftJoin('customer_region', 'customer_region.id', '=', 'customer.region_id')
        ->leftJoin('department', 'department.id', '=', 'customer_tax.department_id')
        ->leftJoin('sale_type', 'sale_type.id', '=', 'stock_delivery.type_id')
        ->where('stock_delivery.id', $id)
        ->first([
            'stock_delivery.*', 
            'sale_type.code as sale_code', 
            'department.code as department_code',
            'customer_tax.code as customer_code',
            'customer.region_id',
            'customer_region.owner_user_id as salesman_id',
            'customer.region2_id', 
            'customer.region3_id'
        ]);

        $sql = "select sdd.id,sdd.type_id,sdd.price,sdd.quantity,sdd.money,sdd.other_money,
        sdd.batch_sn,
        sdd.poscode,
        sdd.remark,
        sdd.product_id,
        sdd.warehouse_id, 
        product.code as product_code, 
        product.name as product_name, 
        sdd.total_weight,
        warehouse.code as warehouse_code,

        null as fee_category_name,
        null as fee_category_id,
        null as fee_src_type_id,
        null as fee_src_sn,
        null as fee_src_id,
        null as promotion_sn,
        null as row_index

        from stock_delivery_data as sdd
        left Join product on product.id = sdd.product_id
        left Join warehouse on warehouse.id = sdd.warehouse_id
        where sdd.delivery_id = ".$id."
        and product.code <> '99001'

        union

        select t.* from (
            select sdd.id,
            null as type_id,
            null as price,
            null as quantity,
			SUM(sdd.money) OVER(PARTITION BY product.code) as money,
			SUM(sdd.other_money) OVER(PARTITION BY product.code) as other_money,
            sdd.batch_sn,
            sdd.poscode,
            sdd.remark,
            null as product_id,
            null as warehouse_id,
            product.code as product_code,
            product.name as product_name,
            null as total_weight,
            null as warehouse_code,
            ccc.name as fee_category_name,
            sdd.fee_category_id as fee_category_id,
            sdd.fee_src_type_id as fee_src_type_id,
            sdd.fee_src_sn as fee_src_sn,
            sdd.fee_src_id as fee_src_id,
            sdd.promotion_sn as promotion_sn,
            row_number() over(partition by product.code order by sdd.id desc) row_index
            from stock_delivery_data as sdd
            left Join product on product.id = sdd.product_id
            left Join customer_cost_category as ccc on ccc.id = sdd.fee_category_id
            where sdd.delivery_id = ".$id." and product.code = '99001'
        ) t where t.row_index = 1";
        $rows = DB::select($sql);

        // 检查库存
        foreach($rows as $row) {
            if ($row['product_code'] == '99001') {
                continue;
            }
            // 检查库存
            $exec = StockService::verfyInvoiceBatch($row['warehouse_id'], $row['product_id'], $row['batch_sn'], $row['poscode'], $row['id'], 0, 0);
            if ($exec[0]['ky_num'] < $row['quantity']) {
                abort_error('存货编码为['.$row['product_code'].']的存货库存不足。');
            }
        }

        // 同步数据到yonyou
        $ret = plugin_sync_api('postDelivery', ['master' => $master, 'rows' => $rows]);
        if ($ret['success'] == true) {
            return $params;
        } 
        abort_error($ret['msg']);
    }
    
    public function onBeforeAbort($params) {
        $id = $params['id'];
        $master = DB::table('stock_delivery')->where('id', $id)->first();
        // 检查用友单据是否存在
        $ret = plugin_sync_api('getVouchExist', ['table' => 'DispatchList', 'field' => 'cDLCode', 'value' => $master['sn']]);
        if ($ret['msg'] > 0) {
            abort_error('用友存在发货单['.$master['sn'].']无法弃审。');
        }
        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
    
}
