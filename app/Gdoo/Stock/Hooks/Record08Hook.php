<?php namespace Gdoo\Stock\Hooks;

use DB;
use Exception;

class Record08Hook
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
            if ($data['table'] == 'stock_record08_data') {
                foreach($data['data'] as $j => $row) {
                    if ($row['batch_sn']) {
                        $batch_sn = substr($row['batch_sn'], 0, 6);
                        $sn = str_split($batch_sn, 2);
                        $row['batch_date'] = date("Y-m-d", mktime(0, 0, 0, $sn[1], $sn[2], $sn[0]));
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
        $master = DB::table('stock_record08')
        ->leftJoin('warehouse', 'warehouse.id', '=', 'stock_record08.warehouse_id')
        ->leftJoin('department', 'department.id', '=', 'stock_record08.department_id')
        ->leftJoin('stock_type', 'stock_type.id', '=', 'stock_record08.type_id')
        ->where('stock_record08.id', $id)
        ->first(['stock_record08.*', 'stock_type.code as type_code', 'department.code as department_code', 'warehouse.code as warehouse_code']);

        $rows = DB::table('stock_record08_data')
        ->leftJoin('product', 'product.id', '=', 'stock_record08_data.product_id')
        ->where('stock_record08_data.record08_id', $id)
        ->get(['stock_record08_data.*', 'product.code as product_code']);
        // 同步数据到yonyou
        $ret = plugin_sync_api('postRecord08', ['master' => $master, 'rows' => $rows]);
        if ($ret['success'] == true) {
            return $params;
        } 
        abort_error($ret['msg']);
    }
    
    public function onBeforeAbort($params) {
        $id = $params['id'];
        $master = DB::table('stock_record08')->where('id', $id)->first();
        // 检查用友单据是否存在
        $ret = plugin_sync_api('getVouchExist', ['table' => 'Rdrecord08', 'field' => 'cCode', 'value' => $master['sn']]);
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
