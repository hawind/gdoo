<?php namespace Gdoo\Purchase\Controllers;

use DB;
use Request;
use Auth;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\User\Models\User;
use Gdoo\Purchase\Models\Order;

use Gdoo\Index\Controllers\WorkflowController;

class OrderController extends WorkflowController
{
    public $permission = ['dialog', 'serviceRecord01'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'purchase_order',
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
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Order::$tabs;
        $header['bys'] = Order::$bys;

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
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'purchase_order', 'id' => $id, 'action' => $action]);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    public function auditAction()
    {
        return $this->createAction('edit');
    }

    public function editAction()
    {
        return $this->createAction('edit');
    }

    public function showAction()
    {
        return $this->createAction('show');
    }

    // 参照其他入库单
    public function serviceRecord01Action()
    {
        $search = search_form(
            ['advanced' => ''], [
                ['form_type' => 'text', 'name' => '供应商名称', 'field' => 'supplier.name', 'options' => []],
                ['form_type' => 'text', 'name' => '供应商编码', 'field' => 'supplier.code', 'options' => []],
                ['form_type' => 'text', 'name' => '单据编号', 'field' => 'purchase_order.sn', 'options' => []],
        ], 'model');

        $query = $search['query'];

        if (Request::method() == 'POST') {

            if ($query['master']) {
                $model = DB::table('purchase_order')
                ->leftJoin(DB::raw('(select SUM(ISNULL(d.quantity, 0)) cg_num, d.order_id
                        FROM purchase_order_data as d
                        GROUP BY d.order_id
                    ) pod
                '), 'purchase_order.id', '=', 'pod.order_id')
                ->leftJoin(DB::raw('(select SUM(ISNULL(d.quantity, 0)) yr_num, d.order_id
                        FROM stock_record01_data as d
                        GROUP BY d.order_id
                    ) srd
                '), 'purchase_order.id', '=', 'srd.order_id')
                ->leftJoin('supplier', 'supplier.id', '=', 'purchase_order.supplier_id')
                ->leftJoin('department', 'department.id', '=', 'purchase_order.department_id')
                ->whereRaw('ISNULL(pod.cg_num, 0) - ISNULL(srd.yr_num, 0) > 0')
                ->orderBy('purchase_order.id', 'desc');

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        if ($where['field'] == 'purchase_order.sn') {
                            $model->search($where);
                        }
                    }
                }

                $model->selectRaw('
                    purchase_order.*,
                    pod.*,
                    srd.*,
                    department.name as department_name
                ');
                $rows = $model->get();
            } else {
                $model = DB::table('purchase_order_data as pod');

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        if ($where['field'] != 'purchase_order.sn') {
                            $model->search($where);
                        }
                    }
                }

                $rows = $model->leftJoin(DB::raw('(select SUM(ISNULL(d.quantity, 0)) yr_num, d.order_data_id
                        FROM stock_record01_data as d
                        GROUP BY d.order_data_id
                    ) srd
                '), 'pod.id', '=', 'srd.order_data_id')
                ->leftJoin('purchase_order as po', 'po.id', '=', 'pod.order_id')
                ->leftJoin('product as p', 'p.id', '=', 'pod.product_id')
                ->leftJoin('product_unit as pu', 'pu.id', '=', 'p.unit_id')
                ->leftJoin('supplier', 'supplier.id', '=', 'pod.supplier_id')
                ->whereRaw('ISNULL(pod.quantity, 0) - ISNULL(srd.yr_num, 0) > 0')
                ->orderBy('po.id', 'desc')
                ->whereIn('pod.order_id', (array)$query['ids'])
                ->selectRaw('srd.*, pod.*, 
                    p.name as product_name, 
                    p.spec as product_spec, 
                    p.code as product_code, 
                    pu.name as product_unit,
                    ISNULL(pod.quantity, 0) - ISNULL(srd.yr_num, 0) as wr_num,
                    po.sn as order_sn,
                    pod.order_id as order_id,
                    pod.id as order_data_id,
                    supplier.id as supplier_id,
                    supplier.code as supplier_code,
                    supplier.name as supplier_name
                ')
                ->get()
                ->toArray();
            }
            return $this->json($rows, true);
        }

        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'purchase_order', 'ids' => $ids]);
        }
    }
}
