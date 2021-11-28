<?php namespace Gdoo\Product\Controllers;

use DB;
use Input;
use Request;
use Validator;

use Gdoo\User\Models\User;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Product\Models\ProductMaterial;

use Gdoo\Index\Controllers\DefaultController;

class MaterialController extends DefaultController
{
    public $permission = ['dialog', 'list', 'getMaterials'];

    public function index()
    {
        $header = Grid::header([
            'code' => 'product_material',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $this->access['edit'],
        ]];

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['right_buttons'] = [
            ['name' => '导入', 'icon' => 'fa-mail-reply', 'color' => 'default', 'action' => 'import', 'display' => $this->access['import']],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = ProductMaterial::$tabs;
        $header['bys'] = ProductMaterial::$bys;

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order']);
            $model->where('product_id_product.id', '>', 0);
            $model->where('product_id_product.status', 1);

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
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'product_material', 'id' => $id, 'action' => $action]);

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
        return $this->create('edit');
    }

    public function list()
    {
        $gets = Request::all();
        if ($gets['product_id']) {
            $header = Grid::header([
                'code' => 'product_material',
            ]);
            $model = ProductMaterial::where('product_id', $gets['product_id']);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }

            $model->leftJoin('product_unit as material_unit', 'material_unit.id', '=', 'material_id_product.unit_id');

            $model->where('product_id_product.status', 1);
            $model->orderBy('product_material.id', 'asc');

            $header['select'][] = 'material_id_product.code as material_code';
            $header['select'][] = 'material_id_product.spec as material_spec';
            $header['select'][] = 'material_id_product.barcode as material_barcode';
            $header['select'][] = 'material_unit.name as material_unit';

            $rows = $model->get($header['select']);
            $rows = Grid::dataFilters($rows, $header, function($row) {
                $row['material_name'] = $row['material_id_name'];
                $row['warehouse_name'] = $row['warehouse_id_name'];
                return $row;
            });
        } else {
            $rows = [];
        }
        return $rows;
    }

    public function getMaterials()
    {
        $gets = Request::all();
        if ($gets['product_id']) {
            $rows = ProductMaterial::leftJoin('product', 'product.id', '=', 'product_material.material_id')
            ->leftJoin('product_unit', 'product_unit.id', '=', 'product.unit_id')
            ->leftJoin('warehouse', 'warehouse.id', '=', 'product.warehouse_id')
            ->where('product_material.product_id', $gets['product_id'])
            ->selectRaw('
                product_material.quantity,
                product_material.quantity base_quantity,
                product_material.loss_rate,
                product.warehouse_id,
                warehouse.name warehouse_id_name,
                product_material.material_id product_id,
                product.spec product_code,
                product.name product_name,
                product.spec product_spec,
                product.barcode product_barcode,
                product.purchase_price price,
                product_unit.name product_unit
            ')
            ->get();

            return $this->json($rows, true);
        }
    }

    public function import()
    {
        if (Request::method() == 'POST') {
            return Form::import(['table' => 'product_material', 'keys' => ['product_id', 'material_id']]);
        }
        $tips = '注意：表格里必须包含[存货编码,物料编码]列。';
        return $this->render(['tips' => $tips], 'layouts.import');
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'product_material', 'ids' => $ids]);
        }
    }
}
