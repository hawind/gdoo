<?php

namespace Gdoo\Order\Controllers;

use DB;
use Request;
use Auth;
use Paginator;

use App\Jobs\SendSms;

use App\Support\Hook;

use Gdoo\Customer\Models\Customer;
use Gdoo\Customer\Models\CustomerTax;

use Gdoo\Customer\Services\CustomerService;

use Gdoo\Product\Models\ProductCategory;
use Gdoo\Product\Models\Warehouse;
use Gdoo\Order\Models\CustomerOrder;
use Gdoo\Product\Models\Stock;
use Gdoo\Index\Models\Notification;

use Gdoo\Index\Services\BadgeService;

use Gdoo\Order\Services\OrderService;
use Gdoo\Produce\Services\ProduceService;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\WorkflowController;

class OrderController extends WorkflowController
{
    public $permission = [
        'dialog', 
        'serviceDelivery', 
        'serviceNotDelivery', 
        'servicePromotion', 
        'deliveryPlan', 
        'deliveryPlanDate',
        'serviceCancelOrder',
        'serviceCustomerMoney',
    ];

    public function index()
    {
        // 客户权限
        $region = regionCustomer('customer_id_customer');

        $header = Grid::header([
            'code' => 'customer_order',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $search = $header['search_form'];
        $query = $search['query'];
        $cols = $header['cols'];

        // 自定义列
        $customFields = [
            'delivery_date' => [
                'headerName' => '发货日期',
                'field' => 'delivery_date',
                'calcFooter' => 'sum',
                'width' => 100,
                'sortable' => false,
                'suppressMenu' => true,
                'cellStyle' => ['text-align' => 'center'],
            ],
            'zy_num' => [
                'headerName' => '直营数量',
                'field' => 'zy_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'zy_money' => [
                'headerName' => '直营金额',
                'field' => 'zy_money',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'pt_num' => [
                'headerName' => '普通数量',
                'field' => 'pt_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'pt_money' => [
                'headerName' => '普通金额',
                'field' => 'pt_money',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'wl_num' => [
                'headerName' => '物料数量',
                'field' => 'wl_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'wl_money' => [
                'headerName' => '物料金额',
                'field' => 'wl_money',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'zp_num' => [
                'headerName' => '赠品数量',
                'field' => 'zp_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'zp_money' => [
                'headerName' => '赠品金额',
                'field' => 'zp_money',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'zk_money' => [
                'headerName' => '折扣金额',
                'field' => 'zk_money',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ], 
            /*
            'delivery_sn' => [
                'headerName' => '发货单号',
                'field' => 'delivery_sn',
                'calcFooter' => 'sum',
                'width' => 140,
                'sortable' => false,
                'suppressMenu' => true,
                'cellStyle' => ['text-align' => 'center'],
            ],
            */
            'yfh_num' => [
                'headerName' => '已发数量',
                'field' => 'yfh_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'wfh_num' => [
                'headerName' => '未发数量',
                'field' => 'wfh_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],'hjzl' => [
                'headerName' => '合计重量(kg)',
                'field' => 'hjzl',
                'calcFooter' => 'sum',
                'width' => 100,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ]
        ];

        $cols = Grid::addColumns($cols, 'master_type_id_name', $customFields);

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '批量编辑', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'batchEdit', 'display' => $this->access['batchEdit']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = CustomerOrder::$tabs;
        $header['bys'] = CustomerOrder::$bys;

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }

            $model->orderBy($header['sort'], $header['order']);

            $model->select($header['select']);

            // 订单统计
            $model->leftJoin(DB::raw("(select
                Sum(case when c.type_id = 3 then money else 0 end) as zy_money,
                Sum(case when c.type_id = 3 then delivery_quantity else 0 end) as zy_num,
                Sum(case when p.material_type > 0 then money else 0 end) as wl_money,
                Sum(case when p.material_type > 0 then delivery_quantity else 0 end) as wl_num,
                Sum(case when d.type_id = 2 then money else 0 end) as zp_money,
                Sum(case when d.type_id = 2 then delivery_quantity else 0 end) as zp_num,
                Sum(case when ((p.product_type = 1 and c.type_id <> 3) or p.code = '99001') then money else 0 end) as pt_money,
                Sum(case when ((p.product_type = 1 and c.type_id <> 3) or p.code = '99001') then d.delivery_quantity else 0 end) as pt_num,
                Sum(case when p.code = '99001' then money else 0 end) as zk_money,
                SUM(ISNULL(d.delivery_quantity, 0)) dd_num, 
                SUM(ISNULL(d.delivery_quantity * p.weight, 0)) hjzl, 
                d.order_id
                FROM customer_order_data as d
                LEFT JOIN product as p on p.id = d.product_id
                LEFT JOIN customer_order as co on co.id = d.order_id
                LEFT JOIN customer as c on c.id = co.customer_id
                GROUP BY d.order_id
            ) cod
            "), 'customer_order.id', '=', 'cod.order_id');
            $model->addSelect(DB::raw('cod.*'));

            // 发货统计
            $model->leftJoin(DB::raw('(select max(m.invoice_dt) delivery_date, SUM(ISNULL(d.quantity, 0)) yfh_num, d.sale_id
                    FROM stock_delivery_data as d
                    left join stock_delivery m on m.id = d.delivery_id
                    GROUP BY d.sale_id
                ) sdd
            '), 'customer_order.id', '=', 'sdd.sale_id');
            $model->addSelect(DB::raw('sdd.*,cod.dd_num - sdd.yfh_num as wfh_num'));

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    /**
     * 订单明细列表
     */
    public function detail()
    {
        // 客户权限
        $region = regionCustomer('customer_id_customer');

        $header = Grid::header([
            'code' => 'customer_order',
            'referer' => 1,
            'template_id' => 52,
            'search' => ['by' => ''],
        ]);

        $search = $header['search_form'];
        $query = $search['query'];

        $cols = $header['cols'];
        // 自定义列
        $customFields = [
            'delivery_date' => [
                'headerName' => '发货日期',
                'field' => 'delivery_date',
                'calcFooter' => 'sum',
                'width' => 100,
                'sortable' => false,
                'suppressMenu' => true,
                'cellStyle' => ['text-align' => 'center'],
            ],
            'delivery_sn' => [
                'headerName' => '发货单号',
                'field' => 'delivery_sn',
                'calcFooter' => 'sum',
                'width' => 140,
                'sortable' => false,
                'suppressMenu' => true,
                'cellStyle' => ['text-align' => 'center'],
            ],
            'yfh_num' => [
                'headerName' => '已发数量',
                'field' => 'yfh_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'wfh_num' => [
                'headerName' => '未发数量',
                'field' => 'wfh_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ]
        ];

        $cols = Grid::addColumns($cols, 'master_warehouse_contact', $customFields);

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = CustomerOrder::$tabs2;
        $header['bys'] = CustomerOrder::$bys;

        if (Request::method() == 'POST') {

            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }

            // 查询已发信息
            $model->leftJoin(DB::raw('(select m.sn delivery_sn, m.invoice_dt delivery_date, SUM(ISNULL(d.quantity, 0)) yf_num, d.sale_data_id
                    FROM stock_delivery_data d
                    left join stock_delivery m on m.id = d.delivery_id
                    GROUP BY d.sale_data_id, m.sn, m.invoice_dt
                ) sdd
            '), 'customer_order_data.id', '=', 'sdd.sale_data_id');

            // 查询调拨单
            $model->leftJoin(DB::raw('(select m.sn delivery_sn, m.invoice_dt delivery_date, SUM(ISNULL(d.quantity, 0)) yf_num, d.sale_data_id
                    FROM stock_allocation_data d
                    left join stock_allocation m on m.id = d.allocation_id
                    GROUP BY d.sale_data_id,m.sn,m.invoice_dt
                ) sad
            '), 'customer_order_data.id', '=', 'sad.sale_data_id');

            $model->orderBy($header['sort'], $header['order']);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $model->selectRaw('isnull(sdd.delivery_sn, sad.delivery_sn) delivery_sn, isnull(sdd.delivery_date, sad.delivery_date) delivery_date, isnull(sdd.yf_num, 0) + isnull(sad.yf_num, 0) as yfh_num, customer_order_data.delivery_quantity - isnull(sdd.yf_num, 0) - isnull(sad.yf_num, 0) as wfh_num');
            $model->addSelect($header['select']);

            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    /**
     * 发货计划
     */
    public function delivery()
    {
        // 客户权限
        $region = regionCustomer('customer_id_customer');

        $header = Grid::header([
            'code' => 'customer_order',
            'referer' => 1,
            'template_id' => '89',
            'search' => ['by' => ''],
        ]);

        $search = $header['search_form'];
        $query = $search['query'];

        $cols = $header['cols'];

        // 自定义列
        $customFields = [
            'pt_num' => [
                'headerName' => '数量',
                'field' => 'pt_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'pt_money' => [
                'headerName' => '金额',
                'field' => 'pt_money',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'wl_num' => [
                'headerName' => '物料数量',
                'field' => 'wl_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'wl_money' => [
                'headerName' => '物料金额',
                'field' => 'wl_money',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'other_money' => [
                'headerName' => '其他金额',
                'field' => 'other_money',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'total_weight' => [
                'headerName' => '重量合计(kg)',
                'field' => 'total_weight',
                'calcFooter' => 'sum',
                'width' => 110,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            /*
            'delivery_date' => [
                'headerName' => '发货日期',
                'field' => 'delivery_date',
                'calcFooter' => 'sum',
                'width' => 100,
                'sortable' => false,
                'suppressMenu' => true,
                'cellStyle' => ['text-align' => 'center'],
            ],
            'delivery_sn' => [
                'headerName' => '发货单号',
                'field' => 'delivery_sn',
                'calcFooter' => 'sum',
                'width' => 140,
                'sortable' => false,
                'suppressMenu' => true,
                'cellStyle' => ['text-align' => 'center'],
            ],
            */
            'yfh_num' => [
                'headerName' => '已发数量',
                'field' => 'yfh_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ],
            'wfh_num' => [
                'headerName' => '未发数量',
                'field' => 'wfh_num',
                'calcFooter' => 'sum',
                'width' => 80,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ]
        ];

        $cols = Grid::addColumns($cols, 'master_warehouse_contact', $customFields);

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '修改预计发货日期', 'color' => 'default', 'icon' => 'fa-file-text-o', 'action' => 'deliveryPlan', 'display' => $this->access['deliveryPlan']],
            ['name' => '修改物流信息', 'color' => 'default', 'action' => 'logisticsPlan', 'display' => $this->access['logisticsPlan']],
            ['name' => '修改运费支付方式', 'color' => 'default', 'action' => 'deliveryEdit', 'display' => $this->access['deliveryEdit']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = CustomerOrder::$tabs3;
        $header['bys'] = CustomerOrder::$bys;

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }

            $model->select($header['select']);

            $model->leftJoin('model_run', 'model_run.data_id', '=', 'customer_order.id')
            ->where('model_run.bill_id', 23);

            // 订单统计
            $model->leftJoin(DB::raw("(select
                Sum(case when p.material_type > 0 then d.money else 0 end) as wl_money,
                Sum(case when p.material_type > 0 then d.delivery_quantity else 0 end) as wl_num,
                Sum(case when ISNULL(p.material_type, 0) = 0 then d.delivery_quantity * d.price else 0 end) as pt_money,
                Sum(case when ISNULL(p.material_type, 0) = 0 then d.delivery_quantity else 0 end) as pt_num,
                Sum(d.other_money) as other_money,
                Sum(d.delivery_quantity * p.weight) as total_weight,
                SUM(ISNULL(d.delivery_quantity, 0)) dd_num, d.order_id
                FROM customer_order_data as d
                LEFT JOIN product as p on p.id = d.product_id
                LEFT JOIN customer_order as co on co.id = d.order_id
                LEFT JOIN customer as c on c.id = co.customer_id
                GROUP BY d.order_id
            ) cod
            "), 'customer_order.id', '=', 'cod.order_id');
            $model->addSelect(DB::raw('cod.*'));

            // 发货统计
            $model->leftJoin(DB::raw('(select SUM(ISNULL(d.quantity, 0)) yfh_num, d.sale_id
                    FROM stock_delivery_data as d
                    GROUP BY d.sale_id
                ) sdd
            '), 'customer_order.id', '=', 'sdd.sale_id');
            $model->addSelect(DB::raw('sdd.*,cod.dd_num - sdd.yfh_num as wfh_num'));

            $model->orderByRaw('ISNULL(model_run.actived_at, 0) desc');
            $model->orderBy($header['sort'], $header['order']);
        
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    /**
     * 获取发货计划明细
     */
    public function deliveryPlan()
    {
        $query = Request::all();
        if (Request::method() == 'POST') {
            $rows = ProduceService::getPreShipDate($query['id'], $query['date']);
            return ['data' => $rows];
        }
        return $this->render([
            'query' => $query,
        ]);
    }

    /**
     *  修改运费支付方式
     */
    public function deliveryEdit()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $id = $gets['id'];
            $delivery = $gets['delivery'];
            DB::table('customer_order')->where('id', $id)->update($delivery);
            return $this->json('修改运费支付方式。', true);
        }
        $order = DB::table('customer_order')->where('id', $gets['id'])->first();
        $gets['freight_pay_text'] = empty($order['freight_pay_text']) ? '回单付，送货' : $order['freight_pay_text'];
        return $this->render([
            'gets' => $gets,
        ]);
    }

    /**
     *  修改物流信息
     */
    public function logisticsPlan()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            $delivery = $gets['delivery'];
            $data = [];
            if ($delivery['plan_delivery_dt']) {
                $data['plan_delivery_dt'] = $delivery['plan_delivery_dt'];
            }
            if ($delivery['freight_short_logistics_id']) {
                $data['freight_short_logistics_id'] = $delivery['freight_short_logistics_id'];
            }
            if ($delivery['freight_short_car']) {
                $data['freight_short_car'] = $delivery['freight_short_car'];
            }
            DB::table('customer_order')->whereIn('id', $ids)->update($data);
            return $this->json('物流信息修改成功。', true);
        }
        return $this->render([
            'gets' => $gets,
        ]);
    }

    /**
     * 修改发货计划日期
     */
    public function deliveryPlanDate()
    {
        $query = Request::all();
        if (Request::method() == 'POST') {
            $order = CustomerOrder::find($query['id']);
            $order->plan_delivery_dt = $query['date'];
            $order->save();
            return $this->json('保存数据成功。', true);
        }
    }

    public function create($action = 'edit')
    {
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'customer_order';
        $header['id'] = $id;

        // 客户权限
        $header['region'] = ['field' => 'customer_id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        $header['select'] = '
            product_id_product.weight,
            product_id_product.weight * customer_order_data.delivery_quantity as total_weight,
            customer_order_data.fee_src_id,
            customer_order_data.fee_data_id
        ';
        $form = Form::make($header);
        $tpl = $action == 'print' ? 'print' : 'create';
        return $this->display(['form' => $form], $tpl);
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

    // 批量编辑
    public function batchEdit()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            DB::table('customer_order')->whereIn('id', $ids)->update([
                $gets['field'] => $gets['search_0'],
            ]);
            return $this->json('修改完成。', true);
        }
        $header = Grid::batchEdit([
            'code' => 'customer_order',
            'columns' => ['customer_id', 'tax_id'],
        ]);
        return view('batchEdit', [
            'gets' => $gets,
            'header' => $header
        ]);
    }

    public function print()
    {
        $id = Request::get('id');
        $template_id = Request::get('template_id');
        $template = DB::table('model_template')->where('id', $template_id)->first();
        $print_type = $template['print_type'];
        $this->layout = 'layouts.print_'.$print_type;
        
        if ($print_type == 'stiReport') {
            $data = OrderService::getPrintData($id);
            $print_data = [
                'master' => [$data['master']],
                'customer_order_data' => $data['rows'],
            ];
            return $this->display([
                'template' => $template,
                'print_data' => $print_data,
            ]);
        } else {
            $print_tpl = view()->exists(Request::controller().'.print.'.$template_id);
            if ($print_tpl) {
                $data = OrderService::getPrintData($id);
                $data['template'] = $template;
                $tpl = $this->display($data, 'print/'.$template_id);
            } else {
                $tpl = $this->create('print');
            }
            return $print_type == 'pdf' ? print_prince($tpl) : $tpl;
        }
    }

    public function dialog()
    {
        $header = Grid::header([
            'code' => 'customer_order',
            'view_type' => 'dialog',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table']);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->leftJoin('customer_order_data as cod', 'cod.order_id', '=', 'customer_order.id');
            $model->where('cod.use_close', 0);

            $model->orderBy($header['sort'], $header['order']);

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

            if (isset($query['status'])) {
                $model->where('customer_order.status', $query['status']);
            }

            if (isset($query['customer_id'])) {
                $model->where('customer_order.customer_id', $query['customer_id']);
            }

            $limit = $query['limit'] > 0 ? $query['limit'] : 50;
            $header['select'][] = 'sum(cod.money) as total_money';
            $header['select'][] = 'customer_id_customer.code as customer_code';

            $header['raw_select'][] = 'customer_id_customer.code';
            $model->groupBy(DB::raw(join(',', $header['raw_select'])));
            
            $model->selectRaw(join(',', $header['select']));
            $rows = $model->paginate();
            $items = Grid::dataFilters($rows, $header, function($item) {
                $item['text'] = $item['name'];
                return $item;
            });
            return $items;
        }
        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    /**
     * 参照到促销申请
     */
    public function servicePromotion()
    {
        $search = search_form([], [
            ['text','customer.name','客户名称'],
            ['text','customer.code','客户编码'],
        ]);
        $query = $search['query'];

        if (Request::method() == 'POST') {

            if ($query['master']) {
                $model = DB::table('customer_order as m')
                ->leftJoin('customer_order_data as d', 'm.id', '=', 'd.order_id')
                ->leftJoin('product', 'product.id', '=', 'd.product_id')
                ->leftJoin('customer', 'customer.id', '=', 'm.customer_id');

                // 如果促销已经关联了就不显示订单了
                // type_id 是否要处理相同的类型
                if ($query['type_id'] == 1 || $query['type_id'] == 2) {
                    $model->whereRaw("m.id not in(select order_id from promotion where isnull(is_close, 0) = 0 and type_id = ? and (order_id is not null and order_id <> ?))", [(int)$query['type_id'], (int)$query['order_id']]);
                }

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        $model->search($where);
                    }
                }
                if (isset($query['customer_id'])) {
                    $model->where('m.customer_id', (int)$query['customer_id']);
                }
        
                if ($query['type_id'] > 0) {
                    // 物资促销1 赠品促销2 其他费用3
                    if ($query['type_id'] == 1) {
                        $model->where('product.material_type', '>', 0);
                    }
                }

                // 客户权限
                $region = regionCustomer();
                if ($region['authorise']) {
                    foreach ($region['whereIn'] as $key => $where) {
                        $model->whereIn($key, $where);
                    }
                }
                $model->groupBy(DB::raw('m.id, m.sn, m.created_at, customer.code, customer.name'))
                ->selectRaw("m.id, m.sn, m.created_at, customer.code customer_code, customer.name customer_name, sum(d.money) as total_money");
                $rows = $model->get();
            } else {
                $model = DB::table('customer_order_data')
                ->leftJoin('product', 'product.id', '=', 'customer_order_data.product_id')
                ->leftJoin('customer_order', 'customer_order.id', '=', 'customer_order_data.order_id')
                ->leftJoin('product_unit', 'product_unit.id', '=', 'product.unit_id')
                ->leftJoin('customer_order_type', 'customer_order_type.id', '=', 'customer_order_data.type_id')
                ->whereIn('customer_order.id', (array)$query['ids']);

                if (isset($query['customer_id'])) {
                    $model->where('customer_order.customer_id', (int)$query['customer_id']);
                }
                if ($query['type_id'] > 0) {
                    // 物资促销1 赠品促销2 其他费用3
                    // 物资促销时只显示物料产品
                    if ($query['type_id'] == 1) {
                        $model->whereRaw('product.material_type > 0');
                    }
                }

                $model->selectRaw("
                    customer_order_data.*,
                    customer_order_data.delivery_quantity as quantity,
                    product.name as product_name,
                    product.spec as product_spec,
                    product.code as product_code,
                    product.barcode as product_barcode,
                    product.unit_id as unit_id,
                    product_unit.name as product_unit
                ");
                $rows = $model->get();
            }
            return ['data' => $rows];
        }
        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 发货参照订单
    public function serviceDelivery()
    {
        $date = date("Y-m-d", strtotime("+1 day"));
        $search = search_form(
            ['advanced' => ''], [
                ['form_type' => 'date', 'name' => '预计发货日期', 'field' => 'customer_order.plan_delivery_dt', 'options' => []],
                ['form_type' => 'text', 'name' => '订单编号', 'field' => 'customer_order.sn', 'options' => []],
                ['form_type' => 'text', 'name' => '所属客户', 'field' => 'customer.name', 'options' => []],
                ['form_type' => 'text', 'name' => '开票名称', 'field' => 'customer_tax.name', 'options' => []],
                ['form_type' => 'text', 'name' => '客户编码', 'field' => 'customer.code', 'options' => []]
        ], 'model');

        $query = $search['query'];

        if (Request::method() == 'POST') {

            if ($query['is_direct'] == 1) {
                $detail = OrderService::getSaleOrderSelectDetailReqSql();
            } else {
                $detail = OrderService::getSaleOrderSelectDetailSql();
            }

            if ($query['master']) {
                $model = DB::table('customer_order')
                ->whereRaw('customer_order.id in (select sale_id from ('.$detail.') b where sale_id = customer_order.id)')
                ->leftJoin('customer', 'customer.id', '=', 'customer_order.customer_id')
                ->leftJoin('customer_tax', 'customer_tax.id', '=', 'customer_order.tax_id')
                ->whereRaw('customer_order.status = 1')
                ->orderBy('customer_order.id', 'desc');

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        $model->search($where);
                    }
                }

                $model->selectRaw('
                    customer_order.*,
                    customer.code as customer_code,
                    customer.name as customer_name,
                    customer_tax.name as tax_name
                ');
                $rows = $model->get();
            } else {
                $model = DB::query()->selectRaw('* FROM('.$detail.') as a');
                $rows = $model->whereIn('sale_id', (array)$query['ids'])->get(['*', 'sale_data_id as id'])->toArray();
            }
            return $this->json($rows, true);
        }

        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 未发货订单(参照)
    public function serviceNotDelivery()
    {
        $search = search_form(
            ['advanced' => ''], [
                ['form_type' => 'text', 'name' => '客户名称', 'field' => 'customer.name', 'options' => []],
                ['form_type' => 'text', 'name' => '客户编码', 'field' => 'customer.code', 'options' => []],
                ['form_type' => 'text', 'name' => '订单编码', 'field' => 'customer_order.sn', 'options' => []]
        ], 'model');

        $query = $search['query'];

        if (Request::method() == 'POST') {

            // 客户权限
            $region = regionCustomer();

            if ($query['master']) {
                $model = DB::table('customer_order')
                ->whereRaw('exists (select sale_id from ('.OrderService::getSaleOrderSelectDetailNotDeliverySql().') d where customer_order.id = d.sale_id)')
                ->leftJoin('customer', 'customer.id', '=', 'customer_order.customer_id')
                ->orderBy('customer_order.id', 'desc');

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        $model->search($where);
                    }
                }

                if ($region['authorise']) {
                    foreach ($region['whereIn'] as $key => $where) {
                        $model->whereIn($key, $where);
                    }
                }

                $model->selectRaw('
                    customer_order.*,
                    customer.code as customer_code, 
                    customer.name as customer_name
                ');
                $rows = $model->get();
            } else {
                $model = DB::query()->selectRaw('* FROM('.OrderService::getSaleOrderSelectDetailNotDeliverySql().') as a');
                $rows = $model->whereIn('sale_id', (array)$query['ids'])->get(['*', 'sale_data_id as id'])->toArray();
            }
            return $this->json($rows, true);
        }

        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 订单退货(参照)
    public function serviceCancelOrder()
    {
        $search = search_form(
            ['advanced' => ''], [
                ['form_type' => 'text', 'name' => '客户名称', 'field' => 'customer.name', 'options' => []],
                ['form_type' => 'text', 'name' => '客户编码', 'field' => 'customer.code', 'options' => []]
        ], 'model');

        $query = $search['query'];

        if (Request::method() == 'POST') {

            if ($query['master']) {
                $model = DB::table('customer_order')
                ->leftJoin('customer', 'customer.id', '=', 'customer_order.customer_id')
                ->orderBy('customer_order.id', 'desc');

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        $model->search($where);
                    }
                }

                $model->selectRaw('
                    customer_order.*,
                    customer.code as customer_code,
                    customer.name as customer_name
                ');
                $rows = $model->get();
            } else {
                $rows = DB::table('customer_order_data as d')->whereIn('d.order_id', (array)$query['ids'])
                ->leftJoin('customer_order_type as cot', 'cot.id', '=', 'd.type_id')
                ->leftJoin('customer_order as m', 'm.id', '=', 'd.order_id')
                ->leftJoin('product', 'product.id', '=', 'd.product_id')
                ->leftJoin('product_unit', 'product_unit.id', '=', 'product.unit_id')
                ->selectRaw('
                    d.product_id,
                    d.quantity,
                    d.price,
                    d.weight,
                    d.batch_sn,
                    d.batch_date,
                    d.fee_data_id,
                    d.fee_src_id,
                    d.fee_src_type_id,
                    d.fee_src_sn,
                    d.promotion_sn,
                    d.promotion_data_id,
                    d.order_id,
                    m.sn as sale_sn,
                    d.type_id,
                    cot.name as type_id_name,
                    product.code as product_code,
                    product.name as product_name,
                    product.barcode as product_barcode,
                    product.spec as product_spec,
                    product_unit.name as product_unit
                ')
                ->get();
            }
            return $this->json($rows, true);
        }

        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    public function serviceCustomerMoney()
    {
        $tax_id = Request::get('tax_id');
        $tax = DB::table('customer_tax')->where('id', $tax_id)->first();

        $accMoney = 0;
        $freMoney = 0;
        $lockMoney = 0;
        $avaMoney = 0;

        if ($tax) {
            // 获取外部接口数据
            $a = plugin_sync_api('getAccInfo/code/'.$tax['code']);
            $b = CustomerService::getLockMoney($tax['id']);

            $accMoney = floatval($a['deARBal']);
            $freMoney = floatval($a['iFreMoney']);
            $lockMoney = floatval($b[0]['money']);
            $avaMoney = floatval($accMoney - $lockMoney);
        }
        $data = [
            'accMoney' => $accMoney,
            'freMoney' => $freMoney,
            'lockMoney' => $lockMoney,
            'avaMoney' => $avaMoney,
        ];
        return $data;
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            return Form::remove(['code' => 'customer_order', 'ids' => $id]);
        }
    }
}
