<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Customer\Models\DeliveryAddress;

use Gdoo\Index\Controllers\DefaultController;

class DeliveryAddressController extends DefaultController
{
    public $permission = ['dialog'];

    public function index()
    {
        $header = Grid::header([
            'code' => 'customer_delivery_address',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        // 客户权限
        $region = regionCustomer('customer_id_customer');
 
        $cols['actions']['options'] = [[
            'name'  => '编辑',
            'action' => 'edit',
            'display' => $this->access['edit'],
        ]];

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
        ];
        $header['cols'] = $cols;
        $header['tabs'] = DeliveryAddress::$tabs;
        $header['bys'] = DeliveryAddress::$bys;

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

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function create($action = 'edit')
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'customer_delivery_address','id' => $id, 'action' => $action]);
        return $this->render([
            'form' => $form,
        ], 'create');
    }

    public function edit()
    {
        return $this->create();
    }

    public function show()
    {
        return $this->create('show');
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'customer_delivery_address', 'ids' => $ids]);
        }
    }

    public function dialog()
    {
        $header = Grid::header([
            'code' => 'customer_delivery_address',
            'prefix' => '',
        ]);
        $search = $header['search_form'];
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

            if ($query['q']) {
                $model->where('customer_delivery_address.address', 'like', '%'. $query['q'] .'%');
            }

            $model->whereRaw('customer_delivery_address.customer_id > 0 and customer_delivery_address.customer_id = ?', [$query['customer_id']]);
            
            $header['select'][] = 'customer_delivery_address.id';
            $header['select'][] = 'customer_delivery_address.address as text';

            if ($query['related'] == '0') {
                $header['select'][] = 'customer_delivery_address.address as id';
            }

            $model->select($header['select']);

            $rows = $model->paginate($query['limit']);
            return $rows;
        }
        return $this->render([
            'search' => $search,
            'query' => $query,
        ]);
    }
}
