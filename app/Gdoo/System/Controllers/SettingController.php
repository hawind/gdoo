<?php namespace Gdoo\System\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\System\Models\Setting;
use Gdoo\Index\Models\Notification;

use Gdoo\Model\Grid;

use Gdoo\Index\Controllers\DefaultController;

class SettingController extends DefaultController
{
    /**
     * 基本设置
     */
    public function indexAction()
    {
        $header = [
            'master_name' => '系统设置',
            'simple_search_form' => 1,
            'table' => 'setting',
            'master_table' => 'setting',
            'create_btn' => 1,
        ];

        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '配置名称', 'field' => 'setting.name', 'value' => '', 'options' => []],
        ], 'model');

        $query = $search['query'];

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
                'width' => 80,
            ],
            'key' => [
                'field' => 'key',
                'headerName' => 'Key',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 100,
            ],
            'value' => [
                'field' => 'value',
                'headerName' => '值',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 260,
            ],
            'type' => [
                'field' => 'type',
                'headerName' => '类型',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
            ],
            'remark' => [
                'field' => 'remark',
                'headerName' => '备注',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
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
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
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
            $ret = $rows->toArray();
            $ret['header'] = Grid::getColumns($header);
            return $ret;
        }

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
        $row = DB::table('setting')->where('id', $id)->first();
        return $this->render([
            'row' => $row,
        ], 'edit');
    }
    
    /**
     * 上传设置
     */
    public function uploadAction()
    {
        // 扫描字体目录
        $paths = new \DirectoryIterator(public_path('assets/fonts'));
        $fonts = [];
        foreach ($paths as $file) {
            if ($file->isFile()) {
                $fonts[] = $file->getFilename();
            }
        }
        return $this->display([
            'fonts' => $fonts,
        ]);
    }

    /**
     * 图片设置
     */
    public function imageAction()
    {
        // 扫描字体目录
        $paths = new \DirectoryIterator(public_path('assets/fonts'));
        $fonts = [];
        foreach ($paths as $file) {
            if ($file->isFile()) {
                $fonts[] = $file->getFilename();
            }
        }
        return $this->display([
            'fonts' => $fonts,
        ]);
    }

    /**
     * 安全设置
     */
    public function securityAction()
    {
        return $this->display();
    }

    /**
     * 日期时间
     */
    public function datetimeAction()
    {
        $lang = trans('setting');
        return $this->display(array(
            'lang' => $lang,
        ));
    }

    /**
     * 保存
     */
    public function storeAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $rules = [
                'name' => 'required',
                'key' => 'required',
                'type' => 'required',
                'value' => 'required',
            ];
            $v = Validator::make($gets, $rules);
            if ($v->fails()) {
                return $this->json(join('<br>', $v->errors()->all()));
            }
            if ($gets['id']) {
                DB::table('setting')->where('id', $gets['id'])->update($gets);
            } else {
                DB::table('setting')->insert($gets);
            }
            return $this->json('恭喜你，操作成功。', true);
        }
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            DB::table('setting')->whereIn('id', $id)->delete();
            return $this->json('删除成功。', true);
        }
    }
}
