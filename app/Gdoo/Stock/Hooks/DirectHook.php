<?php namespace Gdoo\Stock\Hooks;

use DB;
use Exception;

use Gdoo\Stock\Services\StockService;

class DirectHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) {

        $master = $params['master'];
        $warehouse = DB::table('warehouse')->find($master['warehouse_id']);

        $datas = $params['datas'];
        foreach($datas as $data) {
            if ($data['table'] == 'stock_direct_data') {
                foreach($data['data'] as $row) {
                    if ($row['product_id'] == '20226') {
                        continue;
                    }
                    // 检查库存
                    $exec = StockService::verfyInvoiceBatch($master['warehouse_id'], $row['product_id'], $row['batch_sn'], $row['poscode'], 0, 0, $row['id']);
                    if ($exec[0]['ky_num'] < $row['quantity']) {
                        $error = [];
                        $error[] = '存货编码为：'.$row['product_code'];
                        $error[] = '仓库名称为：'.$warehouse['name'];
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
        $master = DB::table('stock_direct')
        ->leftJoin('customer', 'customer.id', '=', 'stock_direct.customer_id')
        ->leftJoin('customer_tax', 'customer_tax.id', '=', 'stock_direct.tax_id')
        ->leftJoin('customer_region', 'customer_region.id', '=', 'customer.region_id')
        ->leftJoin('department', 'department.id', '=', 'customer_tax.department_id')
        ->leftJoin('warehouse', 'warehouse.id', '=', 'stock_direct.warehouse_id')
        ->leftJoin('sale_type', 'sale_type.id', '=', 'stock_direct.type_id')
        ->where('stock_direct.id', $id)
        ->first([
            'stock_direct.*',
            'sale_type.code as sale_code',
            'department.code as department_code',
            'customer_tax.code as customer_code',
            'warehouse.code as warehouse_code',
            'customer.region_id',
            'customer_region.owner_user_id as salesman_id',
            'customer.region2_id', 
            'customer.region3_id'
        ]);

        $sql = DB::table('stock_direct_data')
        ->leftJoin('product', 'product.id', '=', 'stock_direct_data.product_id')
        ->where('stock_direct_data.direct_id', $id)
        ->where('product.code', '<>', '99001')
        ->selectRaw('
            stock_direct_data.id,
            stock_direct_data.type_id,
            stock_direct_data.price,
            stock_direct_data.quantity,
            stock_direct_data.money,
            stock_direct_data.other_money,
            stock_direct_data.batch_sn,
            stock_direct_data.poscode,
            stock_direct_data.remark,
            stock_direct_data.product_id,
            product.code as product_code, 
            product.name as product_name, 
            stock_direct_data.total_weight
        ');

        $rows = DB::table('stock_direct_data')
        ->leftJoin('product', 'product.id', '=', 'stock_direct_data.product_id')
        ->where('stock_direct_data.direct_id', $id)
        ->where('product.code', '99001')
        ->selectRaw('
            max(stock_direct_data.id) as id,
            null as type_id,
            null as price,
            null as quantity,
            sum(stock_direct_data.money) as money,
            sum(stock_direct_data.other_money) as other_money,
            stock_direct_data.batch_sn,
            stock_direct_data.poscode,
            stock_direct_data.remark,
            null as product_id,
            product.code as product_code,
            product.name as product_name,
            null as total_weight
        ')
        ->groupBy('product.name', 'product.code', 'stock_direct_data.batch_sn', 'stock_direct_data.poscode', 'stock_direct_data.remark')
        ->union($sql)->get();

        // 检查库存
        foreach($rows as $row) {
            if ($row['product_code'] == '99001') {
                continue;
            }
            $exec = StockService::verfyInvoiceBatch($master['warehouse_id'], $row['product_id'], $row['batch_sn'], $row['poscode'], 0, 0, $row['id']);
            if ($exec[0]['ky_num'] < $row['quantity']) {
                abort_error('存货编码为['.$row['product_code'].']的存货库存不足。');
            }
        }

        // 同步数据到yonyou
        $ret = plugin_sync_api('postDeliveryZY', ['master' => $master, 'rows' => $rows]);
        if ($ret['success'] == true) {
            return $params;
        } 
        abort_error($ret['msg']);
    }
    
    public function onBeforeAbort($params) {
        $id = $params['id'];
        $master = DB::table('stock_direct')->where('id', $id)->first();
        // 检查用友单据是否存在
        $ret = plugin_sync_api('getVouchExist', ['table' => 'DispatchList', 'field' => 'cDLCode', 'value' => $master['sn']]);
        if ($ret['msg'] > 0) {
            abort_error('用友存在发货单(直营)['.$master['sn'].']无法弃审。');
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
