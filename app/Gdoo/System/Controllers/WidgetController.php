<?php namespace Gdoo\System\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Model\Services\ModuleService;
use Gdoo\System\Models\Widget;

class WidgetController extends DefaultController
{
    public $permission = ['refresh'];

    /**
     * 部件设置
     */
    public function index()
    {
        $header = [
            'master_name' => '部件',
            'simple_search_form' => 1,
            'table' => 'widget',
            'master_table' => 'widget',
            'create_btn' => 0,
        ];

        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '名称', 'field' => 'widget.name', 'value' => '', 'options' => []],
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
            'seq_sn' => [
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
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 100,
            ],
            'code' => [
                'field' => 'code',
                'headerName' => '编码',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 100,
            ],
            'url' => [
                'field' => 'url',
                'headerName' => 'URL',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 120,
            ],
            'more_url' => [
                'field' => 'more_url',
                'headerName' => 'MoreURL',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 120,
            ],
            'receive_name' => [
                'field' => 'receive_name',
                'headerName' => '授权范围',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'width' => 0,
            ],
            'receive_name' => [
                'field' => 'receive_name',
                'headerName' => '授权范围',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'width' => 0,
            ],
            'grid' => [
                'field' => 'grid',
                'headerName' => '位置',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
            ],
            'type' => [
                'field' => 'type',
                'headerName' => '类型',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'width' => 60,
            ],
            'default' => [
                'field' => 'default',
                'headerName' => '全局',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'width' => 60,
            ],
            'status' => [
                'field' => 'status',
                'headerName' => '状态',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 60,
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

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => 0],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '更新', 'color' => 'default', 'icon' => 'fa-refresh', 'action' => 'refresh', 'display' => 1],
        ];

        $header['search_form'] = $search;
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table('widget')->setBy($header);
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $model->orderBy('sort', 'asc');
            $model->selectRaw('*, id as master_id');
            $rows = $model->paginate($query['limit'])->appends($query);

            $rows->transform(function($row) {
                $row['type'] = $row['type'] == 1 ? '部件' : '信息';
                $row['default'] = $row['default'] == 1 ? '是' : '否';
                $row['status'] = $row['status'] == 1 ? '启用' : '禁用';
                $row['grid'] = $row['grid'] == 8 ? '左' : '右';
                $row['updated_dt'] = format_datetime($row['updated_at']);
                return $row;
            });
            $ret = $rows->toArray();
            $ret['header'] = Grid::getColumns($header);
            return $ret;
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function create()
    {
        $id = (int)Request::get('id');

        if (Request::method() == 'POST') {
            $gets = Request::all();

            $rules = [
                'name' => 'required',
                'url' => 'required',
            ];
            $v = Validator::make($gets, $rules);
            if ($v->fails()) {
                return $this->json($v->errors()->first());
            }

            $model = Widget::findOrNew($gets['id']);
            $model->fill($gets);
            $model->save();
            return $this->json('恭喜你，操作成功。', true);
        }
        $row = DB::table('widget')->where('id', $id)->first();
        return $this->render([
            'row' => $row
        ], 'create');
    }

    public function edit()
    {
        return $this->create();
    }

    public function store()
    {
        return $this->edit();
    }

    public function delete()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            Widget::whereIn('id', $id)->delete();
            return $this->json('恭喜你，操作成功。', true);
        }
    }

    /**
     * 更新部件列表
     */
    public function refresh()
    {
        if (Request::method() == 'POST') {
            $widgets = ModuleService::widgets();
            foreach($widgets as $code => $widget) {
                $model = Widget::firstOrNew(['code' => $code]);
                $widget['params'] = json_encode($widget['params'], JSON_UNESCAPED_UNICODE);
                $model->fill($widget);
                $model->save();
            }
            return $this->json('部件刷新完成。', true);
        }
    }

}
