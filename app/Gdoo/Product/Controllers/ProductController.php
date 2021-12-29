<?php namespace Gdoo\Product\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Product\Models\Product;
use Gdoo\Product\Models\ProductCategory;

use Gdoo\Model\Grid;

use Gdoo\Model\Models\Model;
use Gdoo\Model\Models\Field;

use Gdoo\Model\Form;

use Gdoo\Index\Services\NotificationService;

use Gdoo\Index\Controllers\DefaultController;

class ProductController extends DefaultController
{
    public $permission = ['dialog', 'show', 'category', 'serviceCustomer'];

    public function index()
    {
        $header = Grid::header([
            'code' => 'product',
            'sort' => 'product.code',
            'order' => 'asc',
            'referer' => 1,
            'search' => ['by' => ''],
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

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];
        $header['right_buttons'] = [
            ['name' => '导入', 'color' => 'default', 'icon' => 'fa-mail-reply', 'action' => 'import', 'display' => $this->access['import']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Product::$tabs;
        $header['bys'] = Product::$bys;

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->leftJoin('product_category', 'product_category.id', '=', 'product.category_id');
            $model->orderBy($header['sort'], $header['order']);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            if ($query['category_id'] > 0) {
                $categoryIds = DB::table('product_category')
                ->treeById($query['category_id'])->pluck('id');
                $model->whereIn('product.category_id', $categoryIds);
            }

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function create()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'product', 'id' => $id]);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    public function edit()
    {
        return $this->create();
    }

    public function show()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'product', 'id' => $id, 'action' => 'show']);
        return $this->display([
            'form' => $form,
        ], 'create');
    }

    // 数据导入
    public function import()
    {
        if (Request::method() == 'POST') {
            return Form::import(['table' => 'product', 'keys' => ['code', 'category_id', 'unit_id']]);
        }
        $tips = '注意：表格里必须包含[存货编码]列。';
        return $this->render(['tips' => $tips], 'layouts.import');
    }

    public function dialog()
    {
        $header = Grid::header([
            'code' => 'product',
        ]);
        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = Product::query();
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->leftJoin('product_category', 'product_category.id', '=', 'product.category_id');
            $model->where('product_category.type', 1);
            $model->where('product.status', 1)
            ->orderBy('product.code', 'asc');

            if ($query['category_id'] > 0) {
                $categoryIds = DB::table('product_category')
                ->treeById($query['category_id'])->pluck('id');
                $model->whereIn('product.category_id', $categoryIds);
            }

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $header['select'][] = 'product.weight';
            $model->select($header['select']);

            $q = $query['q'];
            if ($q) {
                $model->whereRaw("(product.code like '%$q%' or product.name like '%$q%' or product.spec like '%$q%' or product.barcode like '%$q%')");
            }

            if ($query['suggest']) {
                $rows = $model->limit(15)->get();
            } else {
                $rows = $model->paginate($query['limit']);
            }
            return Grid::dataFilters($rows, $header);
        }
        return $this->render([
            'search' => $search,
        ]);
    }

    /**
     * 客户产品列表
     */
    public function serviceCustomer()
    {
        $search = search_form(
            ['advanced' => ''], [
                ['form_type' => 'text', 'name' => '产品名称', 'field' => 'product.name', 'options' => []],
                ['form_type' => 'text', 'name' => '产品条码', 'field' => 'product.barcode', 'options' => []],
                ['form_type' => 'text', 'name' => '产品编码', 'field' => 'product.code', 'options' => []]
        ], 'model');

        $header = Grid::header([
            'code' => 'product',
        ]);
        $_search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = Product::query();
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->leftJoin('product_category', 'product_category.id', '=', 'product.category_id');
            $model->where('product_category.type', 1);
            $model->where('product.status', 1)
            ->orderBy('product.code', 'asc');

            if ($query['category_id'] > 0) {
                $categoryIds = DB::table('product_category')->treeById($query['category_id'])->pluck('id');
                $model->whereIn('product.category_id', $categoryIds);
            }

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $customer_id = (int)$query['customer_id'];
            $customer = DB::table('customer')->find($customer_id);
            if ($customer['type_id'] > 0) {
                $price = 'product.price'.$customer['type_id'];
                $model->leftJoin(DB::raw('
                (select a.product_id, a.price 
                from customer_price a
                where a.customer_id = '.$customer_id.' 
                group by a.product_id, a.price) as cp'), 'cp.product_id', '=', 'product.id');

                // 获取客户对应的价格
                $header['select'][] = DB::raw("CASE WHEN ISNULL(cp.price, 0) > 0 THEN cp.price WHEN ISNULL(cp.price, 0) = 0 THEN $price ELSE 0 END AS price");
                
                $model->whereRaw('(product.product_type = 1 and cp.product_id is not null or (product.id = 20226 or '.(auth()->user()->role_id == 2 ? 'ISNULL(product.material_type, 0) = 1' : 'ISNULL(product.material_type, 0) > 0').'))');
            }
            $header['select'][] = 'product_category.name as category_name';
            $header['select'][] = 'product.weight';
            
            $model->select($header['select']);

            $q = $query['q'];
            if ($q) {
                $model->whereRaw("(product.code like '%$q%' or product.name like '%$q%' or product.spec like '%$q%' or product.barcode like '%$q%')");
            }

            if ($query['suggest']) {
                $rows = $model->limit(15)->get();
            } else {
                $rows = $model->paginate($query['limit']);
            }
            return Grid::dataFilters($rows, $header);
        }
        return $this->render([
            'search' => $search,
        ]);
    }

    public function category()
    {
        if (Request::method() == 'POST') {
            $model = ProductCategory::orderBy('lft', 'asc');
            $rows = $model->get(['*', 'name as text'])->toArray();
            $rows = array_nest($rows);
            $rows = array_merge($rows);
            return ['data' => $rows];
        }
    }
    
    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'product', 'ids' => $ids]);
        }
    }
}
