<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Customer\Models\Customer;
use Gdoo\User\Models\User;

use Gdoo\Index\Controllers\DefaultController;

class CustomerController extends DefaultController
{
    public $permission = ['dialog'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'customer',
            'referer' => 1,
            'sort' => 'customer.id',
            'order' => 'asc',
            'search' => ['by' => 'enabled'],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ],[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $this->access['edit'],
        ]];

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order']);

            if ($query['by']) {
                if ($query['by'] == 'enabled') {
                    $model->where('customer.status', 1);
                }
                if ($query['by'] == 'disabled') {
                    $model->where('customer.status', 0);
                }
            }
 
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

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);

            $items = Grid::dataFilters($rows, $header, function($item) {
                return $item;
            });
            return $items->toJson();
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-mail-reply', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '批量编辑', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'batchEdit', 'display' => $this->access['batchEdit']],
            ['name' => '销售产品价格', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'priceEdit', 'display' => $this->access['priceEdit']],
        ];
        
        $header['right_buttons'] = [
            ['name' => '导入', 'color' => 'default', 'icon' => 'fa-mail-reply', 'action' => 'import', 'display' => $this->access['import']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Customer::$tabs;
        $header['bys'] = Customer::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    // 新建客户联系人
    public function createAction($action = 'create')
    {
        $id = (int)Request::get('id');

        // 客户权限
        $header['region'] = ['field' => 'id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        $header['code'] = 'customer';
        $header['id'] = $id;
        $header['action'] = $action;

        $form = Form::make($header);

        $taxs = [];
        if ($action == 'show') {
            $taxs = DB::table('customer_tax')->where('customer_id', $id)->get();
        }
        return $this->display([
            'form' => $form,
            'taxs' => $taxs,
        ], 'create');
    }

    // 创建客户联系人
    public function editAction()
    {
        return $this->createAction('edit');
    }

    // 批量编辑
    public function batchEditAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            DB::table('customer')->whereIn('id', $ids)->update([
                $gets['field'] => $gets['search_0'],
            ]);
            return $this->json('修改完成。', true);
        }
        $header = Grid::batchEdit([
            'code' => 'customer',
            'columns' => ['region3_id', 'region2_id', 'region_id', 'grade_id', 'type_id', 'class2_id', 'class_id', 'department_id', 'status'],
        ]);
        return view('batchEdit', [
            'gets' => $gets,
            'header' => $header
        ]);
    }

    // 销售产品价格
    public function priceEditAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            $product_id = $gets['product_id'];
            $price = floatval($gets['price']);

            DB::beginTransaction();
            try {
                foreach($ids as $id) {
                    // 查找客户价格本
                    $count = DB::table('customer_price')
                    ->where('customer_id', $id)
                    ->where('product_id', $product_id)
                    ->count();

                    if ($count == 0) {
                        DB::table('customer_price')->insert([
                            'customer_id' => $id,
                            'product_id' => $product_id,
                            'price' => $price,
                        ]);
                    }
                }
                // 提交事务
                DB::commit();
                return $this->json('更新成功。', true);
            } catch (\Exception $e) {
                DB::rollback();
                abort_error($e->getMessage());
            }
        }
        return $this->render([
            'gets' => $gets,
        ]);
    }

    // 显示客户联系人
    public function showAction()
    {
        return $this->createAction('show');
    }

    // 数据导入
    public function importAction()
    {
        if (Request::method() == 'POST') {
            return Form::import(['table' => 'customer', 'keys' => ['code', 'username']]);
        }
        $tips = '注意：表格里必须包含[客户代码]列。';
        return $this->render(['tips' => $tips], 'layouts.import');
    }

    // 删除
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'customer', 'ids' => $ids]);
        }
    }

    /**
     * 弹出层信息
     */
    public function dialogAction()
    {
        $search = search_form(
            ['advanced' => ''], [
                ['form_type' => 'text', 'name' => '客户名称', 'field' => 'customer.name', 'options' => []],
                ['form_type' => 'text', 'name' => '客户编码', 'field' => 'customer.code', 'options' => []],
                ['form_type' =>'dialog', 'field' => 'customer.region_id', 'name' => '销售区域', 'options' => ['url' => 'customer/region/dialog', 'query' => ['layer' => 3]]],
        ], 'model');

        $header = Grid::header([
            'code' => 'customer',
        ]);
        $_search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table']);
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
            $region = regionCustomer();
            if ($region['authorise']) {
                foreach ($region['whereIn'] as $key => $where) {
                    $model->whereIn($key, $where);
                }
            }

            if ($query['region_id']) {
                $model->where('customer.region_id', $query['region_id']);
            }

            $header['select'][] = 'customer.user_id';
            $model->select($header['select']);

            // 获取默认收货地址
            $sqlsrv = $pgsql = '';
            if ($this->dbType == 'sqlsrv') {
                $sqlsrv = 'top 1';
            } else if($this->dbType == 'pgsql') {
                $pgsql = 'LIMIT 1';
            }   
            $model->leftJoin(DB::raw('(select '.$sqlsrv.' max(id) as delivery_address_id, customer_id, name as warehouse_contact2, phone as warehouse_phone2, address as warehouse_address2, tel as warehouse_tel2
                    FROM customer_delivery_address
                    where is_default = 1
                    GROUP BY customer_id, name, phone, address, tel
                    '.$pgsql.'
            ) cda
            '), 'customer.id', '=', 'cda.customer_id');

            $model->addSelect(DB::raw('cda.*'));

            if ($query['suggest']) {
                if ($query['q']) {
                    $q = $query['q'];
                    $model->whereRaw("(customer.code like '%$q%' or customer.name like '%$q%')");
                }
                $rows = $model->limit(15)->get();
                $data = Grid::dataFilters($rows, $header, function($item) {
                    return $item;
                });
                $items['data'] = $data;
            } else {
                $rows = $model->paginate($query['limit']);
                $items = Grid::dataFilters($rows, $header, function($item) {
                    $item['text'] = $item['name'];
                    $item['sid'] = 'u'.$item['user_id'];
                    return $item;
                });
            }
            return response()->json($items);
        }
        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }
}
