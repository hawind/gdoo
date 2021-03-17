<?php namespace Gdoo\Product\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Product\Models\ProductCategory;

use Gdoo\Index\Controllers\DefaultController;

class CategoryController extends DefaultController
{
    public $permission = ['dialog'];

    public function index()
    {
        $header = Grid::header([
            'code' => 'product_category',
            'referer' => 1,
            'search' => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['seq_sn']['hide'] = true;
        $cols['name']['hide'] = true;

        $cols['actions']['options'] = [[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $this->access['edit'],
        ]];
        
        unset($cols['checkbox']);

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = ProductCategory::$tabs;

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy('product_category.lft', 'asc');

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select']);

            $rows = $model->get()->toNested();
            return Grid::dataFilters($rows, $header);
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function create()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'product_category', 'id' => $id]);
        return $this->render([
            'form' => $form,
        ], 'create');
    }

    public function edit()
    {
        return $this->create();
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'product_category', 'ids' => $ids]);
        }
    }

    public function dialog()
    {
        $search = search_form([], [
            ['text','product_category.name','名称'],
            ['text','product_category.id','ID'],
        ]);
        $query = $search['query'];
        $type = Request::get('type', 1);
        if (Request::method() == 'POST') {
            $model = ProductCategory::orderBy('lft', 'asc');
            $rows = $model->where('type', $type)->get()->toNested();
            $data = [];
            foreach ($rows as $row) {
                $row['sid'] = 'r'.$row['id'];
                $data[] = $row;
            }
            $json = ['data' => $data];
            return $json;
        }

        return $this->render(array(
            'search' => $search,
        ));
    }
}
