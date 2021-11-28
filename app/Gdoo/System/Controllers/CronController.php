<?php namespace Gdoo\System\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\System\Models\Cron;

class CronController extends DefaultController
{
    public $permission = [];

    /**
     * 部件设置
     */
    public function index()
    {
        $header = [
            'master_name' => '定时任务',
            'simple_search_form' => 1,
            'table' => 'cron',
            'master_table' => 'cron',
        ];

        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '名称', 'field' => 'cron.name', 'value' => '', 'options' => []],
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
            'command' => [
                'field' => 'command',
                'headerName' => '命令',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 200,
            ],
            'expression' => [
                'field' => 'expression',
                'headerName' => '表达式',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 200,
            ],
            'type' => [
                'field' => 'type',
                'headerName' => '类型',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 100,
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
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['search_form'] = $search;
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = Cron::setBy($header);
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $model->selectRaw('*, id as master_id');
            $rows = $model->paginate($query['limit'])->appends($query);

            $rows->transform(function($row) {
                $row['type'] = $row['type'] == 'system' ? '系统' : '用户';
                $row['status'] = $row['status'] == 1 ? '启用' : '禁用';
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
                'expression' => 'required',
                'command' => 'required',
            ];
            $v = Validator::make($gets, $rules);
            if ($v->fails()) {
                return $this->json($v->errors()->first());
            }

            $model = Cron::findOrNew($gets['id']);
            $model->fill($gets);
            $model->save();
            return $this->json('恭喜你，操作成功。', true);
        }
        $row = Cron::where('id', $id)->first();
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
            Cron::whereIn('id', $id)->delete();
            return $this->json('恭喜你，操作成功。', true);
        }
    }
}
