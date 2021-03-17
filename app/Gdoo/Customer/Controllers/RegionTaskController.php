<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Customer\Models\CustomerRegionTask;

use Gdoo\Index\Controllers\AuditController;

class RegionTaskController extends AuditController
{
    public $permission = [];

    public function index()
    {
        $header = Grid::header([
            'code' => 'customer_region_task',
            'referer' => 1,
            'sort' => 'customer_region_task_data.id',
            'order' => 'asc',
        ]);

        $cols = $header['cols'];
        
        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-mail-reply', 'action' => 'export', 'display' => 1],
        ];
        
        $header['cols'] = $cols;
        $header['tabs'] = CustomerRegionTask::$tabs;
        
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->leftJoin('customer_region as region2', 'region2.id', '=', 'region_id_customer_region.parent_id')
            ->leftJoin('user as region2_user', 'region2_user.id', '=', 'region2.owner_user_id');
            
            $model->orderBy($header['sort'], $header['order']);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    if ($where['field'] == 'customer_region_task_data.region2_id') {
                        $where['field'] = 'region2.id';
                    }
                    if ($where['field'] == 'customer_region_task_data.region2_user') {
                        $where['field'] = 'region2_user.id';
                    }
                    $model->search($where);
                }
            }

            // 客户权限
            $region = regionCustomer();
            if ($region['authorise']) {
                $model->whereIn('customer_region_task_data.region_id', $region['regionIn']);
            }

            $header['select'][] = 'region2.name as region2_id';
            $header['select'][] = 'region2_user.name as region2_user';

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function create($action = 'create')
    {
        $id = (int)Request::get('id');

        $form = Form::make([
            'code' => 'customer_region_task', 
            'id' => $id, 
            'action' => $action,
            'select' => '
                (customer_region_task_data.month1 + customer_region_task_data.month2 + customer_region_task_data.month3) as quarter1,
                (customer_region_task_data.month4 + customer_region_task_data.month5 + customer_region_task_data.month6) as quarter2,
                (customer_region_task_data.month7 + customer_region_task_data.month8 + customer_region_task_data.month9) as quarter3,
                (customer_region_task_data.month10 + customer_region_task_data.month11 + customer_region_task_data.month12) as quarter4
            ',
        ]);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    public function edit()
    {
        return $this->create('edit');
    }

    public function show()
    {
        return $this->create('show');
    }

    /**
     * 区域进度
     */
    public function progress()
    {
        $year = date('Y');
        $search = search_form([], [[
                'form_type' => 'year',
                'field' => 'date',
                'name' => '年份',
                'value' => $year,
            ],[
                'form_type' =>'dialog',
                'field' => 'region_id',
                'name' => '销售组',
                'options' => ['url' => 'customer/region/dialog', 'query' => ['layer' => 3]]
            ],
        ], 'model');

        $query = [];

        if (Request::method() == 'POST') {

            foreach($search['where'] as $where) {
                if ($where['active']) {
                    $query[$where['field']] = $where['search'];
                }
            }

            if (empty($query['date'])) {
                $query['date'] = $year;
            }

            $region = DB::table('customer_region as r')
            ->where('r.layer', 3);

            $task = DB::table('customer_region_task_data as d')
            ->leftJoin('customer_region_task as m', 'm.id', '=', 'd.task_id')
            ->where('m.status', 1)
            ->whereRaw('m.year = ?', [$query['date']]);

            $delivery = DB::table('stock_delivery_data as d')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
            ->leftJoin('customer as c', 'c.id', '=', 'm.customer_id')
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw(sql_year('m.invoice_dt').' = ?', [$query['date']])
            ->selectRaw('c.region_id, '.sql_month('m.invoice_dt').' as [month], sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money')
            ->groupBy('c.region_id', DB::raw(sql_month('m.invoice_dt')));

            $cancel = DB::table('stock_cancel_data as d')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
            ->leftJoin('customer as c', 'c.id', '=', 'm.customer_id')
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw(sql_year('m.invoice_dt').' = ?', [$query['date']])
            ->selectRaw('c.region_id, '.sql_month('m.invoice_dt').' as [month], sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money')
            ->groupBy('c.region_id', DB::raw(sql_month('m.invoice_dt')));

            $direct = DB::table('stock_direct_data as d')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
            ->leftJoin('customer as c', 'c.id', '=', 'm.customer_id')
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw(sql_year('m.invoice_dt').' = ?', [$query['date']])
            ->selectRaw('c.region_id, '.sql_month('m.invoice_dt').' as [month], sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money')
            ->groupBy('c.region_id', DB::raw(sql_month('m.invoice_dt')));

            if ($query['region_id']) {
                $region_ids = explode(',', $query['region_id']);
                $region->whereIn('r.id', $region_ids);
                $delivery->whereIn('c.region_id', $region_ids);
                $cancel->whereIn('c.region_id', $region_ids);
                $direct->whereIn('c.region_id', $region_ids);
                $task->whereIn('d.region_id', $region_ids);
            }

            // 客户圈权限
            $_region = regionCustomer();
            if ($_region['authorise']) {
                $region->whereIn('r.id', $_region['regionIn']);
                $delivery->whereIn('c.region_id', $_region['regionIn']);
                $cancel->whereIn('c.region_id', $_region['regionIn']);
                $direct->whereIn('c.region_id', $_region['regionIn']);
                $task->whereIn('d.region_id', $_region['regionIn']);
            }

            $_rows = $cancel->unionAll($delivery)->unionAll($direct)->get();
            $_tasks = $task->get()->toArray();

            $rows = [];
            foreach($_rows as $row) {
                $rows[$row['region_id']]['month'.$row['month']] += $row['money'];
            }

            $tasks = [];
            foreach($_tasks as $task) {
                $tasks[$task['region_id']] = $task;
            }

            $regions = $region->get(['r.id as region_id', 'r.name as region_name'])->toArray();
            foreach($regions as &$region) {

                $quarters = [];

                $total_money = 0;
                $total_task = 0;

                $task = $tasks[$region['region_id']];

                // 计算月进度
                for ($i=1; $i <= 12; $i++) {
                    $month = $task['month'.$i];

                    $region['month'.$i] = $month;

                    $money = $rows[$region['region_id']]['month'.$i] / 10000;
                    $money = sprintf('%.4f', $money);
                    
                    $total_money += $money;
                    $total_task += $month;

                    $quarter = ceil($i / 3);
                    $quarters[$quarter]['money'] += $money;
                    $quarters[$quarter]['task'] += $month;

                    $region['month_'.$i.'_money'] = $money;

                    if ($month > 0) {
                        $region['month_'.$i.'_rate'] = sprintf('%.2f', ($money / $month) * 100);
                    } else {
                        $region['month_'.$i.'_rate'] = 0;
                    }
                }

                if ($total_money > 0 && $total_task > 0) {
                    $region['total_rate'] = sprintf('%.2f', ($total_money / $total_task) * 100);
                } else {
                    $region['total_rate'] = 0;
                }

                $region['total_task'] = $total_task;
                $region['total_money'] = $total_money;

                // 计算季度进度
                for ($i=1; $i <= 4; $i++) {
                    $quarter = $quarters[$i];
                    $region['quarter_'.$i] = $quarter['task'];
                    $region['quarter_'.$i.'_money'] = sprintf('%.4f', $quarter['money']);
                    if ($quarter['money'] > 0 && $quarter['task'] > 0) {
                        $region['quarter_'.$i.'_rate'] = sprintf('%.2f', ($quarter['money'] / $quarter['task']) * 100);
                    } else {
                        $region['quarter_'.$i.'_rate'] = 0;
                    }

                }
            }
            
            return $this->json($regions, true);
        }

        $header = [
            'table' => 'region_task',
            'master_table' => 'region_task',
            'buttons' => [],
            'search_form' => $search,
            'simple_search_form' => 0,
        ];

        $header['left_buttons'] = [
            ['name' => '导出', 'color' => 'default', 'icon' => 'fa-mail-forward', 'action' => 'export', 'display' => 1],
        ];

        return $this->display([
            'search' => $search,
            'header' => $header,
        ]);
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'customer_region_task', 'ids' => $ids]);
        }
    }
}
