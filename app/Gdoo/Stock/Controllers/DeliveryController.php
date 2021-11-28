<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Validator;

use App\Support\AES;

use Gdoo\Stock\Models\Delivery;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Model\Models\Bill;
use Gdoo\Model\Models\Step;
use Gdoo\Model\Models\Run;

use Gdoo\Model\Services\ModelService;
use Gdoo\Model\Services\StepService;
use Gdoo\Stock\Services\StockService;

use Gdoo\Index\Controllers\WorkflowController;
use Gdoo\Stock\Services\DeliveryService;

class DeliveryController extends WorkflowController
{
    public $permission = [
        'dialog', 
        'logistics', 
        'getBatchSelect', 
        'getBatchSelectAll', 
        'getBatchSelectZY', 
        'autoSave'
    ];

    public function index()
    {
        $header = Grid::header([
            'code' => 'stock_delivery',
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
        $header['left_buttons'] = [
            ['name' => '批量编辑', 'color' => 'default', 'icon' => 'fa-pencil-square-o', 'action' => 'batchEdit', 'display' => $this->access['batchEdit']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Delivery::$tabs;
        $header['bys'] = Delivery::$bys;

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order']);

            // 过滤库管角色 辅料:30, 成品:31
            if (auth()->user()->role_id == 30) {
                $model->where('stock_delivery.type_id', 2);
            }
            if (auth()->user()->role_id == 31) {
                $model->where('stock_delivery.type_id', '<>', 2);
            }

            // 发货统计
            $model->leftJoin(DB::raw('(select SUM(ISNULL(d.quantity, 0)) total_quantity, d.delivery_id
                    FROM stock_delivery_data as d
                    GROUP BY d.delivery_id
                ) sdd
            '), 'stock_delivery.id', '=', 'sdd.delivery_id');

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
            $model->addSelect(DB::raw('sdd.total_quantity'));
            
            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function detail()
    {
        $header = Grid::header([
            'code' => 'stock_delivery',
            'referer' => 1,
            'template_id' => 71,
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
        $header['tabs'] = Delivery::$tabs2;
        $header['bys'] = Delivery::$bys;

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order']);

            // 过滤库管角色 辅料:30, 成品:31
            if (auth()->user()->role_id == 30) {
                $model->where('stock_delivery.type_id', 2);
            }
            if (auth()->user()->role_id == 31) {
                $model->where('stock_delivery.type_id', '<>', 2);
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
            
            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function autoSave()
    {
        $gets = Request::all();
        $master = $gets['master'];
        $keys = AES::decrypt($master['key'], config('app.key'));
        list($bill_id, $id) = explode('.', $keys);
        $bill = Bill::find($bill_id);

        // 发货日期为空
        if (empty($gets['stock_delivery']['invoice_dt'])) {
            $gets['stock_delivery']['invoice_dt'] = date('Y-m-d');
        }

        $models = ModelService::getModels($bill->model_id);
        if (Request::method() == 'POST') {
            $rows = $gets['stock_delivery_data']['rows'];
            $product_ids = [];
            foreach($rows as $row) {
                $product_ids[$row['product_id']] = $row['product_id'];
            }

            // 获取产品列表
            $vars2 = DB::table('product')->whereIn('id', $product_ids)->get()->keyBy('id');
            $materiels = $products = [];
            foreach($rows as $row) {
                $product = $vars2[$row['product_id']];
                if ($product['material_type'] > 0) {
                    $materiels[] = $row;
                } else {
                    $products[] = $row;
                }
            }

            $print_ids = [];
            if (count($products) > 0) {
                $gets['stock_delivery']['type_id'] = 1;
                $gets['stock_delivery_data']['rows'] = $products;
                $id = Form::store($bill, $models, $gets, 0);
                $print_ids[] = $id;
            }
            
            if (count($materiels) > 0) {
                $gets['stock_delivery']['type_id'] = 2;
                $gets['stock_delivery_data']['rows'] = $materiels;
                $id = Form::store($bill, $models, $gets, 0);
                $print_ids[] = $id;
            }

            foreach($print_ids as $print_id) {
                DB::table('stock_delivery')->where('id', $print_id)->update(['print_master_id' => $print_ids[0]]);
            }
            
            // 自动保存发货单返回数据
            $url = url($master['uri'].'/show', ['id' => $id, 'client' => $master['client']]);
            return $this->json($bill['name'].'保存成功', $url);
        }
        return $this->create('audit');
    }

    public function create($action = 'edit')
    {
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'stock_delivery';
        $header['id'] = $id;

        // 客户权限
        $header['region'] = ['field' => 'customer_id'];
        $header['authorise'] = ['action' => 'index', 'field' => 'created_id'];

        $header['select'] = '
            product_id_product.weight,
            product_id_product.weight * stock_delivery_data.quantity as total_weight
        ';
        
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

    // 批量编辑
    public function batchEdit()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            $ids = explode(',', $gets['ids']);
            DB::table('stock_delivery')->whereIn('id', $ids)->update([
                $gets['field'] => $gets['search_0'],
            ]);
            return $this->json('修改完成。', true);
        }
        $header = Grid::batchEdit([
            'code' => 'stock_delivery',
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

        // 打印插件
        if ($print_type == 'stiReport') {
            $data = DeliveryService::getPrintData($id);
            $print_data = [
                'master' => [$data['master']],
                'stock_delivery_data' => $data['rows'],
            ];
            return $this->display([
                'template' => $template,
                'print_data' => $print_data,
            ]);
        } else {
            $print_tpl = view()->exists(Request::controller().'.print.'.$template_id);
            if ($print_tpl) {
                $data = DeliveryService::getPrintData($id);
                $data['template'] = $template;
                $tpl = $this->display($data, 'print/'.$template_id);
            } else {
                $tpl = $this->create('print');
            }
            return $print_type == 'pdf' ? print_prince($tpl) : $tpl;
        }
    }

    // 物流信息
    public function logistics()
    {
        if (Request::method() == 'POST') {
            $gets = Request::get('stock_delivery');
            $id = $gets['id'];
            $gets['freight_created_dt'] = date('Y-m-d H:i:s');
            $gets['freight_created_by'] = auth()->user()->name;
            DB::table('stock_delivery')->where('id', $id)->update($gets);
            return $this->json('物流信息提交成功。', true);
        }
        $file = base_path().'/app/Gdoo/'.ucfirst(Request::module()).'/views/'.Request::controller().'/'.Request::action().'.xml';
        $id = Request::get('id');
        $row = Delivery::find($id);
        $freight_quantity = floatval($row['freight_quantity']);

        if ($freight_quantity == 0) {
            $count = DB::table('stock_delivery_data')
            ->where('delivery_id', $id)->selectRaw('sum(total_weight) as weight, sum(quantity) as quantity')->first();
            $weight = intval($count['weight'] / 100);
            $weight = number_format($weight / 10, 1);
            $quantity = $count['quantity'];
            $row['freight_quantity'] = $quantity;
            $row['freight_weight'] = $weight;
        }
        $form = Form::make2(['table' => 'stock_delivery', 'file' => $file, 'row' => $row]);
        return $form;
    }

    // 获取库存
    public function getBatchSelect()
    {
        $search = search_form(['advanced' => ''], [
            ['form_type' => 'text', 'name' => '产品名称', 'field' => 'name'],
            ['form_type' => 'text', 'name' => '产品编码', 'field' => 'code']
        ], 'model');
        $query = $search['query'];
        if (Request::method() == 'POST') {
            $rows = StockService::getBatchSelect($query['warehouse_id'], $query['product_id'], $query['value'], $query['customer_id']);
            return ['data' => $rows];
        }
        return $this->render([
            'search' => $search,
        ]);
    }

    // 获取库存(直营)
    public function getBatchSelectZY()
    {
        $search = search_form(['advanced' => ''], [
            ['form_type' => 'text', 'name' => '产品名称', 'field' => 'name'],
            ['form_type' => 'text', 'name' => '产品编码', 'field' => 'code']
        ], 'model');
        $query = $search['query'];
        if (Request::method() == 'POST') {
            $rows = StockService::getBatchSelectZY($query['warehouse_id'], $query['product_id'], $query['value']);
            return ['data' => $rows];
        }
        return $this->render([
            'search' => $search,
        ], 'getBatchSelect');
    }

    // 获取库存(全部)
    public function getBatchSelectAll()
    {
        $search = search_form(['advanced' => ''], [
            ['form_type' => 'text', 'name' => '产品名称', 'field' => 'name'],
            ['form_type' => 'text', 'name' => '产品编码', 'field' => 'code']
        ], 'model');
        $query = $search['query'];
        if (Request::method() == 'POST') {
            $rows = StockService::getBatchSelectAll($query['warehouse_id'], $query['product_id'], $query['value'], 0);
            return ['data' => $rows];
        }
        return $this->render([
            'search' => $search,
        ], 'getBatchSelect');
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'stock_delivery', 'ids' => $ids]);
        }
    }
}
