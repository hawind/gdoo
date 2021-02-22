<?php namespace Gdoo\Workflow\Controllers;

use Request;
use DB;
use Validator;

use Gdoo\Workflow\Models\BillCategory;
use Gdoo\Model\Grid;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Model\Models\Bill;

class CategoryController extends DefaultController
{
    // 流程类别
    public function indexAction()
    {
        $header = [
            'master_name' => '流程类别',
            'simple_search_form' => 1,
            'table' => 'model_bill_category',
            'master_table' => 'model_bill_category',
            'create_btn' => 1,
        ];

        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '名称', 'field' => 'model_bill_category.name', 'value' => '', 'options' => []],
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
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 100,
            ],
            'remark' => [
                'field' => 'remark',
                'headerName' => '备注',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 0,
            ],
            'sort' => [
                'field' => 'sort',
                'headerName' => '排序',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 60,
            ],
            'updated_dt' => [
                'field' => 'updated_dt',
                'headerName' => '操作时间',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 60,
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

            $model = BillCategory::orderBy('sort', 'desc')
            ->selectRaw("*, id as master_id")
            ->setBy($header);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $rows = $model->paginate($query['limit'])->appends($query);

            $rows->transform(function($row) {
                $row['updated_dt'] = format_datetime($row['updated_at']);
                return $row;
            });
            return $rows;
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['search_form'] = $search;
        $header['js'] = Grid::js($header);

        // 配置权限
        return $this->display([
            'header' => $header,
        ]);
    }

    public function createAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $rules = [
                'name' => 'required',
            ];
            $v = Validator::make($gets, $rules);

            if ($v->fails()) {
                return $this->json($v->errors()->first());
            }
            $category = BillCategory::findOrNew($gets['id']);
            $category->fill($gets);
            $category->save();
            return $this->json('恭喜你，操作成功。', true);
        }

        $category_id = Request::get('id');
        $category = BillCategory::find($category_id);
        return $this->render([
            'category' => $category,
        ], 'create');
    }

    public function editAction()
    {
        return $this->createAction();
    }

    public function storeAction()
    {
        return $this->editAction();
    }

    // 删除流程类别
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            if (empty($id)) {
                return $this->json('最少选择一行记录。');
            }

            $count = Bill::whereIn('category_id', $id)->count();
            if ($count > 0) {
                return $this->json('此类别存在工作数据，无法删除。');
            }

            BillCategory::whereIn('id', $id)->delete();
            return $this->json('恭喜你，操作成功。', true);
        }
    }
}
