<?php namespace Gdoo\System\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;

use Gdoo\Index\Controllers\DefaultController;

class SmsLogController extends DefaultController
{
    public $permission = [];
    
    /**
     * 短信记录
     */
    public function indexAction()
    {
        $header = [
            'master_name' => '短信记录',
            'simple_search_form' => 1,
            'table' => 'sms_log',
            'master_table' => 'sms_log',
            'create_btn' => 1,
        ];

        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '名称', 'field' => 'sms.name', 'value' => '', 'options' => []],
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
                'headerName' => '服务商',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
            ],
            'content' => [
                'field' => 'content',
                'headerName' => '内容',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 120,
            ],
            'data' => [
                'field' => 'data',
                'headerName' => '数据',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 0,
            ],
            'phone' => [
                'field' => 'phone',
                'headerName' => '手机号',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
            ],
            'status_name' => [
                'field' => 'status_name',
                'headerName' => '状态',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
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
                    $row['status_name'] = '成功';
                } else {
                    $row['status_name'] = '失败';
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

        // 配置权限
        return $this->display([
            'header' => $header,
        ]);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            DB::table('sms_log')->whereIn('id', $id)->delete();
            return $this->json('删除成功。', true);
        }
    }
}
