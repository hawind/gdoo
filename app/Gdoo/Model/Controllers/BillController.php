<?php namespace Gdoo\Model\Controllers;

use DB;
use Auth;
use Request;
use Validator;

use Gdoo\Model\Grid;

use Gdoo\Model\Models\Bill;
use Gdoo\Model\Models\Model;

use Gdoo\Index\Controllers\DefaultController;

class BillController extends DefaultController
{
    public function indexAction()
    {
        $search = search_form([
            'advanced' => '',
        ], [
            ['form_type' => 'text', 'name' => '名称', 'field' => 'model_bill.name', 'value' => '', 'options' => []],
        ], 'model');

        $header = [
            'master_name' => '单据',
            'simple_search_form' => 1,
            'table' => 'model_bill',
            'master_table' => 'model_bill',
            'create_btn' => 1,
        ];

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
            'code' => [
                'field' => 'code',
                'headerName' => '编码',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 100,
            ],
            'uri' => [
                'field' => 'uri',
                'headerName' => '路径',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-left',
                'form_type' => 'text',
                'width' => 140,
            ],
            'model_name' => [
                'field' => 'model_name',
                'headerName' => '主模型',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 120,
            ],
            'audit_type_name' => [
                'field' => 'audit_type_name',
                'headerName' => '审核类型',
                'sortable' => true,
                'suppressMenu' => true,
                'cellClass' => 'text-center',
                'form_type' => 'text',
                'width' => 80,
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

        $header['buttons'] = [
            ['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['search_form'] = $search;
        $query = $search['query'];

        if (Request::method() == 'POST') {

            $model = Bill::leftJoin('model', 'model.id', '=', 'model_bill.model_id')
            ->selectRaw('model_bill.*,model_bill.id as master_id, model.[table] as model_table,model.name as model_name')
            ->orderBy('model_bill.id', 'desc')
            ->where('model_bill.type', 0)
            ->setBy($header);

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }
            $rows = $model->paginate($query['limit'])->appends($query);

            $rows->transform(function($row) {
                $row['updated_dt'] = format_datetime($row['updated_at']);
                
                if ($row['audit_type'] == 0) {
                    $row['audit_type_name'] = '无';
                }
                elseif($row['audit_type'] == 1) {
                    $row['audit_type_name'] = '固定流程';
                }
                elseif($row['audit_type'] == 2) {
                    $row['audit_type_name'] = '自由流程';
                }
                elseif($row['audit_type'] == 3) {
                    $row['audit_type_name'] = '审核';
                }
                $row['sn_rule'] = $row['sn_prefix'].$row['sn_rule'].($row['sn_length'] > 0 ? $row['sn_length'] : '');
                $row['model_name'] = $row['model_name'].'('.$row['model_table'].')';
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
            
            // 单据设置为0
            $gets['type'] = 0;

            $bill = Bill::findOrNew($gets['id']);
            $bill->fill($gets);
            $bill->save();
            return $this->json('恭喜你，操作成功。', true);
        }

        $bill_id = Request::get('id');
        $bill = Bill::find($bill_id);
        $models = Model::where('parent_id', 0)->get();
        
        return $this->render([
            'bill' => $bill,
            'models' => $models,
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
            /*
            Step::whereIn('bill_id', $ids)->delete();
            Run::whereIn('bill_id', $ids)->delete();
            RunLog::whereIn('bill_id', $ids)->delete();
            RunStep::whereIn('bill_id', $ids)->delete();
            Template::whereIn('bill_id', $ids)->delete();
            */
            return $this->json('恭喜你，操作成功。', true);
        }
    }
}
