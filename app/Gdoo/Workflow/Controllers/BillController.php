<?php namespace Gdoo\Workflow\Controllers;

use DB;
use Auth;
use Request;
use Validator;

use Gdoo\Model\Grid;

use Gdoo\Model\Models\Bill;
use Gdoo\Model\Models\Model;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Model\Models\Run;
use Gdoo\Model\Models\RunLog;
use Gdoo\Model\Models\RunStep;
use Gdoo\Model\Models\Step;
use Gdoo\Model\Models\Template;
use Gdoo\Workflow\Models\BillCategory;

class BillController extends DefaultController
{
    public function indexAction()
    {
        $header = [
            'master_name' => '流程',
            'simple_search_form' => 1,
            'table' => 'model_bill',
            'master_table' => 'model_bill',
            'create_btn' => 1,
        ];

        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '名称', 'field' => 'model_bill.name', 'value' => '', 'options' => []],
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
            'category_name' => [
                'field' => 'category_name',
                'headerName' => '类别',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 120,
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
            'sn_rule' => [
                'field' => 'sn_rule',
                'headerName' => '编号规则',
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
                    'name' => '视图',
                    'action' => 'view',
                    'display' => 1,
                ],[
                    'name' => '流程',
                    'action' => 'flow',
                    'display' => 1,
                ],[
                    'name' => '权限',
                    'action' => 'permission',
                    'display' => 1,
                ],[
                    'name' => '编辑',
                    'action' => 'edit',
                    'display' => $this->access['edit'],
                ]],
                'width' => 160,
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

            $model = Bill::leftJoin('model_bill_category', 'model_bill_category.id', '=', 'model_bill.category_id')
            ->selectRaw('
                model_bill.*,
                model_bill.id as master_id, 
                model_bill_category.name as category_name
            ')
            ->where('model_bill.type', 1)
            ->orderBy('model_bill.id', 'desc')
            ->setBy($header);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $rows = $model->paginate($query['limit'])->appends($query);

            $rows->transform(function($row) {
                $row['updated_dt'] = format_datetime($row['updated_at']);
                $row['sn_rule'] = $row['sn_prefix'].$row['sn_rule'].($row['sn_length'] > 0 ? $row['sn_length'] : '');
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
                'code' => 'required|unique:model_bill,code,'.$gets['id'],
            ];
            $v = Validator::make($gets, $rules);

            if ($v->fails()) {
                return $this->json($v->errors()->first());
            }

            // 流程设置为1
            $gets['audit_type'] = 1;
            $gets['type'] = 1;

            $bill = Bill::findOrNew($gets['id']);
            $bill->fill($gets);
            $bill->save();
            return $this->json('恭喜你，操作成功。', true);
        }

        $bill_id = Request::get('id');
        $bill = Bill::find($bill_id);
        $categorys = BillCategory::get();
        return $this->render([
            'bill' => $bill,
            'categorys' => $categorys,
            'bill_id' => $bill_id,
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
            Bill::whereIn('id', $ids)->delete();
            Step::whereIn('bill_id', $ids)->delete();
            Run::whereIn('bill_id', $ids)->delete();
            RunLog::whereIn('bill_id', $ids)->delete();
            RunStep::whereIn('bill_id', $ids)->delete();
            Template::whereIn('bill_id', $ids)->delete();
            return $this->json('恭喜你，操作成功。', true);
        }
    }
}
