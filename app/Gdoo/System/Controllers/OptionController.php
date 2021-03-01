<?php namespace Gdoo\System\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;

use Gdoo\System\Models\Option;

use Gdoo\Index\Controllers\DefaultController;

class OptionController extends DefaultController
{
    public $permission = ['category'];
    
    public function indexAction()
    {
        $header = [
            'master_name' => '枚举',
            'simple_search_form' => 1,
            'table' => 'option',
            'master_table' => 'option',
            'create_btn' => 1,
        ];

        $search = search_form([
            'advanced' => '',
            'parent_id' => 0,
        ], [
            ['form_type' => 'text', 'name' => '名称', 'field' => 'option.name', 'value' => '', 'options' => []],
        ], 'model');

        $header['cols'] = [
            'checkbox' => [
                'width' => 40,
                'suppressSizeToFit' => true,
                'cellClass' => 'text-center',
                'suppressMenu' => true,
                'sortable' => false,
                'editable' => false,
                'resizable' => false,
                'filter' => false,
                'checkboxSelection' => true,
                'headerCheckboxSelection' => true,
            ],
            'sequence_sn' => [
                'width' => 60,
                'headerName' => '序号',
                'suppressSizeToFit' => true,
                'cellClass' => 'text-center',
                'suppressMenu' => true,
                'sortable' => false,
                'resizable' => false,
                'editable' => false,
                'type' => 'sn',
                'filter' => false,
            ],
            'name' => [
                'field' => 'name',
                'headerName' => '名称',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-',
                'form_type' => 'text',
                'width' => 0,
            ],
            'value' => [
                'field' => 'value',
                'headerName' => '值',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 120,
            ],
            'status_name' => [
                'field' => 'status_name',
                'headerName' => '状态',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 120,
            ],
            'sort' => [
                'field' => 'sort',
                'headerName' => '排序',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 120,
            ],
            'id' => [
                'field' => 'id',
                'headerName' => 'ID',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 40,
            ],
            'actions' => [
                'headerName' => '',
                'cellRenderer' => 'actionCellRenderer',
                'options' => [[
                    'name' => '编辑',
                    'action' => 'edit',
                    'display' => $this->access['edit'],
                ]],
                'width' => 80,
                'cellClass' => 'text-center',
                'suppressSizeToFit' => true,
                'suppressMenu' => true,
                'sortable' => false,
                'editable' => false,
                'resizable' => false,
                'filter' => false,
            ],
        ];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->where('option.parent_id', $query['parent_id']);

            $model->selectRaw('*, id as master_id');
            $rows = $model->paginate($query['limit'])->appends($query);
            $rows->transform(function($row) {
                if ($row['status'] == 1) {
                    $row['status_name'] = '启用';
                } else {
                    $row['status_name'] = '禁用';
                }
                return $row;
            });
            return $rows->toJson();
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['search_form'] = $search;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function editAction()
    {
        return $this->createAction();
    }

    public function createAction()
    {
        $id = (int)Request::get('id');
        $row = DB::table('option')->where('id', $id)->first();
        $parent_id = (int)Request::get('parent_id');
        $parents = Option::where('parent_id', 0)->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        return $this->render([
            'row' => $row,
            'parents' => $parents,
            'parent_id' => $parent_id,
        ], 'edit');
    }

    public function storeAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $rules = [
                'name' => 'required',
                'value' => 'required',
            ];
            $v = Validator::make($gets, $rules);
            if ($v->fails()) {
                return $this->back()->withErrors($v)->withInput();
            }

            if ($gets['id']) {
                DB::table('option')->where('id', $gets['id'])->update($gets);
            } else {
                DB::table('option')->insert($gets);
            }
            return $this->json('恭喜你，操作成功。', true);
        }
    }

    public function categoryAction()
    {
        if (Request::method() == 'POST') {
            $rows = Option::where('parent_id', 0)->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
            $rows->prepend(['name' => '全部类别', 'id' => 0, 'parent_id' => 0]);
            return response()->json(['data' => $rows]);
        }
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');

            $count = DB::table('option')->whereIn('parent_id', $id)->count();
            if ($count > 0) {
                return $this->error('存在子选项无法删除。');
            }

            DB::table('option')->whereIn('id', $id)->delete();
            return $this->back('删除成功。');
        }
    }
}
