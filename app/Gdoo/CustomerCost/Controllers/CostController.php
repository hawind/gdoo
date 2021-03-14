<?php namespace Gdoo\CustomerCost\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\CustomerCost\Models\Cost;
use Gdoo\CustomerCost\Models\CostData;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\AuditController;

class CostController extends AuditController
{
    public $permission = ['dialog', 'serviceSaleOrder', 'useCount'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'customer_cost',
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

            // 客户权限
            $region = regionCustomer('customer_id_customer');
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    if ($where['field'] == 'customer_cost_data.tax_id') {
                        $customer_ids = DB::table('customer_tax')->where('id', $where['search'])->pluck('customer_id');
                        $model->whereIn('customer_cost_data.customer_id', $customer_ids);
                    } else {
                        $model->search($where);
                    }
                }
            }

            $header['select'][] = 'customer_cost_data.src_id';
            $header['select'][] = 'customer_cost_data.src_type_id';
            $header['select'][] = 'customer_cost_data.cost_id';
            $model->select($header['select']);

            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '批量编辑', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'batchEdit', 'display' => $this->access['batchEdit']],
        ];

        $header['right_buttons'] = [
            ['name' => '关闭', 'color' => 'default', 'icon' => 'fa-lock', 'action' => 'close', 'display' => $this->access['close']],
            ['name' => '费用调整单', 'color' => 'info', 'icon' => 'fa-plus', 'action' => 'create4', 'display' => $this->access['create4']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Cost::$tabs;
        $header['bys'] = Cost::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction($action = 'edit')
    {
        $id = (int)Request::get('id');

        // 客户权限
        $header['region'] = ['field' => 'customer_id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        $header['code'] = 'customer_cost';
        $header['id'] = $id;
        $header['action'] = $action;

        $form = Form::make($header);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    public function editAction()
    {
        return $this->createAction();
    }

    public function showAction()
    {
        return $this->createAction('show');
    }

    // 费用参照到订单
    public function serviceSaleOrderAction() 
    {
        $header = Grid::header([
            'code' => 'customer_cost',
            'type' => 'dialog',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table('customer_cost_data')
            ->leftJoin('customer_cost', 'customer_cost.id', '=', 'customer_cost_data.cost_id')
            ->leftJoin('customer', 'customer.id', '=', 'customer_cost_data.customer_id')
            ->leftJoin('customer_cost_category', 'customer_cost_category.id', '=', 'customer_cost.category_id')
            ->whereRaw('(isnull(customer_cost_data.remain_money, 0) > 0 and isnull(customer_cost_data.use_close, 0) = 0)')
            ->where('customer_cost_data.customer_id', $query['customer_id'])
            ->selectRaw("
                customer_cost_data.*,
                customer.name as customer_name,
                customer.code as customer_code,

                customer_cost.sn,

                customer_cost_data.src_sn as fee_src_sn,
                customer_cost_data.src_type_id as fee_src_type_id,
                customer_cost_data.src_id as fee_src_id,
                customer_cost_data.id as fee_data_id,

                customer_cost_category.name as fee_category_id_name,
                customer_cost.category_id as fee_category_id,
                
                '99001' as product_code,
                '20226' as product_id,
                '折扣额' as product_name,
                '元' as product_unit,
                '5' as type_id,
                '费用' as type_id_name,
                customer_cost_data.money as total_money,
                customer_cost_data.use_money,
                isnull(customer_cost_data.remain_money, 0) as money
            ");

            if ($query['sort'] && $query['order']) {
                $model->orderBy($query['sort'], $query['order']);
            }

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $rows = $model->get();
            $rows->transform(function($row) {
                return $row;
            });
            return ['data' => $rows];
        }

        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }

    // 可用列表
    public function useCountAction()
    {
        $customer_id = Request::get('customer_id');
        $count = DB::table('customer_cost_data')
        ->where('customer_id', $customer_id)
        ->whereRaw('(isnull(remain_money, 0) > 0 and isnull(use_close, 0) = 0)')
        ->count();
        return $this->json($count, true);
    }

    // 批量编辑
    public function batchEditAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            DB::table('customer_cost_data')->whereIn('id', $ids)->update([
                $gets['field'] => $gets['search_0'],
            ]);
            return $this->json('修改完成。', true);
        }
        $header = Grid::batchEdit([
            'code' => 'customer_cost',
            'columns' => ['customer_id'],
        ]);
        return view('batchEdit', [
            'gets' => $gets,
            'header' => $header
        ]);
    }

    // 费用关闭
    public function closeAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            if (empty($gets['close_remark'])) {
                return $this->json('原因必须填写。');
            }
            $cost = CostData::find($gets['id']);
            $cost->close_remark = $gets['close_remark'];
            $cost->use_close = $cost->use_close == 1 ? 0 : 1;
            $cost->save();
            return $this->json('恭喜你，操作成功。', url_referer('index'));
        }
        return $this->render([
            'gets' => $gets,
        ]);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'customer_cost', 'ids' => $ids]);
        }
    }
}
