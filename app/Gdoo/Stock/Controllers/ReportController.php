<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Auth;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\User\Models\User;
use Gdoo\Produce\Models\Plan;
use Gdoo\Produce\Models\Formula;

use Gdoo\Stock\Services\StockService;

use Gdoo\Index\Controllers\DefaultController;

class ReportController extends DefaultController
{
    public $permission = [];

    // 库存明细表
    public function stockDetailAction()
    {
        $sdate = date('Y-m-01');
        $edate = date('Y-m-d');
        $search = search_form([
            'advanced' => 0,
        ], [
            ['form_type' => 'dialog', 'name' => '仓库', 'field' => 'warehouse_id', 'options' => ['url' => 'stock/warehouse/dialog', 'query' => ['multi'=>0]]],
            ['form_type' => 'dialog', 'name' => '产品', 'field' => 'product_id', 'options' => ['url' => 'product/product/dialog', 'query' => ['multi'=>0]]],
            ['form_type' => 'text', 'name' => '批号', 'field' => 'batch_sn', 'options' => []],
            ['form_type' => 'select', 'name' => '内销/外销', 'field' => 'type', 'options' => [['id'=>'内销','name'=>'内销'],['id'=>'外销','name'=>'外销']]],
            //['form_type' => 'select', 'name' => '是否统计批号', 'field' => 'batch', 'value' => 0, 'options' => [['id'=>1,'name'=>'是'],['id'=>0,'name'=>'否']]],
            ['form_type' => 'date2', 'name' => '单据日期', 'field' => 'date', 'value' => [$sdate, $edate], 'options' => []],
        ], 'model');

        $query = $search['query'];

        if (Request::method() == 'POST') {
            $fields = [];
            foreach($search['forms']['field'] as $i => $field) {
                $fields[$field] = $search['forms']['search'][$i];
            }
            $rows = [];
            if ($query['filter'] == 1) {
                $rows = StockService::reportOrderStockDetail(
                    $fields['warehouse_id'],
                    $fields['product_id'],
                    $fields['batch_sn'],
                    $fields['type'],
                    $fields['date'][0],
                    $fields['date'][1],
                    auth()->id(),
                    $fields['bmj']
                );
                $QmNum = 0;
                foreach($rows as $i => $row) {
                    if ($row['bill_name'] == '期初') {
                        $QmNum = (float)$row['qm_num'];
                    } else {
                        $QmNum = ((float)$row['rk_num'] - (float)$row['ck_num']) + $QmNum;
                    }
                    $row['qm_num'] = $QmNum;
                    $row['id'] = $i + 1;
                    $rows[$i] = $row;
                }
            }
            return $this->json($rows, true);
        }
        $search['table'] = 'material_plan';
        return $this->display([
            'search' => $search, 
            'query' => $query,
        ]);
    }

    // 库存汇总表
    public function stockTotalAction()
    {
        $search = search_form([
            'advanced' => 0,
        ], [
            ['form_type' => 'dialog', 'name' => '仓库', 'field' => 'warehouse_id', 'options' => ['url' => 'stock/warehouse/dialog', 'query' => ['multi'=>0]]],
            ['form_type' => 'text', 'name' => '存货编码', 'field' => 'product_code', 'options' => []],
            ['form_type' => 'select', 'name' => '内销/外销', 'field' => 'type', 'options' => [['id'=>'1','name'=>'内销'],['id'=>'2','name'=>'外销']]],
            ['form_type' => 'date2', 'name' => '生产日期', 'field' => 'date', 'value' => [], 'options' => []],
            ['form_type' => 'select', 'name' => '统计批号', 'field' => 'batch', 'value' => 1, 'options' => [['id'=>1,'name'=>'是'],['id'=>0,'name'=>'否']]],
            
        ], 'model');

        $query = $search['query'];

        if (Request::method() == 'POST') {
            $fields = [];
            foreach($search['forms']['field'] as $i => $field) {
                $fields[$field] = $search['forms']['search'][$i];
            }
            $rows = [];
            if ($query['filter'] == 1) {
                /*
                $rows = DB::select('EXEC P_ReportOrderStockTotal ?,?,?,?,?,?,?,?', [
                    $fields['warehouse_id'],
                    $fields['product_code'],
                    $fields['type'],
                    $fields['date'][0],
                    $fields['date'][1],
                    auth()->id(),
                    $fields['batch'],
                    $fields['bmj'],
                ]);
                */
                $rows = StockService::reportOrderStockTotal(
                    $fields['warehouse_id'],
                    $fields['product_code'],
                    $fields['type'],
                    $fields['date'][0],
                    $fields['date'][1],
                    auth()->id(),
                    $fields['batch'],
                    $fields['bmj']
                );
            }
            return $this->json($rows, true);
        }
        $search['table'] = 'material_plan';
        return $this->display([
            'search' => $search, 
            'query' => $query,
        ]);
    }

    // 进销存库存汇总表
    public function stockInOutAction()
    {
        $sdate = date('Y-m-01');
        $edate = date('Y-m-d');
        $search = search_form([
            'advanced' => 0,
        ], [
            ['form_type' => 'dialog', 'name' => '仓库', 'field' => 'warehouse_id', 'options' => ['url' => 'stock/warehouse/dialog', 'query' => ['multi'=>0]]],
            ['form_type' => 'dialog', 'name' => '产品', 'field' => 'product_id', 'options' => ['url' => 'product/product/dialog', 'query' => ['multi'=>0]]],
            ['form_type' => 'text', 'name' => '批号', 'field' => 'batch_sn', 'options' => []],
            ['form_type' => 'select', 'name' => '内销/外销', 'field' => 'type', 'options' => [['id'=>'内销','name'=>'内销'],['id'=>'外销','name'=>'外销']]],
            ['form_type' => 'select', 'name' => '统计批号', 'field' => 'batch', 'value' => 0, 'options' => [['id'=>1,'name'=>'是'],['id'=>0,'name'=>'否']]],
            ['form_type' => 'date2', 'name' => '单据日期', 'field' => 'date', 'value' => [$sdate, $edate], 'options' => []],
        ], 'model');

        $query = $search['query'];

        if (Request::method() == 'POST') {
            $fields = [];
            foreach($search['forms']['field'] as $i => $field) {
                $fields[$field] = $search['forms']['search'][$i];
            }

            $rows = [];
            if ($query['filter'] == 1) {
                $rows = StockService::reportOrderStockInOut(
                    $fields['warehouse_id'],
                    $fields['product_id'],
                    $fields['batch_sn'],
                    $fields['type'],
                    $fields['date'][0],
                    $fields['date'][1],
                    auth()->id(),
                    $fields['batch'],
                    $fields['bmj']
                );
            }
            return $this->json($rows, true);
        }
        $search['table'] = 'material_plan';
        return $this->display([
            'search' => $search, 
            'query' => $query,
        ]);
    }
}
