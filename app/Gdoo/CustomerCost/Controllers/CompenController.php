<?php namespace Gdoo\CustomerCost\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\CustomerCost\Models\Compen;
use Gdoo\CustomerCost\Models\CostData;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\AuditController;

class CompenController extends AuditController
{
    public $permission = ['importExcel'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'customer_cost_compen',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action'  => 'show',
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
            $model->where('customer_cost.type_id', 87);

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

            $header['select'][] = 'customer_cost_data.cost_id';
            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            $items = Grid::dataFilters($rows, $header);
            return $items->toJson();
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Compen::$tabs;
        $header['bys'] = Compen::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    // 其他费用
    public function createAction($action = 'edit')
    {
        $id = (int)Request::get('id');

        // 客户权限
        $header['region'] = ['field' => 'customer_id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        $header['code'] = 'customer_cost_compen';
        $header['id'] = $id;
        $header['action'] = $action;

        $form = Form::make($header);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    // 编辑促销
    public function editAction()
    {
        return $this->createAction();
    }

    // 显示促销
    public function showAction()
    {
        return $this->createAction('show');
    }

    public function importExcelAction()
    {
        if (Request::method() == 'POST') {
            $file = Request::file('file');
            if ($file->isValid()) {
                $types = DB::table('customer_order_type')->get()->keyBy('name');

                /*
                [0] => 客户编码
                [1] => 客户名称
                [2] => 金额
                */

                $rows = readExcel($file->getPathName());
                $items = [];

                $codes = [];
                foreach($rows as $i => $row) {
                    if ($i > 1) {
                        $codes[] = $row[0];
                    }
                }

                $customers = DB::table('customer')->whereIn('code', $codes)->get()->keyBy('code');
                foreach($rows as $i => $row) {
                    if ($i > 1) {
                        $customer = $customers[$row[0]];
                        if (empty($customer)) {
                            return $this->json('客户编码'.$row[0].'不存在。');
                        }
                        $item = [
                            'customer_id' => $customer['id'],
                            'customer_id_name' => $customer['name'],
                            'money' => $row[2],
                        ];
                        $items[] = $item;
                    }
                }
                return $this->json($items, true);
            }
        }
        return view('importExcel');
    }

    // 删除促销
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'customer_cost_compen', 'ids' => $ids]);
        }
    }
}
