<?php namespace Gdoo\Order\Controllers;

use DB;
use Request;
use Auth;
use Cache;

use Gdoo\Customer\Models\Customer;
use Gdoo\Product\Models\Product;
use Gdoo\Product\Models\ProductCategory;
use Gdoo\Stock\Models\Warehouse;
use Gdoo\Order\Models\CustomerOrder;
use Gdoo\Stock\Models\Stock;

use Gdoo\Produce\Services\ProduceService;

use Gdoo\Index\Controllers\DefaultController;

class PlanController extends DefaultController
{
    public $permission = [
        'producePlan', 
    ];

    /**
     * 生产计划总表
     */
    public function indexAction()
    {
        // 开始天
        $sdate = date('Y-m-d', strtotime("-1 day"));
        // 结束天
        $edate = date('Y-m-d', strtotime("+2 day"));

        $search = search_form([], [[
                'form_type' => 'date2', 
                'field' => 'date', 
                'name' => '计划日期',
                'value' => [$sdate, $edate]
            ],[
                'form_type' =>'select', 
                'field' => 'type', 
                'name' => '内销/外销',
                'options' => [['id'=>1, 'name'=> '内销'], ['id'=>2, 'name'=> '外销']]
            ],[
                'form_type' =>'dialog', 
                'field' => 'warehouse_id',
                'name' => '仓库',
                'options' => ['url' => 'stock/warehouse/dialog', 'query' => []]
            ],[
                'form_type' =>'dialog', 
                'field' => 'category_id',
                'name' => '产品类别', 
                'options' => ['url' => 'product/category/dialog', 'query' => []]
            ],
        ], 'model');

        $query = [];

        if (Request::method() == 'POST') {
            foreach($search['where'] as $where) {
                if ($where['active']) {
                    $query[$where['field']] = $where['search'];
                }
            }

            if ($query['date']) {
                $sdate = $query['date'][0];
                $edate = $query['date'][1];
            }
            $rows = ProduceService::getPlanDetail($sdate, $edate, $query['warehouse_id'], $query['category_id'], $query['type']);
            $json = ['data' => $rows, 'status' => true];
            return response()->json($json);
        }

        $header = [
            'table' => 'produce_data',
            'master_table' => 'produce_data',
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

    /**
     * 外销销售进度表
     */
    public function export_saleAction()
    {
        // 开始天
        $sdate = date('Y-m-01');
        // 结束天
        $edate = date('Y-m-d');

        $options = option('flow.status');

        $search = search_form([], [[
                'form_type' => 'date2', 
                'field' => 'mm.created_dt', 
                'name' => '订单日期',
                'value' => [$sdate, $edate]
            ],[
                'form_type' => 'text', 
                'field' => 'mm.sn', 
                'name' => '单据编号',
            ],[
                'form_type' =>'dialog', 
                'field' => 'mm.product_id',
                'name' => '产品名称', 
                'options' => ['url' => 'product/product/dialog', 'query' => []]
            ],[
                'form_type' =>'dialog', 
                'field' => 'mm.customer_id',
                'name' => '客户',
                'options' => ['url' => 'customer/customer/dialog', 'query' => []]
            ],[
                'form_type' =>'select',
                'field' => 'mm.status',
                'name' => '状态',
                'options' => $options
            ],
        ], 'model');

        $query = $search['query'];

        if (Request::method() == 'POST') {
            $rows = [];

            if ($query['filter'] == 1) {

                $sql = "mm.status,
                mm.id,
                mm.sn,
                mm.created_at,
                mm.created_dt,
                mm.customer_id,
                mm.customer_name,
                mm.customer_code,
                mm.product_id,
                mm.product_code,
                mm.product_name,
                mm.product_spec,
                mm.quantity,
                mm.batch_sn,
                pp.plan_num,
                r.storage_num
                FROM (SELECT m.status,
                    m.id,
                    m.sn,
                    m.created_at,
                    ".sql_year_month_day('m.created_at', 'ts')." AS created_dt,
                    m.customer_id,
                    c.name AS customer_name,
                    c.code AS customer_code,
                    d.product_id,
                    p.code AS product_code,
                    p.name AS product_name,
                    p.spec AS product_spec,
                    d.quantity,
                    d.batch_sn
                    FROM customer_order_data d
                        LEFT JOIN product p ON p.id = d.product_id
                        LEFT JOIN customer_order m ON m.id = d.order_id
                        LEFT JOIN customer c ON c.id = m.customer_id
                    WHERE m.type_id = 2) mm
                            
                LEFT JOIN (SELECT produce_plan_data.batch_sn,
                    produce_plan_data.product_id,
                    sum(isnull(produce_plan_data.plan_num, 0)) AS plan_num
                    FROM produce_plan_data
                    WHERE produce_plan_data.batch_sn IS NOT NULL AND produce_plan_data.plan_num IS NOT NULL
                    GROUP BY produce_plan_data.product_id, produce_plan_data.batch_sn) pp ON mm.product_id = pp.product_id AND mm.batch_sn = pp.batch_sn
                                
                LEFT JOIN (SELECT stock_record10_data.product_id,
                    stock_record10_data.batch_sn,
                    sum(isnull(stock_record10_data.quantity, 0)) AS storage_num
                    FROM stock_record10_data
                    GROUP BY stock_record10_data.product_id, stock_record10_data.batch_sn) r ON mm.product_id = r.product_id AND mm.batch_sn = r.batch_sn
                ";
                
                $model = DB::query()->selectRaw($sql)
                ->orderBy('created_at', 'desc');

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        $model->search($where);
                    }
                }

                $rows = $model->get();

                $options = $options->pluck('name', 'id');
                $rows->transform(function($item) use($options) {
                    $item['status'] = $options[$item['status']];
                    return $item;
                });
            }
            $json = ['data' => $rows, 'status' => true];
            return response()->json($json);
        }

        $search['table'] = 'produce_plan';
        return $this->display([
            'search' => $search,
        ]);
    }

