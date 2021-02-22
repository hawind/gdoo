<?php namespace Gdoo\Stock\Hooks;

use DB;
use Exception;

class Record10Hook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onBillSeqNo($params) {
        // 川南库管独立编号
        if (auth()->id() == 2177) {
            $params['rule'] = $params['rule'].'11';
        } else {
            $params['rule'] = $params['rule'].'10';
        }
        return $params;
    }

    public function onBeforePage($params) {
        // 川南库管登录
        if (auth()->id() == 2177) {
            $params['q']->whereIn('stock_record10.warehouse_id', [20001, 20047]);
        } else {
            $params['q']->whereNotIn('stock_record10.warehouse_id', [20001, 20047]);
        }
        return $params;
    }
    
    public function onBeforeStore($params) {
        $datas = $params['datas'];
        // 处理生产日期
        foreach($datas as $i => $data) {
            if ($data['table'] == 'stock_record10_data') {
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

        $master = DB::table('stock_record10')
        ->leftJoin('warehouse', 'warehouse.id', '=', 'stock_record10.warehouse_id')
        ->leftJoin('department', 'department.id', '=', 'stock_record10.department_id')
        ->leftJoin('stock_type', 'stock_type.id', '=', 'stock_record10.type_id')
        ->where('stock_record10.id', $id)
        ->first(['stock_record10.*', 'stock_type.code as type_code', 'department.code as department_code', 'warehouse.code as warehouse_code']);

        $rows = DB::table('stock_record10_data')
        ->leftJoin('product', 'product.id', '=', 'stock_record10_data.product_id')
        ->where('stock_record10_data.record10_id', $id)
        ->get(['stock_record10_data.*', 'product.code as product_code']);
        // 同步数据到yonyou
        $ret = plugin_sync_api('postRecord10', ['master' => $master, 'rows' => $rows]);
        if ($ret['success'] == true) {
            return $params;
        }

        abort_error($ret['msg']);
    }
    
    public function onBeforeAbort($params) {
        $id = $params['id'];
        $master = DB::table('stock_record10')->where('id', $id)->first();
        // 检查用友单据是否存在
        $ret = plugin_sync_api('getVouchExist', ['table' => 'Rdrecord10', 'field' => 'cCode', 'value' => $master['sn']]);
        if ($ret['msg'] > 0) {
            abort_error('用友存在产成品入库单['.$master['sn'].']无法弃审。');
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
