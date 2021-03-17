<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\Stock\Models\Cancel;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\WorkflowController;

class CancelController extends WorkflowController
{
    public $permission = ['dialog'];

    public function index()
    {
        $header = Grid::header([
            'code' => 'stock_cancel',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => 0],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '批量编辑', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'batchEdit', 'display' => $this->access['batchEdit']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Cancel::$tabs;
        $header['bys'] = Cancel::$bys;

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

    public function create($action = 'edit')
    {
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'stock_cancel';
        $header['id'] = $id;
        
        $form = Form::make($header);
        $tpl = $action == 'print' ? 'print' : 'create';
        return $this->display([
            'form' => $form,
        ], $tpl);
    }

    public function edit()
    {
        return $this->create();
    }

    public function audit()
    {
        return $this->create('audit');
    }

    public function show()
    {
        return $this->create('show');
    }

    public function print()
    {
        $id = Request::get('id'); 
        $template_id = Request::get('template_id');

        $this->layout = 'layouts.print3';
        $master = DB::table('stock_cancel as sd')
        ->leftJoin('customer as c', 'c.id', '=', 'sd.customer_id')
        ->leftJoin('customer_tax as ct', 'ct.id', '=', 'sd.tax_id')
        ->leftJoin('sale_type as st', 'st.id', '=', 'sd.type_id')
        ->selectRaw('sd.*, ct.name as tax_name, c.name as customer_name, st.name as type_name')
        ->where('sd.id', $id)
        ->first();

        $model = DB::table('stock_cancel_data as sdd')
        ->leftJoin('stock_cancel as sd', 'sd.id', '=', 'sdd.cancel_id')
        ->leftJoin('product as p', 'p.id', '=', 'sdd.product_id')
        ->leftJoin('product_unit as pu', 'pu.id', '=', 'p.unit_id')
        ->leftJoin('customer_order_type as cot', 'cot.id', '=', 'sdd.type_id')
        ->leftJoin('warehouse as w', 'w.id', '=', 'sdd.warehouse_id')
        ->where('sdd.cancel_id', $id);

        $rows = $model->selectRaw("
            sdd.*,
            p.name as product_name,
            p.spec as product_spec,
            cot.name as type_name,
            pu.name as product_unit,
            p.material_type,
            p.product_type
        ")
        ->orderBy('p.code', 'asc')
        ->get();

        $form = [
            'template' => DB::table('model_template')->where('id', $template_id)->first()
        ];

        return $this->display([
            'master' => $master,
            'rows' => $rows,
            'form' => $form,
        ], 'print/'.$template_id);
    }

    // 批量编辑
    public function batchEdit()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            DB::table('stock_cancel')->whereIn('id', $ids)->update([
                $gets['field'] => $gets['search_0'],
            ]);
            return $this->json('修改完成。', true);
        }
        $header = Grid::batchEdit([
            'code' => 'stock_cancel',
            'columns' => ['customer_id', 'tax_id'],
        ]);
        return view('batchEdit', [
            'gets' => $gets,
            'header' => $header
        ]);
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'stock_cancel', 'ids' => $ids]);
        }
    }
}