    /**
     * 生产计划
     */
    public function produceAction()
    {
        // 开始天
        $sdate = date('Y-m-d', strtotime("-1 day"));
        // 结束天
        $edate = date('Y-m-d', strtotime("+2 day"));

        $search = search_form([], [[
                'form_type' => 'date2', 
                'field' => 'date', 
                'name' => '计划日期',
                'value' => [$sdate, $edate]
            ],[
                'form_type' =>'select', 
                'field' => 'type', 
                'name' => '内销/外销',
                'options' => [['id'=>1, 'name'=> '内销'], ['id'=>2, 'name'=> '外销']]
            ],[
                'form_type' =>'dialog', 
                'field' => 'category_id',
                'name' => '产品类别', 
                'options' => ['url' => 'product/category/dialog', 'query' => []]
            ],
        ], 'model');

        $query = [];

        if (Request::method() == 'POST') {
            foreach($search['where'] as $where) {
                if ($where['active']) {
                    $query[$where['field']] = $where['search'];
                }
            }

            if ($query['date']) {
                $sdate = $query['date'][0];
                $edate = $query['date'][1];
            }

            $dates = array_reverse(date_range($sdate, $edate));
            $columns = [];

            // 创建计划主表
            foreach ($dates as $produce_dt) {
                if ($produce_dt) {

                    $produce = DB::table('produce_data')
                    ->where('date', $produce_dt)
                    ->first();

                    $editable = false;
                    $btn = '';

                    if ($produce['status'] == 1) {
                        $btn = ' <a href="javascript:;" class="btn btn-default btn-xs disabled">已提交</a>';
                    } else {
                        if ($this->access['produce_save']) {
                            $btn .= ' <a href="javascript:;" data-toggle="produce_data" data-id="'.$produce_dt.'" data-action="save" class="btn btn-default btn-xs">保存</a>';
                            $editable = true;
                        }
                        if ($this->access['produce_submit']) {
                            $btn .= ' <a href="javascript:;" data-toggle="produce_data" data-id="'.$produce_dt.'" data-action="submit" class="btn btn-default btn-xs">提交</a>';
                        }
                    }
                    
                    $_produce_dt = str_replace('-', '_', $produce_dt);
                    $columns[] = [
                        'headerName' => $produce_dt.$btn,
                        'cellRenderer' => 'htmlCellRenderer',
                        'children' => [
                            ['cellClass' => 'text-right', 'headerName' => '发货计划', 'width' => 60, 'cellRenderer' => 'wfhjh', 'field' => 'wfhjh_num_'.$_produce_dt, 'type' => 'number', 'numberOptions' => ['places' => 0, 'default' => ''], 'calcFooter' => 'sum'],
                            ['cellClass' => 'text-right', 'headerName' => '营销计划', 'width' => 60, 'field' => 'sale_plan_num_'.$_produce_dt, 'type' => 'number', 'numberOptions' => ['places' => 0, 'default' => ''], 'calcFooter' => 'sum', 'editable'=> $editable],
                            ['cellClass' => 'text-right', 'headerName' => '生产计划', 'width' => 60, 'field' => 'produce_plan_num_'.$_produce_dt, 'type' => 'number', 'numberOptions' => ['places' => 0, 'default' => ''], 'calcFooter' => 'sum'],
                            ['cellClass' => 'text-right', 'headerName' => '计划变更', 'width' => 60, 'field' => 'produce_bg_num_'.$_produce_dt, 'type' => 'number', 'numberOptions' => ['places' => 0, 'default' => ''], 'calcFooter' => 'sum'],
                            ['cellClass' => 'text-right', 'headerName' => '成品入库', 'width' => 60, 'field' => 'rk_num_'.$_produce_dt, 'type' => 'number', 'numberOptions' => ['places' => 0, 'default' => ''], 'calcFooter' => 'sum'],
                        ]
                    ];
                }
            }
            $rows = ProduceService::getPlanDetail($sdate, $edate, 0, $query['category_id'], $query['type']);
            $json = ['columns' => $columns, 'data' => $rows, 'status' => true];
            return response()->json($json);
        }

        $header = [
            'table' => 'produce_data',
            'master_table' => 'produce_data',
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

    /**
     * 获取发货计划明细
     */
    public function producePlanAction()
    {
        $query = Request::all();
        if (Request::method() == 'POST') {

            $sql = "d.product_id, SUM(isnull(d.delivery_quantity,0) - isnull(i.Num,0) - isnull(r.Num,0)) as fhjh_num, m.pay_dt, 
            m.plan_delivery_dt,m.export_country,c.name as customer_name,m.sn
            FROM customer_order_data AS d
            left JOIN customer_order AS m ON m.id = d.order_id
            left JOIN customer as c ON m.customer_id = c.id
            LEFT JOIN (select dd.sale_data_id,SUM(dd.quantity) Num
                from stock_delivery_data dd,stock_delivery mm
                where mm.id = dd.delivery_id 
                GROUP BY dd.sale_data_id
            ) as i ON i.sale_data_id = d.id
            LEFT JOIN (select dd.sale_data_id, SUM(dd.quantity) Num
                from stock_allocation_data dd, stock_allocation mm
                where mm.id = dd.allocation_id
                GROUP BY dd.sale_data_id
             ) as r ON r.sale_data_id = d.id
            ";

            $date = str_replace('_', '-', $query['date']);
            $model = DB::query()->selectRaw($sql);
            $model->whereRaw('ISNULL(d.use_close, 0) = 0 and m.status > 0')
            ->where('plan_delivery_dt', $date)
            ->where('product_id', $query['product_id'])
            ->havingRaw('isnull(SUM(d.delivery_quantity),0) - isnull(i.Num,0) - isnull(r.Num,0) <> 0')
            ->groupBy(DB::raw('m.sn, d.product_id, d.delivery_quantity, i.Num, r.Num, m.pay_dt, m.plan_delivery_dt, m.export_country, c.name, m.sn'));

            $rows = $model->get();

            return ['data' => $rows];
        }
        return $this->render([
            'query' => $query,
        ]);
    }

    // 保存生产计划
    public function produce_saveAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $date = $gets['date'];
            foreach ($gets['rows'] as $row) {

                $has = DB::table('produce_data')
                ->where('product_id', $row['product_id'])
                ->where('date', $date)
                ->first();
                
                if (empty($has)) {
                    $row['date'] = $date;
                    DB::table('produce_data')->insert($row);
                } else {
                    if ($has['quantity'] > 0) {
                        DB::table('produce_data')->where('id', $has['id'])->update($row);
                    } else {
                        // 数量如果不大于0就删除
                        DB::table('produce_data')->where('id', $has['id'])->delete();
                    }
                }
            }
            return $this->json('营销计划保存成功。', true);
        }
    }

    // 提交生产计划
    public function produce_submitAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $count = DB::table('produce_data')->where('date', $gets['date'])->get();
            if ($count > 0) {
                DB::table('produce_data')->where('date', $gets['date'])->update(['status' => 1]);
                return $this->json('营销计划提交成功。', true);
            } else {
                return $this->json('营销计划为空不能提交。', true);
            }
        }
    }
}
