<?php namespace Gdoo\Stock\Hooks;

use DB;
use Exception;

class Record01Hook
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
        $master = DB::table('stock_record01')
        ->leftJoin('warehouse', 'warehouse.id', '=', 'stock_record01.warehouse_id')
        ->leftJoin('department', 'department.id', '=', 'stock_record01.department_id')
        ->leftJoin('stock_type', 'stock_type.id', '=', 'stock_record01.type_id')
        ->leftJoin('supplier', 'supplier.id', '=', 'stock_record01.supplier_id')
        ->where('stock_record01.id', $id)
        ->first([
            'stock_record01.*',
            'department.code as department_code',
            'stock_type.code as type_code',
            'supplier.code as supplier_code',
            'warehouse.code as warehouse_code',
        ]);

        $rows = DB::table('stock_record01_data')
        ->leftJoin('product', 'product.id', '=', 'stock_record01_data.product_id')
        ->where('stock_record01_data.record01_id', $id)
        ->get(['stock_record01_data.*', 'product.code as product_code']);
        // 同步数据到外部接口
        $ret = plugin_sync_api('postRecord01', ['master' => $master, 'rows' => $rows]);
        if ($ret['error_code'] > 0) {
            abort_error($ret['msg']);
        }
        return $params;
    }
    
    public function onBeforeAbort($params) {
        $id = $params['id'];
        $master = DB::table('stock_record01')->where('id', $id)->first();
        // 检查外部接口单据是否存在
        $ret = plugin_sync_api('getVouchExist', ['table' => 'Rdrecord01', 'field' => 'cCode', 'value' => $master['sn']]);
        if ($ret['msg'] > 0) {
            abort_error('存在采购单['.$master['sn'].']无法弃审。');
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
