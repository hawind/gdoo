<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\Stock\Models\Direct;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\WorkflowController;

class DirectController extends WorkflowController
{
    public $permission = ['dialog', 'importExcel'];

    public function index()
    {
        $header = Grid::header([
            'code' => 'stock_direct',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action' => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Direct::$tabs;
        $header['bys'] = Direct::$bys;

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
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'stock_direct';
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
        $id = Request::get('id'); 
        $template_id = Request::get('template_id');

        $this->layout = 'layouts.print_html';

        $master = DB::table('stock_direct as sd')
        ->leftJoin('customer as c', 'c.id', '=', 'sd.customer_id')
        ->leftJoin('customer_tax as ct', 'ct.id', '=', 'sd.tax_id')
        ->leftJoin('sale_type as st', 'st.id', '=', 'sd.type_id')
        ->selectRaw('sd.*, ct.name as tax_name, c.name as customer_name, st.name as type_name')
        ->where('sd.id', $id)
        ->first();

        $model = DB::table('stock_direct_data as sdd')
        ->leftJoin('stock_direct as sd', 'sd.id', '=', 'sdd.direct_id')
        ->leftJoin('product as p', 'p.id', '=', 'sdd.product_id')
        ->leftJoin('product_unit as pu', 'pu.id', '=', 'p.unit_id')
        ->leftJoin('customer_order_type as cot', 'cot.id', '=', 'sdd.type_id')
        ->leftJoin('warehouse as w', 'w.id', '=', 'sdd.warehouse_id');

        $model->where('sdd.direct_id', $id);

        $model->whereRaw("p.code <> '99001'");
        
        $rows = $model->selectRaw("
            sdd.*,
            p.name as product_name,
            p.spec as product_spec,
            cot.name as type_name,
            pu.name as product_unit,
            p.material_type,
            p.product_type,
            batch_sn,
            w.name as warehouse_name
        ")
        ->orderBy('p.code', 'asc')
        ->get();

        $money = DB::table('stock_direct_data as sdd')
        ->leftJoin('product as p', 'p.id', '=', 'sdd.product_id')
        ->where('sdd.direct_id', $id)
        ->whereRaw("p.code = '99001'")
        ->sum("money");

        $form = [
            'template' => DB::table('model_template')->where('id', $template_id)->first()
        ];

        return $this->display([
            'master' => $master,
            'money' => $money,
            'rows' => $rows,
            'form' => $form,
        ], 'print/'.$template_id);
    }

    public function importExcel()
    {
        if (Request::method() == 'POST') {
            $customer_id = Request::get('customer_id');
            $file = Request::file('file');
            if ($file->isValid()) {
                $types = DB::table('customer_order_type')->get()->keyBy('name');
                $customer = DB::table('customer')->where('id', $customer_id)->first();
                $products = DB::table('customer_price')
                ->leftJoin('product', 'product.id', '=', 'customer_price.product_id')
                ->leftJoin('product_unit', 'product_unit.id', '=', 'product.unit_id')
                ->where('customer_id', $customer_id)
                ->selectRaw('
                    product.*,
                    customer_price.price,
                    product_unit.name as unit_name,
                    product.price'.$customer['type_id'].' as product_price
                ')
                ->get()->keyBy('code');

                /*
                [0] => 类型
                [1] => 产品编码
                [2] => 数量
                [3] => 单价
                [4] => 备注
                */
                $rows = readExcel($file->getPathName(), $file->getClientOriginalExtension());
                $items = [];
                foreach($rows as $i => $row) {
                    if ($i > 1) {
                        $type = $types[$row[0]];
                        if (empty($type)) {
                            return $this->json('产品编码'.$row[1].':类型('.$row[0].')不存在。');
                        }
                        $product = $products[$row[1]];
                        if (empty($product)) {
                            return $this->json('产品编码'.$row[1].'在客户销售价格中不存在。');
                        }

                        if (floatval($row[3]) <> 0) {
                            $price = $row[3];
                        } else {
                            $price = floatval($product['price']) == 0 ? $product['product_price'] : $product['price'];
                        }

                        $quantity = $row[2];
                        $item = [
                            'type_id' => $type['id'],
                            'type_id_name' => $type['name'],
                            'product_id' => $product['id'],
                            'product_code' => $product['code'],
                            'product_name' => $product['name'],
                            'product_spec' => $product['spec'],
                            'product_barcode' => $product['barcode'],
                            'product_unit' => $product['unit_name'],
                            'price' => $price,
                            'quantity' => $quantity,
                            'money' => $price * $quantity,
                            'weight' => $product['weight'],
                            'total_weight' => $product['weight'] * $quantity,
                            'remark' => $row[3],
                        ];
                        if ($type['id'] == 2) {
                            $item['other_money'] = $item['money'];
                        }
                        $items[] = $item;
                    }
                }
                return $this->json($items, true);
            }
        }
        return view('importExcel');
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'stock_direct', 'ids' => $ids]);
        }
    }
}
