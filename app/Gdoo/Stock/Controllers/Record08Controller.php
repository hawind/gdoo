<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\Stock\Models\Record08;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\AuditController;

class Record08Controller extends AuditController
{
    public $permission = ['dialog', 'importExcel'];

    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'stock_record08',
            'referer' => 1,
            'search'  => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action'  => 'show',
            'display' => $this->access['show'],
        ]];

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => 0],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Record08::$tabs;
        $header['bys'] = Record08::$bys;

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

    public function createAction($action = 'edit')
    {
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'stock_record08';
        $header['id'] = $id;
        
        $form = Form::make($header);
        $tpl = $action == 'print' ? 'print' : 'create';
        return $this->display([
            'form' => $form,
        ], $tpl);
    }

    public function editAction()
    {
        return $this->createAction();
    }

    public function showAction()
    {
        return $this->createAction('show');
    }

    public function printAction()
    {
        $this->layout = 'layouts.print2';
        print_prince($this->createAction('print'));
    }

    public function importExcelAction()
    {
        if (Request::method() == 'POST') {
            $file = Request::file('file');

            if ($file->isValid()) {
                $products = DB::table('product')
                ->leftJoin('product_unit', 'product_unit.id', '=', 'product.unit_id')
                ->selectRaw('
                    product.*,
                    product_unit.name as unit_name
                ')
                ->get()
                ->keyBy('code');
                /*
                [0] => 存货编码
                [1] => 存货名称
                [2] => 规格型号
                [3] => 批次
                [4] => 数量
                */
                $rows = readExcel($file->getPathName(), $file->getClientOriginalExtension());
                $items = [];
                foreach($rows as $i => $row) {
                    if ($i > 1) {
                        if ($row[0]) {
                            $product = $products[$row[0]];
                            if (empty($product)) {
                                return $this->json('产品编码'.$product[0].':产品('.$product[1].')不存在。');
                            }
                            $batch_sn = $row[3];
                            $quantity = $row[4];
                            $item = [
                                'product_id' => $product['id'],
                                'product_code' => $product['code'],
                                'product_name' => $product['name'],
                                'product_spec' => $product['spec'],
                                'product_unit' => $product['unit_name'],
                                'quantity' => $quantity,
                                'batch_sn' => $batch_sn,
                            ];
                            $items[] = $item;
                        }
                    }
                }
                return $this->json($items, true);
            }
        }
        return view('importExcel');
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'stock_record08', 'ids' => $ids]);
        }
    }
}
