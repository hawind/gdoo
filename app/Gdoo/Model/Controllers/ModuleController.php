<?php namespace Gdoo\Model\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\Model\Form;
use Gdoo\Model\Grid;

use Gdoo\Model\Models\Module;
use Gdoo\Model\Services\ModuleService;

use Gdoo\Index\Controllers\DefaultController;

class ModuleController extends DefaultController
{
    public $permission = ['refresh'];

    public function indexAction()
    {
        $header = [
            'master_name' => '模块',
            'simple_search_form' => 1,
            'table' => 'model_module',
            'master_table' => 'model_module',
            'create_btn' => 0,
        ];

        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '模块名称', 'field' => 'model_module.name', 'value' => '', 'options' => []],
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
                'headerName' => '模块名称',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-',
                'form_type' => 'text',
                'width' => 0,
            ],
            'status_name' => [
                'field' => 'status_name',
                'headerName' => '状态',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
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
                    'name' => '安装',
                    'action' => 'install',
                    'display' => 0,
                ],[
                    'name' => '升级',
                    'action' => 'upgrade',
                    'display' => 0,
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
                    $row['status_name'] = '已安装';
                } else {
                    $row['status_name'] = '未安装';
                }
                return $row;
            });
            return $rows->toJson();
        }

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['left_buttons'] = [
            ['name' => '更新', 'color' => 'default', 'icon' => 'fa-refresh', 'action' => 'refresh', 'display' => 1],
            ['name' => '安装', 'color' => 'default', 'icon' => 'fa-cloud-download', 'action' => 'install', 'display' => 1],
            ['name' => '打包', 'color' => 'default', 'icon' => 'fa-cube', 'action' => 'package', 'display' => 1],
        ];

        $header['search_form'] = $search;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            Module::whereIn('id', $id)->delete();
            return $this->json('删除成功。', true);
        }
    }

    /**
     * 更新模块列表
     */
    public function refreshAction()
    {
        if (Request::method() == 'POST') {
            $modules = ModuleService::details();
            foreach($modules as $module => $row) {
                $model = Module::firstOrNew(['module' => $module]);
                $model->name = $row['name'];
                $model->module = $module;
                $model->save();
            }
            return $this->json('模块刷新完成。', true);
        }
    }
}
