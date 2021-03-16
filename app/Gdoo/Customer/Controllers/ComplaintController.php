<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Auth;
use Paginator;

use Gdoo\Customer\Models\CustomerComplaint;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\WorkflowController;

class ComplaintController extends WorkflowController
{
    public $permission = ['dialog'];

    public function indexAction()
    {
        // 客户权限
        $region = regionCustomer('customer_id_customer');

        $header = Grid::header([
            'code' => 'customer_complaint',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $search = $header['search_form'];
        $query = $search['query'];

        $cols = $header['cols'];
        
        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = CustomerComplaint::$tabs;
        $header['bys'] = CustomerComplaint::$bys;

        if (Request::method() == 'POST') {

            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }

            $model->orderBy($header['sort'], $header['order']);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $model->select($header['select']);

            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction($action = 'edit')
    {
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'customer_complaint';
        $header['id'] = $id;

        // 客户权限
        $header['region'] = ['field' => 'customer_id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        $form = Form::make($header);
        $row = $form['row'];
        $tpl = $action == 'print' ? 'print' : 'create';

        if ($action == 'print') {
            $this->layout = 'layouts.print_'.$form['print_type'];
        }
        
        return $this->display(['form' => $form], $tpl);
    }

    public function editAction()
    {
        return $this->createAction();
    }

    public function showAction()
    {
        return $this->createAction('show');
    }

    public function printAction()
    {
        return $this->createAction('print');
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            return Form::remove(['code' => 'customer_complaint', 'ids' => $id]);
        }
    }
}
