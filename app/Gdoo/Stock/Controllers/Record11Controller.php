<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\Stock\Models\Record11;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\AuditController;

class Record11Controller extends AuditController
{
    public $permission = ['dialog'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'stock_record11',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action'  => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Record11::$tabs;
        $header['bys'] = Record11::$bys;

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
        $header['code'] = 'stock_record11';
        $header['id'] = $id;
        
        $form = Form::make($header);
        $tpl = $action == 'print' ? 'print' : 'create';
        return $this->display([
            'form' => $form,
        ], $tpl);
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
        $id = Request::get('id');
        $template_id = Request::get('template_id');
        if ($template_id == 115) {

            $this->layout = 'layouts.print3';

            $master = DB::table('stock_record11 as m')->where('m.id', $id)
            ->leftJoin('stock_type as st', 'st.id', '=', 'm.type_id')
            ->leftJoin('department', 'department.id', '=', 'm.department_id')
            ->leftJoin('warehouse', 'warehouse.id', '=', 'm.warehouse_id')
            ->selectRaw('m.*, st.name as type_name, warehouse.name as warehouse_name, department.name as department_name')
            ->first();

            $rows = DB::table('stock_record11_data as d')
            ->leftJoin('stock_record11 as m', 'm.id', '=', 'd.record11_id')
            ->leftJoin('product as p', 'p.id', '=', 'd.product_id')
            ->leftJoin('product_unit as pu', 'pu.id', '=', 'p.unit_id')
            ->leftJoin('stock_type as st', 'st.id', '=', 'm.type_id')
            ->where('m.id', $id)
            ->selectRaw('
                d.*,
                p.name as product_name,
                p.code as product_code,
                p.spec as product_spec,
                st.name as type_name,
                pu.name as product_unit
            ')
            ->orderBy('p.code', 'asc')
            ->get();

            $form = [
                'template' => DB::table('model_template')->where('id', $template_id)->first()
            ];

            $tpl = $this->display([
                'master' => $master,
                'rows' => $rows,
                'form' => $form,
            ], 'print/'.$template_id);
            return $tpl;

        } else {
            $this->layout = 'layouts.print2';
            $tpl = $this->createAction('print');
            print_prince($tpl);
        }
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'stock_record11', 'ids' => $ids]);
        }
    }
}
