<?php namespace Gdoo\Customer\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Customer\Models\CustomerClass;

use Gdoo\Index\Controllers\DefaultController;

class CustomerClassController extends DefaultController
{
    public $permission = ['dialog'];
    
    public function indexAction()
    {
        $display = $this->access;

        $header = Grid::header([
            'code' => 'customer_class',
            'referer' => 1,
            'search' => [],
        ]);

        $cols = $header['cols'];
        $cols['actions']['options'] = [[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $display['edit'],
        ]];
        unset($cols['checkbox']);

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy('customer_class.code', 'asc');
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select'])
            ->addSelect(DB::raw('parent_id'));

            $items = $model->get()->toNested('name');
            $items = Grid::dataFilters($items, $header, function($item) {
                return $item;
            });
            return $this->json($items, true);
        }

        $header['buttons'] = [
            ['name' => '删除','icon' => 'fa-remove','action' => 'delete','display' => $display['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];
        $header['cols'] = $cols;
        $header['tabs'] = CustomerClass::$tabs;
        $header['bys'] = CustomerClass::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'customer_class', 'id' => $id]);
        return $this->render([
            'form' => $form,
        ], 'create');
    }

    public function editAction()
    {
        return $this->createAction();
    }

    public function dialogAction()
    {
        $search = search_form([], [
            ['text','customer_class.name','名称'],
            ['text','customer_class.code','编码'],
            ['text','customer_class.id','ID'],
        ]);

        if (Request::method() == 'POST') {
            $model = CustomerClass::orderBy('code', 'asc');
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            // $model->where('end', 1);
            $rows = $model->get();
            return response()->json(['data' => $rows]);
        }
        return $this->render([
            'search' => $search
        ]);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            $id = array_filter((array)$id);
            if (empty($id)) {
                return $this->json('最少选择一行记录。');
            }
            $has = CustomerClass::whereIn('parent_id', $id)->count();
            if ($has) {
                return $this->json('存在子节点不允许删除。');
            }
            CustomerClass::whereIn('id', $id)->delete();
            return $this->json('删除成功。', true);
        }
    }
}
