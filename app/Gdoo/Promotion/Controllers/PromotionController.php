<?php namespace Gdoo\Promotion\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\Promotion\Models\Promotion;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Services\BadgeService;
use Gdoo\Promotion\Services\PromotionService;

use Gdoo\Index\Controllers\WorkflowController;

class PromotionController extends WorkflowController
{
    public $permission = ['dialog', 'serviceSaleOrder', 'useCount', 'product'];

    public function index()
    {
        // 客户权限
        $region = regionCustomer('customer_id_customer');

        $header = Grid::header([
            'code' => 'promotion',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        // 自定义列
        $customFields = [
            'zp_money' => [
                'headerName' => '赠品金额(元)',
                'field' => 'zp_money',
                'calcFooter' => 'sum',
                'width' => 100,
                'suppressMenu' => true,
                'type' => 'number',
                'cellStyle' => ['text-align' => 'right'],
            ]
        ];
        $cols = Grid::addColumns($cols, 'master_cash_amount', $customFields);

        $cols['master_product']['cellRenderer'] = 'htmlCellRenderer';
        $cols['master_cash_amount']['cellRenderer'] = 'htmlCellRenderer';

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
        $header['tabs'] = Promotion::$tabs;
        $header['bys'] = Promotion::$bys;

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

            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            $model->leftJoin(DB::raw('(SELECT sum(fact_verification_cost) as master_cash_amount, min(date) as master_cash_date, apply_id FROM promotion_review where status = 1 group by apply_id) as b'), 'promotion.id', '=', 'b.apply_id');
            $header['select'][] = 'b.master_cash_amount';
            $header['select'][] = 'b.master_cash_date';

            $model->select($header['select']);
            $model->addSelect(DB::raw("case when promotion.type_id = 2 then promotion.undertake_money else 0 end as zp_money"));

            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header, function($item) {
                $item['master_cash_amount'] = '<a href="javascript:;" data-toggle="event" data-action="fee_detail" data-master_id="'.$item['master_id'].'" class="option">'.$item['master_cash_amount'].'</a>';
                $item['master_product'] = '<a href="javascript:;" data-toggle="event" data-action="product" data-master_id="'.$item['master_id'].'" class="option">明细</a>';
                return $item;
            });
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function create($action = 'edit')
    {
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'promotion';
        $header['id'] = $id;

        // 客户权限
        $header['region'] = ['field' => 'customer_id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        // 获取核销单
        $review = DB::table('promotion_review')->where('apply_id', $id)->first();
        $header['joint'] = [
            ['name' => '关联订单', 'action' => 'sale_order', 'field' => 'order_id'],
            ['name' => '核销单', 'action' => 'promotion_review', 'field' => 'id'],
            ['name' => '核销资料', 'action' => 'material', 'field' => 'id'],
        ];

        $form = Form::make($header);
        $tpl = $action == 'print' ? 'print' : 'create';
        return $this->display([
            'form' => $form,
            'review' => $review,
        ], $tpl);
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

    public function print()
    {
        $id = (int) Request::get('id');
        $template_id = (int) Request::get('template_id');
        $header['action'] = 'print';
        $header['code'] = 'promotion';
        $header['id'] = $id;
        $header['template_id'] = $template_id;
        $this->layout = 'layouts.print2';
        $form = Form::make($header);
        $form['template']['name'] = '促销申请';
        print_prince($this->display([
            'form' => $form,
        ], 'print'));
    }

    // 赠品促销参照到订单
    public function serviceSaleOrder()
    {
        $search = search_form(
            ['advanced' => ''], [
                ['form_type' => 'text', 'name' => '促销编号', 'field' => 'promotion_sn', 'options' => []],
                ['form_type' => 'text', 'name' => '客户名称', 'field' => 'customer_id_customer.name', 'options' => []],
        ], 'model');

        $header = Grid::header([
            'code' => 'promotion',
        ]);
        $_search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {

            if ($query['master']) {

                $model = DB::table('promotion');
                foreach ($header['join'] as $join) {
                    $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
                }

                $model->where('promotion.customer_id', $query['customer_id'])
                ->whereRaw('promotion.id in (select promotion_id from ('.PromotionService::getSurplusPromotionSql().') as sp where type_id in (1, 2))')
                ->where('promotion.customer_id', $query['customer_id'])
                ->whereRaw('isnull(promotion.is_close, 0) = 0');
                
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

                $model->selectRaw('
                    promotion.*,
                    promotion.status as master_status,
                    promotion.id as master_id,
                    customer_id_customer.code as customer_code,
                    customer_id_customer.name as customer_name,
                    customer_id_customer.warehouse_contact,
                    customer_id_customer.warehouse_phone,
                    customer_id_customer.warehouse_address
                ');
                $rows = $model->get();
                $rows = Grid::dataFilters($rows, $header, function($row) {
                    if ($row['type_id'] == 1) {
                        $row['type_name'] = '物资促销';
                    } else if($row['type_id'] == 2) {
                        $row['type_name'] = '赠品促销';
                    }
                    return $row;
                });

            } else {
 
                $sql = "temp.price,
                temp.quantity,
                temp.ysy_num,
                temp.wsy_num,
                temp.product_id,
                temp.id,
                temp.promotion_sn,
                temp.promotion_id,
                temp.type_id,
                temp.customer_id,
                temp.promotion_data_id,
                temp.customer_name,
                temp.customer_code,
                p.code AS product_code,
                p.name AS product_name,
                p.spec AS product_spec,
                u.name AS product_unit,
                p.weight,

                temp.promotion_id as fee_src_id,
                temp.promotion_sn as fee_src_sn,
                temp.id as promotion_data_id

                FROM (SELECT isnull(a.price, 0) AS price,
                    isnull(a.quantity, 0) AS quantity,
                    isnull(d.quantity, 0) AS ysy_num,
                    isnull(a.quantity, 0) - isnull(d.quantity, 0) AS wsy_num,
                    a.product_id,
                    a.id,
                    b.sn AS promotion_sn,
                    b.id AS promotion_id,
                    b.type_id,
                    b.customer_id,
                    d.promotion_data_id,
                    c.name AS customer_name,
                    c.code AS customer_code
                    FROM promotion_data a
                        LEFT JOIN promotion b ON a.promotion_id = b.id
                        LEFT JOIN customer c ON b.customer_id = c.id
                        LEFT JOIN ( SELECT sum(isnull(customer_order_data.delivery_quantity, 0)) AS quantity,
                            customer_order_data.promotion_data_id
                            FROM customer_order_data
                            WHERE (customer_order_data.promotion_data_id IS NOT NULL)
                            GROUP BY customer_order_data.promotion_data_id
                        ) d ON a.id = d.promotion_data_id
                ) temp
                LEFT JOIN product p ON temp.product_id = p.id
                LEFT JOIN product_unit u ON p.unit_id = u.id";
                $model = DB::query()->selectRaw($sql);
                $model->whereRaw('isnull(temp.wsy_num, 0) > 0')
                ->whereIn('type_id', [1, 2])
                ->where('customer_id', $query['customer_id'])
                ->whereIn('promotion_id', (array)$query['ids']);

                if ($query['sort'] && $query['order']) {
                    $model->orderBy($query['sort'], $query['order']);
                }

                $rows = $model->get();
                $rows->transform(function ($row) {
                    // 赠品
                    if ($row['type_id'] == 2) {
                        $row['fee_src_type_id'] = 17;
                        $row['fee_category_id'] = 6;
                        $row['fee_category_id_name'] = '赠品';
                        $row['type_id'] = 2;
                        $row['type_id_name'] = '赠品';
                    // 物料
                    } else if ($row['type_id'] == 1) {
                        $row['fee_src_type_id'] = 17;
                        $row['fee_category_id'] = 7;
                        $row['fee_category_id_name'] = '物料';
                        $row['type_id'] = 4;
                        $row['type_id_name'] = '物料';
                    }
                    return $row;
                });
            }
            return $this->json($rows, true);
        }

        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 产品明细
    public function product()
    {
        $query = Request::all();
        if (Request::method() == 'POST') {
            $rows = DB::table('promotion_data')
            ->leftJoin('product', 'product.id', '=', 'promotion_data.product_id')
            ->where('promotion_id', $query['id'])
            ->orderBy('product.code', 'asc')
            ->get(['product.*']);
            return $this->json($rows, true);
        }
        return $this->render(['query' => $query]);
    }

    public function close()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $row = DB::table('promotion')->where('id', $gets['id'])->first();
            DB::table('promotion')->where('id', $gets['id'])->update([
                'is_close' => !$row['is_close']
            ]);
            return $this->json('操作成功。', true);
        }
    }
    
    // 可用列表
    public function useCount()
    {
        $customer_id = Request::get('customer_id');

        $count = DB::table('promotion')
        ->where('promotion.customer_id', $customer_id)
        ->whereRaw('isnull(promotion.is_close, 0) = 0 and promotion.id in (select promotion_id from ('.PromotionService::getSurplusPromotionSql().') as sp where type_id in (1,2))')
        ->count('promotion.id');

        return $this->json($count, true);
    }

    public function dialog()
    {
        $search = search_form(
            ['advanced' => ''], [
                ['form_type' => 'text', 'name' => '客户名称', 'field' => 'customer_id_customer.name', 'options' => []],
                ['form_type' => 'text', 'name' => '促销编号', 'field' => 'promotion.sn', 'options' => []],
        ], 'model');

        $header = Grid::header([
            'code' => 'promotion',
        ]);

        $_search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {

            if ($query['master']) {

                $model = DB::table('promotion');

                foreach ($header['join'] as $join) {
                    $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
                }
                
                $model->whereRaw('NOT EXISTS (select apply_id from promotion_review where apply_id = promotion.id and apply_id IS NOT NULL)')
                ->where('promotion.need_review', 1)
                ->where('promotion.status', 1)
                ->whereRaw('isnull(promotion.is_close, 0) = 0')
                ->orderBy('promotion.id', 'desc');

                if ($query['sort'] && $query['order']) {
                    $model->orderBy($query['sort'], $query['order']);
                }

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

                $model->selectRaw('
                    promotion.sn,
                    promotion.created_at as apply_dt,
                    promotion.start_dt,
                    promotion.end_dt,
                    promotion.promote_scope,
                    '.sql_float_varchar('promotion.apply_fee', 20).' AS apply_money,
                    '.sql_float_varchar('promotion.undertake_money', 20).' AS undertake_money,
                    '.sql_float_varchar('promotion.undertake_money', 20).' AS verification_cost,
                    promotion.status as master_status,
                    promotion.id as master_id,
                    promotion.id,
                    promotion.customer_id,
                    customer_id_customer.region_id,
                    region_id_customer_region.name as region_name,
                    customer_id_customer.code as customer_code,
                    customer_id_customer.name as customer_name,
                    customer_id_customer.warehouse_contact,
                    customer_id_customer.warehouse_phone,
                    customer_id_customer.warehouse_address
                ');

                $rows = $model->get();
                $rows = Grid::dataFilters($rows, $header);
                
            } else {

                $model = DB::table('promotion_data')
                ->leftJoin('product', 'product.id', '=', 'promotion_data.product_id')
                ->leftJoin('promotion', 'promotion.id', '=', 'promotion_data.promotion_id')
                ->leftJoin('product_unit', 'product_unit.id', '=', 'product.unit_id');

                $model->whereRaw('NOT EXISTS (select apply_id from promotion_review where apply_id = promotion.id and apply_id IS NOT NULL)')
                ->whereIn('promotion.id', (array)$query['ids']);

                if ($query['sort'] && $query['order']) {
                    $model->orderBy($query['sort'], $query['order']);
                }

                $model->selectRaw("
                    promotion_data.*,
                    promotion.id as promotion_id,
                    promotion.sn as promotion_sn,
                    product.code as product_code,
                    product.name as product_name,
                    product.spec as product_spec,
                    product.barcode as product_barcode,
                    product.unit_id as unit_id,
                    product_unit.name as product_unit,
                    product.weight,
                    promotion.id as apply_id,
                    '1' as apply_type_id,
                    promotion_data.id as apply_data_id
                ");
                $rows = $model->get();
            }
            return $this->json($rows, true);
        }

        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 批量编辑
    public function batchEdit()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            DB::table('promotion')->whereIn('id', $ids)->update([
                $gets['field'] => $gets['search_0'],
            ]);
            return $this->json('修改完成。', true);
        }
        $header = Grid::batchEdit([
            'code' => 'promotion',
            'columns' => ['customer_id', 'tax_id'],
        ]);
        return view('batchEdit', [
            'gets' => $gets,
            'header' => $header
        ]);
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'promotion', 'ids' => $ids]);
        }
    }
}
