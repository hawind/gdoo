<?php namespace Gdoo\Model\Controllers;

use DB;
use Auth;
use Request;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\Model\Models\Model;
use Gdoo\Model\Models\Field;
use Gdoo\Model\Models\Step;
use Gdoo\Model\Models\Run;
use Gdoo\Model\Models\RunLog;
use Gdoo\Model\Models\RunStep;
use Gdoo\Model\Models\Template;

use Gdoo\Index\Controllers\DefaultController;

class ModelController extends DefaultController
{
    public function indexAction()
    {
        $header = [
            'master_name' => '模型',
            'simple_search_form' => 1,
            'table' => 'model',
            'master_table' => 'model',
            'create_btn' => 1,
        ];

        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '名称', 'field' => 'model.name', 'value' => '', 'options' => []],
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
            'text' => [
                'field' => 'text',
                'headerName' => '名称',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 100,
            ],
            'user' => [
                'field' => 'table',
                'headerName' => '表名',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 0,
            ],
            'relation' => [
                'field' => 'relation',
                'headerName' => '关联外键',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
            ],
            'updated_dt' => [
                'field' => 'updated_dt',
                'headerName' => '操作时间',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
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
                    'name' => '字段',
                    'action' => 'field',
                    'display' => 1,
                ],[
                    'name' => '编辑',
                    'action' => 'edit',
                    'display' => $this->access['edit'],
                ]],
                'width' => 100,
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
            $model = Model::setBy($header);
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $model->orderBy('lft', 'asc');
            $model->selectRaw('*, id as master_id');
            $rows = $model->paginate($query['limit'])->appends($query);

            $rows->transform(function($row) {
                $row['updated_dt'] = format_datetime($row['updated_at']);
                return $row;
            });

            $items = $rows->items();
            array_nest($items);
            $rows->items($items);
           
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
                'table' => 'required|unique:model,table,'.$gets['id'],
            ];
            $v = Validator::make($gets, $rules);

            if ($v->fails()) {
                return $this->json($v->errors()->first());
            }

            $model = Model::findOrNew($gets['id']);
            $model->fill($gets);
            $model->save();
            $model->treeRebuild();
            return $this->json('恭喜你，操作成功。', true);
        }

        $model_id = Request::get('id');
        $model = Model::find($model_id);
        $models = Model::where('parent_id', 0)->get();
        
        return $this->render([
            'model' => $model,
            'models' => $models,
            'model_id' => $model_id,
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

    public function deleteAction()
    {
        $ids = (array)Request::get('id');
        if (count($ids) > 0) {
            Model::whereIn('id', $ids)->delete();
            Field::whereIn('model_id', $ids)->delete();
            return $this->json('恭喜你，操作成功。', true);
        }
    }
}
