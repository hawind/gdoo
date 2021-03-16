<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Customer\Models\CustomerTask;

use Gdoo\Index\Controllers\AuditController;

class TaskController extends AuditController
{
    public $permission = ['importExcel'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'customer_task',
            'referer' => 1,
            'sort' => 'id',
            'order' => 'desc',
            'search' => [],
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
        $header['tabs'] = CustomerTask::$tabs;
        
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

            // 客户权限
            $region = regionCustomer('customer_id_customer');
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
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

    public function createAction($action = 'create')
    {
        $id = (int)Request::get('id');
        $form = Form::make([
            'code' => 'customer_task', 
            'id' => $id, 
            'action' => $action,
            'select' => '
                (customer_task_data.month1 + customer_task_data.month2 + customer_task_data.month3) as quarter1,
                (customer_task_data.month4 + customer_task_data.month5 + customer_task_data.month6) as quarter2,
                (customer_task_data.month7 + customer_task_data.month8 + customer_task_data.month9) as quarter3,
                (customer_task_data.month10 + customer_task_data.month11 + customer_task_data.month12) as quarter4
            ',
        ]);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    public function editAction()
    {
        return $this->createAction('edit');
    }

    public function showAction()
    {
        return $this->createAction('show');
    }

    /**
     * 区域进度
     */
    public function progressAction()
    {
        $year = date('Y');
        $search = search_form([], [[
                'form_type' => 'year', 
                'field' => 'date', 
                'name' => '年份',
                'value' => $year,
            ],[
                'form_type' =>'dialog', 
                'field' => 'customer_id',
                'name' => '客户', 
                'options' => ['url' => 'customer/customer/dialog', 'query' => []]
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

            $customer = DB::table('customer')
            ->leftJoin('customer_region as r', 'r.id', '=', 'customer.region_id');

            $task = DB::table('customer_task_data as d')
            ->leftJoin('customer_task as m', 'm.id', '=', 'd.task_id')
            ->leftJoin('customer', 'customer.id', '=', 'd.customer_id')
            ->where('m.status', 1)
            ->whereRaw('m.year = ?', [$query['date']]);

            $delivery = DB::table('stock_delivery_data as d')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
            ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw(sql_year('m.invoice_dt').' = ?', [$query['date']])
            ->selectRaw('m.customer_id, count(DISTINCT m.id) as [count], '.sql_month('m.invoice_dt').' as [month], sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money')
            ->groupBy('m.customer_id', DB::raw(sql_month('m.invoice_dt')));

            $cancel = DB::table('stock_cancel_data as d')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
            ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw(sql_year('m.invoice_dt').' = ?', [$query['date']])
            ->selectRaw('m.customer_id, 0 as [count], '.sql_month('m.invoice_dt').' as [month], sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money')
            ->groupBy('m.customer_id', DB::raw(sql_month('m.invoice_dt')));

            $direct = DB::table('stock_direct_data as d')
            ->leftJoin('product', 'product.id', '=', 'd.product_id')
            ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
            ->leftJoin('customer', 'customer.id', '=', 'm.customer_id')
            ->whereRaw('d.product_id <> 20226 and isnull(product.product_type, 0) = 1')
            ->whereRaw(sql_year('m.invoice_dt').' = ?', [$query['date']])
            ->selectRaw('m.customer_id, count(DISTINCT m.id) as [count], '.sql_month('m.invoice_dt').' as [month], sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money')
            ->groupBy('m.customer_id', DB::raw(sql_month('m.invoice_dt')));

            if ($query['region_id']) {
                $region_ids = explode(',', $query['region_id']);
                $customer->whereIn('customer.region_id', $region_ids);
                $task->whereIn('customer.region_id', $region_ids);
                $delivery->whereIn('customer.region_id', $region_ids);
                $cancel->whereIn('customer.region_id', $region_ids);
                $direct->whereIn('customer.region_id', $region_ids);
            }

            if ($query['customer_id']) {
                $customer_ids = explode(',', $query['customer_id']);
                $customer->whereIn('customer.id', $customer_ids);
                $task->whereIn('customer.id', $customer_ids);
                $delivery->whereIn('customer.id', $customer_ids);
                $cancel->whereIn('customer.id', $customer_ids);
                $direct->whereIn('customer.id', $customer_ids);
            }

            // 客户圈权限
            $_region = regionCustomer();
            if ($_region['authorise']) {
                foreach($_region['whereIn'] as $key => $whereIn) {
                    $customer->whereIn($key, $whereIn);
                    $task->whereIn($key, $whereIn);
                    $delivery->whereIn($key, $whereIn);
                    $cancel->whereIn($key, $whereIn);
                    $direct->whereIn($key, $whereIn);
                }
            }
            $_rows = $cancel->unionAll($delivery)->unionAll($direct)->get();

            $_tasks = $task->get()->toArray();

            $rows = [];
            foreach($_rows as $row) {
                $rows[$row['customer_id']]['month'.$row['month']] += $row['money'];
                $rows[$row['customer_id']]['count'.$row['month']] += $row['count'];
            }

            $tasks = [];
            foreach($_tasks as $task) {
                $tasks[$task['customer_id']] = $task;
            }

            $customers = $customer->get([
                'r.name as region_name', 
                'customer.id as customer_id', 
                'customer.name as customer_name',
                'customer.code as customer_code',
                'customer.status'
            ])->toArray();

            foreach($customers as &$customer) {

                $quarters = [];

                $task = $tasks[$customer['customer_id']];

                $total_money = 0;
                $total_task = 0;
                $total_count = 0;

                // 计算月进度
                for ($i=1; $i <= 12; $i++) {
                    $month = $task['month'.$i];

                    $customer['month'.$i] = $month;

                    $money = $rows[$customer['customer_id']]['month'.$i] / 10000;
                    $money = sprintf('%.4f', $money);

                    $quarter = ceil($i / 3);
                    $quarters[$quarter]['money'] += $money;
                    $quarters[$quarter]['task'] += $month;

                    $total_money += $money;
                    $total_task += $month;
                    
                    // 总订单数量
                    $count = $rows[$customer['customer_id']]['count'.$i];
                    $total_count += $count;

                    $customer['month_'.$i.'_money'] = $money;
                    if ($money > 0 && $month > 0) {
                        $customer['month_'.$i.'_rate'] = sprintf('%.2f', ($money / $month) * 100);
                    } else {
                        $customer['month_'.$i.'_rate'] = 0;
                    }
                }

                if ($total_money > 0 && $total_task > 0) {
                    $customer['total_rate'] = sprintf('%.2f', ($total_money / $total_task) * 100);
                } else {
                    $customer['total_rate'] = 0;
                }
                $customer['total_task'] = $total_task;
                $customer['total_money'] = $total_money;
                $customer['total_count'] = $total_count;

                // 计算季度进度
                for ($i=1; $i <= 4; $i++) {
                    $quarter = $quarters[$i];
                    $customer['quarter_'.$i] = $quarter['task'];
                    $customer['quarter_'.$i.'_money'] = sprintf('%.4f', $quarter['money']);
                    if ($quarter['money'] > 0 && $quarter['task'] > 0) {
                        $customer['quarter_'.$i.'_rate'] = sprintf('%.2f', ($quarter['money'] / $quarter['task']) * 100);
                    } else {
                        $customer['quarter_'.$i.'_rate'] = 0;
                    }

                }
            }
            return $this->json($customers, true);
        }

        $header = [
            'table' => 'customer_task',
            'master_table' => 'customer_task',
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

    public function importExcelAction()
    {
        if (Request::method() == 'POST') {
            $file = Request::file('file');
            if ($file->isValid()) {
                $customers = DB::table('customer')->get()->keyBy('code');
                /*
                [0] => 合同编号
                [1] => 客户编码
                [2] => 数量
                [3] => 单价
                [4] => 备注
                */
                $rows = readExcel($file->getPathName(), $file->getClientOriginalExtension());
                $items = [];
                foreach($rows as $i => $row) {
                    if ($i > 1) {
                        $customer = $customers[$row[1]];
                        if (empty($customer)) {
                            return $this->json('客户编码'.$row[1].'客户档案不存在。');
                        }
                        $item = [
                            'code' => $row[0],
                            'customer_id' => $customer['id'],
                            'customer_id_name' => $customer['name'],
                            'month1' => $row[4],
                            'month2' => $row[5],
                            'month3' => $row[6],
                            'month4' => $row[7],
                            'month5' => $row[8],
                            'month6' => $row[9],
                            'month7' => $row[10],
                            'month8' => $row[11],
                            'month9' => $row[12],
                            'month10' => $row[13],
                            'month11' => $row[14],
                            'month12' => $row[15],
                        ];
                        $items[] = $item;
                    }
                }
                return $this->json($items, true);
            }
        }
        return view('importExcel');
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'customer_task', 'ids' => $ids]);
        }
    }
}
