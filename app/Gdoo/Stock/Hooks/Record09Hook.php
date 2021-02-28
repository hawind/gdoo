<?php namespace Gdoo\Stock\Hooks;

use DB;
use Exception;

use Gdoo\Stock\Services\StockService;

class Record09Hook
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
            if ($data['table'] == 'stock_record09_data') {
                foreach($data['data'] as $row) {
                    // 检查库存
                    $exec = StockService::verfyInvoiceBatch($master['warehouse_id'], $row['product_id'], $row['batch_sn'], $row['poscode'], 0, $row['id'], 0);
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

        $master = DB::table('stock_record09')
        ->leftJoin('warehouse', 'warehouse.id', '=', 'stock_record09.warehouse_id')
        ->leftJoin('department', 'department.id', '=', 'stock_record09.department_id')
        ->leftJoin('stock_type', 'stock_type.id', '=', 'stock_record09.type_id')
        ->where('stock_record09.id', $id)
        ->first(['stock_record09.*', 'stock_type.code as type_code', 'department.code as department_code', 'warehouse.code as warehouse_code']);

        $rows = DB::table('stock_record09_data')
        ->leftJoin('product', 'product.id', '=', 'stock_record09_data.product_id')
        ->where('stock_record09_data.record09_id', $id)
        ->selectRaw('
            stock_record09_data.*,
            product.code as product_code,
            product.weight * stock_record09_data.quantity as total_weight
        ')
        ->get();
        $master['total_weight'] = $rows->sum('total_weight');

        // 检查库存
        foreach($rows as $row) {
            $exec = StockService::verfyInvoiceBatch($master['warehouse_id'], $row['product_id'], $row['batch_sn'], $row['poscode'], 0, $row['id'], 0);
            if ($exec[0]['ky_num'] < $row['quantity']) {
                abort_error('存货编码为['.$row['product_code'].']的存货库存不足。');
            }
        }

        if ($master['type_id'] == 2) {
            $post_type = 'postSampleDelivery';
        } else {
            $post_type = 'postRecord09';
        }
        // 同步数据到外部接口
        $ret = plugin_sync_api($post_type, ['master' => $master, 'rows' => $rows]);
        if ($ret['success'] == true) {
            return $params;
        }
        abort_error($ret['msg']);
    }
    
    public function onBeforeAbort($params) {
        $id = $params['id'];

        $master = DB::table('stock_record09')->where('id', $id)->first();
        // 检查外部接口单据是否存在
        if ($master['type_id'] == 2) {
            $ret = plugin_sync_api('getVouchExist', ['table' => 'DispatchList', 'field' => 'cDLCode', 'value' => $master['sn']]);
            if ($ret['msg'] > 0) {
                abort_error('用友存在样品申请单['.$master['sn'].']无法弃审。');
            }
        } else {
            $ret = plugin_sync_api('getVouchExist', ['table' => 'Rdrecord09', 'field' => 'cCode', 'value' => $master['sn']]);
            if ($ret['msg'] > 0) {
                abort_error('用友存在其他出库单['.$master['sn'].']无法弃审。');
            }
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
