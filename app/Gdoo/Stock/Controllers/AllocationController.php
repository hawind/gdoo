<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\Stock\Models\Allocation;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Stock\Services\StockService;

use Gdoo\Index\Controllers\WorkflowController;

class AllocationController extends WorkflowController
{
    public $permission = ['dialog', 'logistics', 'stockSelect'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'stock_allocation',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

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

        $header['buttons'] = [
            //['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Allocation::$tabs;
        $header['bys'] = Allocation::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction($action = 'edit')
    {
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'stock_allocation';
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

    public function auditAction()
    {
        return $this->createAction('audit');
    }

    public function showAction()
    {
        return $this->createAction('show');
    }

    public function printAction()
    {
        $id = Request::get('id');
        $template_id = Request::get('template_id');

        $this->layout = 'layouts.print3';
        $master = DB::table('stock_allocation as m')
        ->where('m.id', $id)
        ->leftJoin('warehouse as wo', 'wo.id', '=', 'm.out_warehouse_id')
        ->leftJoin('warehouse as wi', 'wi.id', '=', 'm.in_warehouse_id')
        ->selectRaw('m.*, wi.name as in_warehouse_name, wo.name as out_warehouse_name, wi.code as in_warehouse_code, wo.code as out_warehouse_code')
        ->first();

        $rows = DB::table('stock_allocation_data as d')
        ->leftJoin('stock_allocation as m', 'm.id', '=', 'd.allocation_id')
        ->leftJoin('product as p', 'p.id', '=', 'd.product_id')
        ->leftJoin('product_unit as pu', 'pu.id', '=', 'p.unit_id')
        ->where('m.id', $id)
        ->selectRaw('
            d.*,
            p.name as product_name,
            p.code as product_code,
            p.spec as product_spec,
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
    }

    // 选择库存
    public function stockSelectAction()
    {
        $search = search_form(['advanced' => ''], [
            ['form_type' => 'text', 'name' => '产品名称', 'field' => 'name'],
            ['form_type' => 'text', 'name' => '产品编码', 'field' => 'code']
        ], 'model');
        $query = $search['query'];
        if (Request::method() == 'POST') {
            $fields = [];
            foreach($search['forms']['field'] as $i => $field) {
                $fields[$field] = $search['forms']['search'][$i];
            }
            if($fields['name']) {
                $query['value'] = $fields['name'];
            }
            if($fields['code']) { 
                $query['value'] = $fields['code'];
            }
            
            $rows = StockService::getBatchSelectZY($query['warehouse_id'], '', $query['value']);
            return ['data' => $rows];
        }
        return $this->render([
            'search' => $search,
        ]);
    }

    // 物流信息
    public function logisticsAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::get('stock_allocation');
            $id = $gets['id'];
            $gets['freight_created_dt'] = date('Y-m-d H:i:s');
            $gets['freight_created_by'] = auth()->user()->name;
            DB::table('stock_allocation')->where('id', $id)->update($gets);
            return $this->json('物流信息提交成功。', true);
        }
        $file = base_path().'/app/Gdoo/'.ucfirst(Request::module()).'/views/'.Request::controller().'/'.Request::action().'.html';
        $id = Request::get('id');
        $row = Allocation::find($id);
        $freight_quantity = floatval($row['freight_quantity']);

        if ($freight_quantity == 0) {
            $count = DB::table('stock_allocation_data')
            ->where('allocation_id', $id)->selectRaw('sum(total_weight) as weight, sum(quantity) as quantity')->first();
            $weight = number_format($count['weight'] / 1000, 2);
            $quantity = number_format($count['quantity'], 2);
            $row['freight_quantity'] = $quantity;
            $row['freight_weight'] = $weight;
        }
        $form = Form::make2(['table' => 'stock_allocation', 'file' => $file, 'row' => $row]);
        return $form;
    }

    // 删除
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'stock_allocation', 'ids' => $ids]);
        }
    }
}
