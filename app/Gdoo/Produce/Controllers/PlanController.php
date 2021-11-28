<?php namespace Gdoo\Produce\Controllers;

use DB;
use Request;
use Auth;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\User\Models\User;
use Gdoo\Produce\Models\Plan;
use Gdoo\Produce\Models\Formula;

use Gdoo\Produce\Services\ProduceService;

use Gdoo\Index\Controllers\WorkflowController;

class PlanController extends WorkflowController
{
    public $permission = ['dialog', 'orderPlan', 'planExport'];

    public function index()
    {
        $header = Grid::header([
            'code' => 'produce_plan',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $this->access['edit'],
        ]];

        $header['buttons'] = [
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Plan::$tabs;
        $header['bys'] = Plan::$bys;

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

    // 生产计划导出
    public function planExport()
    {
        $search = search_form([
            'advanced' => 0,
        ], [
            ['form_type' => 'date', 'name' => '计划时间', 'field' => 'date', 'options' => []
        ],
        ], 'model');
        
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $date = $query['search_0'];
            $rows = [];
            if ($date) {

                $departments = DB::table('produce_plan_data')
                ->leftJoin('produce_plan', 'produce_plan.id', '=', 'produce_plan_data.plan_id')
                ->leftJoin('department', 'department.id', '=', 'produce_plan_data.department_id')
                ->where('produce_plan.date', $date)
                ->where('department.id', '>', 0)
                ->groupBy('department.id', 'department.name')
                ->get(['department.id','department.name']);

                foreach($departments as $index => $department) {
                    $sql = "
                    SELECT p.name as product_name, p.spec as product_spec, pu.name as product_unit, d.plan_num, d.remark, d.batch_sn, m.date
                    FROM produce_plan_data d 
                    left join produce_plan m on m.id = d.plan_id
                    left join product p on d.product_id = p.id
                    left join product_unit pu on pu.id = p.unit_id
                    WHERE m.id = d.plan_id 
                    AND isnull(d.department_id, 0) = ? 
                    AND d.plan_num <> 0 
                    AND d.plan_num IS NOT NULL 
                    AND m.date = ? AND m.type = 1";
                    $items = DB::select($sql, [$department['id'], $date]);

                    if ($index > 0) {
                        $rows[] = [];
                    }

                    $rows[] = [
                        'product_name' => '序号:'.($index + 1),
                        'product_spec' => '',
                        'product_unit' => '生产车间',
                        'plan_num' => $department['name'],
                        'quantity' => '',
                        'remark' => '',
                        'date' => ''
                    ];

                    $rows[] = [
                        'product_name' => '成品名称',
                        'product_spec' => '规格型号',
                        'product_unit' => '计量单位',
                        'plan_num' => '计划数量',
                        'quantity' => '生产数量',
                        'remark' => '备注',
                        'date' => '计划日期'
                    ];

                    $plan_num = 0;
                    foreach($items as $item) {
                        $plan_num += $item['plan_num'];
                        $rows[] = [
                            'product_name' => $item['product_name'],
                            'product_spec' => $item['product_spec'],
                            'product_unit' => $item['product_unit'],
                            'plan_num' => $item['plan_num'],
                            'remark' => $item['batch_sn'],
                            'date' => $item['date'],
                        ];
                    }
                    $rows[] = [
                        'product_name' => '总量',
                        'product_spec' => '',
                        'product_unit' => '',
                        'plan_num' => $plan_num,
                        'remark' => '',
                        'date' => ''
                    ];
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

    public function create($action = 'edit')
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'produce_plan', 'id' => $id, 'action' => $action]);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    public function audit()
    {
        return $this->create('edit');
    }

    public function edit()
    {
        return $this->create('edit');
    }

    public function show()
    {
        return $this->create('show');
    }

    // 参照订单计划
    public function orderPlan()
    {
        $plan_date = Request::get('plan_date');
        $rows = [];
        if ($plan_date) {
            $rows = ProduceService::getProducePlanQuantity($plan_date);
            if (empty($rows)) {
                return $this->json($plan_date.'无营销计划和外贸订单。');
            } else {
                return $this->json($rows, true);
            }
        }
        return $this->json($rows, true);
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'produce_plan', 'ids' => $ids]);
        }
    }
}
