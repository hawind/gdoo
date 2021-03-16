<?php namespace Gdoo\Order\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\Order\Models\SampleApply;
use Gdoo\Order\Models\SampleApplyProduct;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Order\Services\OrderService;

use Gdoo\Index\Controllers\WorkflowController;
use Gdoo\User\Services\UserService;

class SampleApplyController extends WorkflowController
{
    public $permission = ['dialog', 'serviceDelivery'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'sample_apply',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        // 自定义列
        $customFields = [
            'quantity' => [
                'headerName' => '数量',
                'field' => 'quantity',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'money' => [
                'headerName' => '金额',
                'field' => 'money',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
        ];

        $cols = Grid::addColumns($cols, 'master_region_id_name', $customFields);

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];
        $header['right_buttons'] = [
            ['name' => '关闭', 'color' => 'default', 'icon' => 'fa-lock', 'action' => 'close', 'display' => $this->access['close']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = SampleApply::$tabs;
        $header['bys'] = SampleApply::$bys;

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

            if ($this->access['index'] <> 4) {
                $region = regionCustomer('customer');
                $model->whereIn('sample_apply.region_id', $region['regionIn']);
            }
            
            $model->select($header['select']);

            // 明细统计
            $model->leftJoin(DB::raw('(select SUM(ISNULL(d.money, 0)) money, SUM(ISNULL(d.quantity, 0)) quantity, d.sample_id
                FROM sample_apply_data as d
                GROUP BY d.sample_id
            ) sad
            '), 'sample_apply.id', '=', 'sad.sample_id');
            $model->addSelect(DB::raw('sad.*'));

            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function detailAction()
    {
        $header = Grid::header([
            'code' => 'sample_apply',
            'referer' => 1,
            'template_id' => 79,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];
        $header['right_buttons'] = [
            ['name' => '关闭', 'color' => 'default', 'icon' => 'fa-lock', 'action' => 'close', 'display' => $this->access['close']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = SampleApply::$tabs2;
        $header['bys'] = SampleApply::$bys;

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

            if ($this->access['index'] <> 4) {
                $region = regionCustomer('customer');
                $model->whereIn('sample_apply.region_id', $region['regionIn']);
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
        $header['code'] = 'sample_apply';
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
        $this->layout = 'layouts.print2';
        print_prince($this->createAction('print'));
    }

    // 关闭
    public function closeAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $rows = SampleApply::whereIn('id', $gets['ids'])->get();
            foreach($rows as $row) {
                $row->use_close = $row->use_close == 1 ? 0 : 1;
                $row->save();
            }
            return $this->json('恭喜你，操作成功。', url_referer('index'));
        }
        return $this->render([
            'gets' => $gets,
        ]);
    }

    // 样品出库
    public function serviceDeliveryAction()
    {
        $header = Grid::header([
            'code' => 'sample_apply',
            'prefix' => '',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {

            if ($query['master']) {

                $model = DB::table('sample_apply')
                ->leftJoin('department', 'department.id', '=', 'sample_apply.department_id')
                ->leftJoin('customer_region', 'customer_region.id', '=', 'sample_apply.region_id');
                $model->orderBy('sample_apply.id', 'desc');

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        $model->search($where);
                    }
                }
                
                $model->whereExists(function($q) {
                    $q->selectRaw('1 FROM('.OrderService::getSampleSelectDetailSql().') as sad')->whereRaw('sad.sample_id = sample_apply.id');
                });
                $model->selectRaw('
                    sample_apply.*,
                    department.name as department_name, 
                    customer_region.name as region_name
                ');
                $rows = $model->get();

            } else {
                $model = DB::query()->selectRaw('* FROM('.OrderService::getSampleSelectDetailSql().') as a');
                $rows = $model->whereIn('sample_id', (array)$query['ids'])->get(['*', 'sample_data_id as id'])->toArray();
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
            return Form::remove(['code' => 'sample_apply', 'ids' => $ids]);
        }
    }
}
