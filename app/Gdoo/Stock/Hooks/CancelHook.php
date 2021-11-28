<?php namespace Gdoo\Stock\Hooks;

use DB;
use Exception;

class CancelHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) {
        $datas = $params['datas'];
        // 处理生产日期
        foreach($datas as $i => $data) {
            if ($data['table'] == 'stock_cancel_data') {
                foreach($data['data'] as $j => $row) {
                    if ($row['batch_sn']) {
                        $batch_sn = substr($row['batch_sn'], 0, 6);
                        $sn = str_split($batch_sn, 2);
                        $row['batch_date'] = date("Y-m-d", mktime(0, 0, 0, $sn[1], $sn[2], $sn[0]));
                    }
                    if ($row['quantity'] >= 0) {
                        abort_error('产品编码['.$row['product_code'].']数量必须是负数。');
                    }
                    $data['data'][$j] = $row;
                }
                $datas[$i] = $data;
            }
        }
        $params['datas'] = $datas;
        return $params;
    }

    public function onBeforeAudit($params) {
        $id = $params['id'];
        $master = DB::table('stock_cancel')
        ->leftJoin('customer', 'customer.id', '=', 'stock_cancel.customer_id')
        ->leftJoin('customer_tax', 'customer_tax.id', '=', 'stock_cancel.tax_id')
        ->leftJoin('customer_region', 'customer_region.id', '=', 'customer.region_id')
        ->leftJoin('department', 'department.id', '=', 'customer_tax.department_id')
        ->leftJoin('sale_type', 'sale_type.id', '=', 'stock_cancel.type_id')
        ->where('stock_cancel.id', $id)
        ->first([
            'stock_cancel.*', 
            'sale_type.code as sale_code',
            'department.code as department_code',
            'customer_tax.code as customer_code',
            'customer.region_id', 
            'customer_region.owner_user_id as salesman_id'
        ]);
        
        $sql = "select d.id,d.type_id,d.price,d.quantity,d.money,d.other_money,
        d.batch_sn,
        d.poscode,
        d.remark,
        d.product_id,
        d.warehouse_id, 
        product.code as product_code, 
        product.name as product_name, 
        d.total_weight,
        warehouse.code as warehouse_code,

        null as fee_category_name,
        null as fee_category_id,
        null as fee_src_type_id,
        null as fee_src_sn,
        null as fee_src_id,
        null as promotion_sn,
        null as row_index

        from stock_cancel_data as d
        left Join product on product.id = d.product_id
        left Join warehouse on warehouse.id = d.warehouse_id
        where d.cancel_id = ".$id."
        and product.code <> '99001'

        union

        select t.* from (
            select d.id,
            null as type_id,
            null as price,
            null as quantity,
			SUM(d.money) OVER(PARTITION BY product.code) as money,
			SUM(d.other_money) OVER(PARTITION BY product.code) as other_money,
            d.batch_sn,
            d.poscode,
            d.remark,
            null as product_id,
            null as warehouse_id,
            product.code as product_code,
            product.name as product_name,
            null as total_weight,
            null as warehouse_code,
            ccc.name as fee_category_name,
            d.fee_category_id as fee_category_id,
            d.fee_src_type_id as fee_src_type_id,
            d.fee_src_sn as fee_src_sn,
            d.fee_src_id as fee_src_id,
            d.promotion_sn as promotion_sn,
            row_number() over(partition by product.code order by d.id desc) row_index
            from stock_cancel_data as d
            left Join product on product.id = d.product_id
            left Join customer_cost_category as ccc on ccc.id = d.fee_category_id
            where d.cancel_id = ".$id." and product.code = '99001'
        ) t where t.row_index = 1";
        $rows = DB::select($sql);

        // 同步数据到外部接口
        $ret = plugin_sync_api('postCancelOrder', ['master' => $master, 'rows' => $rows]);
        if ($ret['error_code'] > 0) {
            abort_error($ret['msg']);
        }
        return $params;
    }
    
    public function onBeforeAbort($params) {
        $id = $params['id'];
        $master = DB::table('stock_cancel')->where('id', $id)->first();
        // 检查外部接口单据是否存在
        $ret = plugin_sync_api('getVouchExist', ['table' => 'DispatchList', 'field' => 'cDLCode', 'value' => $master['sn']]);
        if ($ret['msg'] > 0) {
            abort_error('存在退货申请['.$master['sn'].']无法弃审。');
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
