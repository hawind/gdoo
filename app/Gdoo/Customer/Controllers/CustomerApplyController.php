<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Customer\Models\CustomerApply;

use Gdoo\Index\Controllers\WorkflowController;

class CustomerApplyController extends WorkflowController
{
    public $permission = ['dialog'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'customer_apply',
            'referer' => 1,
            'sort' => 'customer_apply.id',
            'order' => 'asc',
            'search' => ['by' => 'todo'],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '导出', 'icon' => 'fa-mail-reply', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = CustomerApply::$tabs;
        $header['bys'] = CustomerApply::$bys;

        $search = $header['search_form'];
        $query = $search['query'];

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

            if ($query['by']) {
                if ($query['by'] == 'end') {
                    $model->where('customer_apply.status', 1);
                } else {
                    $model->where('customer_apply.status', '<>', 1);
                }
            }

            // 客户权限
            $region = regionCustomer('customer_apply');
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header, function($item) {
                return $item;
            });
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction($action = 'create')
    {
        $id = (int)Request::get('id');

        // 客户权限
        $header['region'] = ['field' => 'id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        $header['code'] = 'customer_apply';
        $header['id'] = $id;
        $header['action'] = $action;

        $form = Form::make($header);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    public function editAction()
    {
        return $this->createAction('edit');
    }

    public function auditAction()
    {
        return $this->createAction('audit');
    }

    public function showAction()
    {
        return $this->createAction('show');
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'customer_apply', 'ids' => $ids]);
        }
    }
}
