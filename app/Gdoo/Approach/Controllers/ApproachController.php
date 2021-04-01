<?php namespace Gdoo\Approach\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Approach\Models\Approach;
use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\WorkflowController;

class ApproachController extends WorkflowController
{
    public $permission = ['dialog', 'reference', 'useCount', 'serviceReview', 'serviceCostList', 'serviceCostDetail', 'product'];

    public function index()
    {
        // 客户权限
        $region = regionCustomer('customer_id_customer');

        $header = Grid::header([
            'code' => 'approach',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];
            
        $cols['master_product']['cellRenderer'] = 'htmlCellRenderer';
        $cols['master_cash_amount']['cellRenderer'] = 'htmlCellRenderer';

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => 0],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '批量编辑', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'batchEdit', 'display' => $this->access['batchEdit']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Approach::$tabs;
        $header['bys'] = Approach::$bys;

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {

            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }

            $model->leftJoin(DB::raw('(SELECT sum(verification_cost) as master_cash_amount, min(date) as master_cash_date, apply_id FROM approach_review where status = 1 group by apply_id) as b'), 'approach.id', '=', 'b.apply_id');
            $header['select'][] = 'b.master_cash_amount';
            $header['select'][] = 'b.master_cash_date';
            
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

            $model->select($header['select']);
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

        // 客户权限
        $header['region'] = ['field' => 'customer_id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        $header['action'] = $action;
        $header['code'] = 'approach';
        $header['id'] = $id;
        
        $form = Form::make($header);
        $tpl = $action == 'print' ? 'print' : 'create';
        return $this->display([
            'form' => $form,
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
        $this->layout = 'layouts.print_html';
        print_prince($this->create('print'));
    }

    // 关闭操作
    public function close()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $row = DB::table('approach')->where('id', $gets['id'])->first();
            DB::table('approach')->where('id', $gets['id'])->update([
                'is_close' => !$row['is_close']
            ]);
            return $this->json('操作成功。', true);
        }
    }

    // 产品明细
    public function product()
    {
        $query = Request::all();
        if (Request::method() == 'POST') {
            $rows = DB::table('approach_data')
            ->leftJoin('product', 'product.id', '=', 'approach_data.product_id')
            ->where('approach_id', $query['id'])
            ->orderBy('product.code', 'asc')
            ->get(['product.*']);
            return $this->json($rows, true);
        }
        return $this->render(['query' => $query]);
    }

    // 核销单选择
    public function serviceReview()
    {
        $header = Grid::header([
            'code' => 'approach',
            'prefix' => '',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            if ($query['master']) {
                $model = DB::table('approach');
                foreach ($header['join'] as $join) {
                    $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
                }
                $model->leftJoin('approach_market', 'approach_market.id', '=', 'approach.market_id')
                ->where('approach.status', 1)
                ->orderBy('approach.id', 'desc');

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        $model->search($where);
                    }
                }
                $model->selectRaw('
                    distinct(approach.id),
                    approach.id,
                    approach.sn,
                    approach.status,
                    approach.barcode_cast,
                    approach.apply2_money,
                    approach.created_at,
                    approach.customer_id,
                    customer_id_customer.region_id,
                    approach_market.name as market_name,
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

                $model = DB::table('approach_data')
                ->leftJoin('product', 'product.id', '=', 'approach_data.product_id')
                ->leftJoin('approach', 'approach.id', '=', 'approach_data.approach_id')
                ->leftJoin('product_unit', 'product_unit.id', '=', 'product.unit_id')
                ->whereIn('approach.id', (array)$query['ids']);
                if ($query['sort'] && $query['order']) {
                    $model->orderBy($query['sort'], $query['order']);
                }

                foreach ($search['where'] as $where) {
                    if ($where['active']) {
                        $model->search($where);
                    }
                }
                $model->selectRaw("
                    approach_data.*,
                    approach.id as approach_id,
                    approach.sn as approach_sn,
                    product.code as product_code,
                    product.name as product_name,
                    product.spec as product_spec,
                    product.barcode as product_barcode,
                    product.unit_id as unit_id,
                    product_unit.name as product_unit,
                    product.weight
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

    // 费用申请明细
    public function serviceCostDetail()
    {
        $query = Request::all();
        $customer_id = (int)$query['customer_id'];
        $date = empty($query['date']) ? date('Y-m-d') : $query['date'];
        $year = date('Y', strtotime($date));

        if (Request::method() == 'POST') {

            if ($query['type'] == 'promotion') {
                $rows = DB::table('promotion')
                ->where('type_id', 2)
                ->whereRaw('customer_id=? and '.sql_year('actived_dt').'=? and isnull(is_close, 0) = 0 and isnull(status, 0) <> 0', [$customer_id, $year])
                ->get();
            }
            if ($query['type'] == 'approach') {
                $rows = DB::table('approach')
                ->whereRaw('customer_id=? and '.sql_year('actived_dt').'=? and isnull(is_close, 0) = 0 and isnull(status, 0) <> 0', [$customer_id, $year])
                ->get();
            }
            if ($query['type'] == 'material') {
                $rows = DB::table('promotion')
                ->where('type_id', 1)
                ->whereRaw('customer_id=? and '.sql_year('actived_dt').'=? and isnull(is_close, 0) = 0 and isnull(status, 0) <> 0', [$customer_id, $year])
                ->get();
            }
            return $this->json($rows, true);
        }

        $approach = DB::table('approach')
        ->whereRaw('customer_id=? and '.sql_year('actived_dt').'=? and isnull(is_close, 0) = 0 and isnull(status, 0) <> 0', [$customer_id, $year])
        ->selectRaw('sum(barcode_cast) as apply_money,sum(apply2_money) as support_money')
        ->first();

        $promotion = DB::table('promotion')
        ->whereRaw('customer_id=? and '.sql_year('actived_dt').'=? and isnull(is_close, 0) = 0 and isnull(status, 0) <> 0', [$customer_id, $year])
        ->selectRaw('sum(apply_fee) as apply_money,sum(undertake_money) as support_money')
        ->first();

        $apply_money = $approach['apply_money'] + $promotion['apply_money'];
        $support_money = $approach['support_money'] + $promotion['support_money'];

    
        // 发货
        $delivery = DB::table('stock_delivery_data as d')
        ->leftJoin('stock_delivery as m', 'm.id', '=', 'd.delivery_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->whereRaw('m.customer_id=? and '.sql_year('m.invoice_dt').'=? and d.product_id <> 20226 and isnull(product.product_type, 0) = 1', [$customer_id, $year])
        ->selectRaw('sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money');
        // 退货
        $cancel = DB::table('stock_cancel_data as d')
        ->leftJoin('stock_cancel as m', 'm.id', '=', 'd.cancel_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->whereRaw('m.customer_id=? and '.sql_year('m.invoice_dt').'=? and d.product_id <> 20226 and isnull(product.product_type, 0) = 1', [$customer_id, $year])
        ->selectRaw('sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money');
        // 直营
        $direct = DB::table('stock_direct_data as d')
        ->leftJoin('stock_direct as m', 'm.id', '=', 'd.direct_id')
        ->leftJoin('product', 'product.id', '=', 'd.product_id')
        ->whereRaw('m.customer_id=? and '.sql_year('m.invoice_dt').'=? and d.product_id <> 20226 and isnull(product.product_type, 0) = 1', [$customer_id, $year])
        ->selectRaw('sum(isnull(d.money, 0) - isnull(d.other_money, 0)) money');
        $rows = $cancel->unionAll($delivery)->unionAll($direct)->get();
        $money = $rows->sum('money');

        $apply_percent = $support_percent = 0;
        if ($money > 0) {
            $apply_percent = ($apply_money / $money) * 100;
            $support_percent = ($support_money / $money) * 100;
        }

        $all = [
            'money' => $money,
            'apply_money' => $apply_money,
            'support_money' => $support_money,
            'apply_percent' => $apply_percent,
            'support_percent' => $support_percent,
        ];

        return $this->render([
            'all' => $all,
            'query' => $query,
        ]);
    }

    // 批量编辑
    public function batchEdit()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            DB::table('approach')->whereIn('id', $ids)->update([
                $gets['field'] => $gets['search_0'],
            ]);
            return $this->json('修改完成。', true);
        }
        $header = Grid::batchEdit([
            'code' => 'approach',
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
            return Form::remove(['code' => 'approach', 'ids' => $ids]);
        }
    }
}