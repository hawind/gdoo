<?php namespace Gdoo\System\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Grid;

use Gdoo\Index\Services\NotificationService;

use Gdoo\Index\Controllers\DefaultController;

class SmsController extends DefaultController
{
    public $permission = ['test'];
    
    /**
     * 短信设置
     */
    public function indexAction()
    {
        $header = [
            'master_name' => '短信',
            'simple_search_form' => 1,
            'table' => 'sms',
            'master_table' => 'sms',
            'create_btn' => 0,
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
                'cellClass' => 'text-',
                'form_type' => 'text',
                'width' => 120,
            ],
            'apikey' => [
                'field' => 'apikey',
                'headerName' => 'apikey',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 0,
            ],
            'driver' => [
                'field' => 'driver',
                'headerName' => '服务商',
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

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => 0],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '测试短信', 'color' => 'default', 'icon' => 'fa-comment-o', 'action' => 'test', 'display' => 1],
        ];

        $header['search_form'] = $search;
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
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

    /**
     * 短信保存
     */
    public function storeAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $rules = [
                'name' => 'required',
                'apikey' => 'required',
            ];
            $v = Validator::make($gets, $rules);
            if ($v->fails()) {
                return $this->json(join('<br>', $v->errors()->all()));
            }
            if ($gets['id']) {
                DB::table('sms')->where('id', $gets['id'])->update($gets);
            } else {
                DB::table('sms')->insert($gets);
            }
            return $this->json('恭喜你，操作成功。', true);
        }
    }

    public function createAction()
    {
        return $this->editAction();
    }

    public function editAction()
    {
        $id  = (int)Request::get('id');
        $row = DB::table('sms')->where('id', $id)->first();
        return $this->render([
            'row' => $row,
        ], 'edit');
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            DB::table('sms')->whereIn('id', $id)->delete();
            return $this->json('删除成功。', true);
        }
    }

    /**
     * 短信测试
     */
    public function testAction()
    {
        $phone = Request::get('phone');
        if (Request::method() == 'POST') {
            $send = NotificationService::sms([$phone], '测试短信', '测试短信内容');
            if ($send) {
                return $this->json('测试短信已发送。', true);
            } else {
                return $this->json('发送失败，请检查短信配置。');
            }
        }
        return $this->render([
            'phone' => $phone
        ]);
    }
}
