<?php namespace Gdoo\System\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\Index\Controllers\DefaultController;

class LogController extends DefaultController
{
    public $permission = [];

    /**
     * 系统日志
     */
    public function indexAction()
    {
        $header = [
            'master_name' => '系统日志',
            'simple_search_form' => 1,
            'table' => 'system_log',
            'master_table' => 'system_log',
            'create_btn' => 1,
        ];

        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '名称', 'field' => 'mail.name', 'value' => '', 'options' => []],
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
            'type' => [
                'field' => 'type',
                'headerName' => '类型',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 100,
            ],
            'name' => [
                'field' => 'name',
                'headerName' => '名称',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 120,
            ],
            'user' => [
                'field' => 'remark',
                'headerName' => '备注',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 0,
            ],
            'created_dt' => [
                'field' => 'created_dt',
                'headerName' => '操作时间',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 100,
            ],
            'ip' => [
                'field' => 'ip',
                'headerName' => 'IP',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 100,
            ],
            'browser' => [
                'field' => 'browser',
                'headerName' => '浏览器',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
            ],
            'device' => [
                'field' => 'device',
                'headerName' => '设备ID',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
            ],
            /*
            'error_count' => [
                'field' => 'error_count',
                'headerName' => '错误次数',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
            ],
            */
            'level' => [
                'field' => 'level',
                'headerName' => '等级',
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
        ];

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['search_form'] = $search;
        $header['js'] = Grid::js($header);

        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $model->orderBy('id', 'desc');
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
            $ret = $rows->toArray();
            $ret['header'] = Grid::getColumns($header);
            return $ret;
        }

        return $this->display([
            'header' => $header,
        ]);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            DB::table('system_log')->whereIn('id', $id)->delete();
            return $this->json('操作完成。', true);
        }
    }
}
