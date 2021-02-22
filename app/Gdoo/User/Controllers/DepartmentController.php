<?php namespace Gdoo\User\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\User\Models\Department;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\DefaultController;

class DepartmentController extends DefaultController
{
    public $permission = ['dialog'];
    
    public function indexAction()
    {
        $display = $this->access;

        $header = Grid::header([
            'code' => 'department',
            'referer' => 1,
            'search'  => ['by' => '', 'tab' => 'department'],
        ]);

        $cols = $header['cols'];
        $cols = Grid::addColumns($cols, 'code', [[
            'headerName' => '用户数',
            'field' => 'user_count',
            'footerRenderer' => 'sum',
            'width' => 60,
            'cellStyle' => ['text-align' => 'center'],
        ]]);
        
        $cols['actions']['options'] = [[
            'name' => '编辑',
            'action' => 'edit',
            'display' => $display['edit'],
        ]];
        unset($cols['checkbox']);
        unset($cols['name']);

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy('department.lft', 'asc');
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select'])
            ->addSelect(DB::raw('parent_id,(select count(id) from [user] where department_id = department.id) as user_count'));

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
        $header['tabs'] = User::$tabs;
        $header['bys'] = Department::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction()
    {
        $id = (int)Request::get('id');
        $form = Form::make(['code' => 'department', 'id' => $id]);
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
            ['text','department.name','名称'],
            ['text','department.id','ID'],
        ]);

        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = Department::orderBy('code', 'asc');
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            if (isset($query['parent_id'])) {
                $model->where('parent_id', $query['parent_id']);
            }
            
            $rows = $model->get()->toNested('name');
            $data = [];
            foreach ($rows as $row) {
                $row['sid'] = 'd'.$row['id'];
                $data[] = $row;
            }
            $data[] = [
                'id' => 0,
                'sid' => 'all',
                'tree_path' => ['全体人员'],
                'name' => '全体人员',
                'text' => '全体人员',
            ];
            return response()->json(['data' => $data]);
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

            $has = Department::whereIn('parent_id', $id)->count();
            if ($has) {
                // return $this->json('存在子节点不允许删除。');
            }

            // 删除部门
            Department::whereIn('id', $id)->delete();
            
            // 重构树形结构
            Department::treeRebuild();

            return $this->json('删除成功。', true);
        }
    }
}
