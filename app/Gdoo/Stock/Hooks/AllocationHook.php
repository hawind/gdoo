<?php namespace Gdoo\Stock\Hooks;

use DB;
use Exception;

class AllocationHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) {
        return $params;
    }

    public function onBeforeAudit($params) {
        $id = $params['id'];
        $master = DB::table('stock_allocation as sa')
        ->leftJoin('warehouse as w', 'w.id', '=', 'sa.in_warehouse_id')
        ->leftJoin('warehouse as w2', 'w2.id', '=', 'sa.out_warehouse_id')
        ->leftJoin('department as d', 'd.id', '=', 'sa.in_department_id')
        ->leftJoin('department as d2', 'd2.id', '=', 'sa.out_department_id')
        ->leftJoin('stock_type as st', 'st.id', '=', 'sa.in_type_id')
        ->leftJoin('stock_type as st2', 'st2.id', '=', 'sa.out_type_id')
        ->where('sa.id', $id)
        ->selectRaw('
            sa.*,
            st.code as in_type_code,
            st2.code as out_type_code,
            d.code as in_department_code,
            d2.code as out_department_code,
            w.code as in_warehouse_code,
            w2.code as out_warehouse_code
        ')
        ->first();

        $rows = DB::table('stock_allocation_data')
        ->leftJoin('product', 'product.id', '=', 'stock_allocation_data.product_id')
        ->where('stock_allocation_data.allocation_id', $id)
        ->get(['stock_allocation_data.*', 'product.code as product_code']);
        // 同步数据到yonyou
        $ret = plugin_sync_api('postTransVouch', ['master' => $master, 'rows' => $rows]);
        if ($ret['success'] == true) {
            return $params;
        } 
        abort_error($ret['msg']);
    }
    
    public function onBeforeAbort($params) {
        $id = $params['id'];
        $master = DB::table('stock_allocation')->where('id', $id)->first();
        // 检查用友单据是否存在
        $ret = plugin_sync_api('getVouchExist', ['table' => 'TransVouch', 'field' => 'cTVCode', 'value' => $master['sn']]);
        if ($ret['msg'] > 0) {
            abort_error('用友存在其他入库单['.$master['sn'].']无法弃审。');
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
