<?php namespace Gdoo\System\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\Index\Services\NotificationService;

use Gdoo\Index\Controllers\DefaultController;

class MailController extends DefaultController
{
    public $permission = ['test'];

    /**
     * 邮件设置
     */
    public function indexAction()
    {
        $header = [
            'master_name' => '邮件',
            'simple_search_form' => 1,
            'table' => 'mail',
            'master_table' => 'mail',
            'create_btn' => 0,
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
            'user' => [
                'field' => 'user',
                'headerName' => '邮箱帐号',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 120,
            ],
            'smtp' => [
                'field' => 'smtp',
                'headerName' => 'SMTP服务器',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 120,
            ],
            'port' => [
                'field' => 'port',
                'headerName' => '服务器端口',
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
            //['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '测试邮件', 'color' => 'default', 'icon' => 'fa-envelope-o', 'action' => 'test', 'display' => 1],
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
        return $this->editAction();
    }

    public function editAction()
    {
        $id  = (int)Request::get('id');
        $row = DB::table('mail')->where('id', $id)->first();
        return $this->render([
            'row' => $row,
        ], 'edit');
    }

    public function storeAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $rules = [
                'name' => 'required',
                'smtp' => 'required',
                'user' => 'required',
                'password' => 'required',
                'port' => 'required',
            ];
            $v = Validator::make($gets, $rules);
            if ($v->fails()) {
                return $this->json(join('<br>', $v->errors()->all()));

            }
            if ($gets['id']) {
                DB::table('mail')->where('id', $gets['id'])->update($gets);
            } else {
                DB::table('mail')->insert($gets);
            }
            return $this->json('恭喜你，操作成功。', true);
        }
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            DB::table('mail')->whereIn('id', $id)->delete();
            return $this->json('操作完成。', true);
        }
    }

    /**
     * 邮件测试
     */
    public function testAction()
    {
        $mail_to = Request::get('mail_to');
        if (Request::method() == 'POST') {
            $send = NotificationService::mail('notification', [$mail_to], '测试邮件', '测试邮件内容');
            if ($send) {
                return $this->json('测试邮件已发送。', true);
            } else {
                return $this->json('发送失败，请检查邮件地址或SMTP配置。');
            }
        }
        return $this->render([
            'mail_to' => $mail_to
        ]);
    }
}
