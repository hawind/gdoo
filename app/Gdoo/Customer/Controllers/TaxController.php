<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Customer\Models\CustomerTax;

use Gdoo\Index\Controllers\AuditController;

class TaxController extends AuditController
{
    public $permission = ['dialog'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'customer_tax',
            'referer' => 1,
            'search' => ['by' => ''],
            'sort' => 'customer_tax.customer_id',
            'order' => 'desc',
            'trash_btn' => 0,
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $this->access['edit'],
        ]];

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order']);

            // 客户权限
            $region = regionCustomer('customer_id_customer');
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            $items = Grid::dataFilters($rows, $header);
            return $items->toJson();
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '批量编辑', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'batchEdit', 'display' => $this->access['batchEdit']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = CustomerTax::$tabs;
        $header['bys'] = CustomerTax::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    // 新建客户联系人
    public function createAction($action = 'edit')
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'customer_tax', 'id' => $id, 'action' => $action]);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    // 创建客户联系人
    public function editAction()
    {
        return $this->createAction('edit');
    }

    // 创建客户联系人
    public function showAction()
    {
        return $this->createAction('show');
    }

    public function dialogAction()
    {
        $search = search_form(
            ['advanced' => ''], [
                ['form_type' => 'text', 'name' => '开票名称', 'field' => 'customer_tax.name', 'options' => []],
                ['form_type' => 'text', 'name' => '开票编码', 'field' => 'customer_tax.code', 'options' => []],
                ['form_type' => 'text', 'name' => '客户名称', 'field' => 'customer.name', 'options' => []],
                ['form_type' => 'text', 'name' => '客户编码', 'field' => 'customer.code', 'options' => []]
        ], 'model');

        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = CustomerTax::leftJoin('customer', 'customer_tax.customer_id', '=', 'customer.id');

            if (isset($query['customer_id'])) {
                $model->where('customer_id', $query['customer_id']);
            }
            
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            // 客户权限
            $region = regionCustomer();
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $model->select(['customer_tax.*','customer.code as customer_code', 'customer.name as customer_name']);
            $rows = $model->paginate($query['limit']);
            return response()->json($rows);
        }
        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 批量编辑
    public function batchEditAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            DB::table('customer_tax')->whereIn('id', $ids)->update([
                $gets['field'] => $gets['search_0'],
            ]);
            return $this->json('修改完成。', true);
        }
        $header = Grid::batchEdit([
            'code' => 'customer_tax',
            'columns' => ['class_id', 'department_id', 'status'],
        ]);
        return view('batchEdit', [
            'gets' => $gets,
            'header' => $header
        ]);
    }

    // 删除
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'customer_tax', 'ids' => $ids]);
        }
    }
}
