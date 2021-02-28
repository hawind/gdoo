<?php namespace Gdoo\Stock\Controllers;

use DB;
use Request;
use Validator;

use Gdoo\User\Models\User;
use Gdoo\Stock\Models\Record10;

use Gdoo\Model\Grid;
use Gdoo\Model\Form;

use Gdoo\Index\Controllers\WorkflowController;

class Record10Controller extends WorkflowController
{
    public $permission = ['dialog', 'print3'];

    // 列表
    public function indexAction()
    {
        $header = Grid::header([
            'code' => 'stock_record10',
            'referer' => 1,
            'search'  => ['by' => ''],
        ]);

        $cols = $header['cols'];

        $cols['actions']['options'] = [[
            'name' => '显示',
            'action'  => 'show',
            'display' => $this->access['show'],
        ]];

        $search = $header['search_form'];
        $query = $search['query'];

        if (Request::method() == 'POST') {
            $model = DB::table($header['table'])->setBy($header);
            foreach ($header['join'] as $join) {
                $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
            }
            $model->orderBy($header['sort'], $header['order']);

            /*
            // 外部库管登录
            if (auth()->id() == 2177) {
                $model->whereIn('stock_record10.warehouse_id', [20001, 20047]);
            } else {
                $model->whereNotIn('stock_record10.warehouse_id', [20001, 20047]);
            }
            */

            foreach ($search['where'] as $where) {
                if ($where['active']) {
                    $model->search($where);
                }
            }

            $model->select($header['select']);
            $rows = $model->paginate($query['limit'])->appends($query);
            $items = Grid::dataFilters($rows, $header);
            return $items->toJson();
        }

        $header['buttons'] = [
            //['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            ['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['cols'] = $cols;
        $header['tabs'] = Record10::$tabs;
        $header['bys'] = Record10::$bys;
        $header['js'] = Grid::js($header);

        return $this->display([
            'header' => $header,
        ]);
    }

    // 新建
    public function createAction($action = 'edit')
    {
        $id = (int) Request::get('id');
        $header['action'] = $action;
        $header['code'] = 'stock_record10';
        $header['id'] = $id;
        
        $form = Form::make($header);
        
        $tpl = $action == 'print' ? 'print' : 'create';
        return $this->display([
            'form' => $form,
        ], $tpl);
    }

    // 编辑
    public function editAction()
    {
        return $this->createAction();
    }

    // 审核
    public function auditAction()
    {
        return $this->createAction('audit');
    }

    // 显示
    public function showAction()
    {
        return $this->createAction('show');
    }

    // 打印
    public function print2Action()
    {
        $this->layout = 'layouts.print2';
        $view = $this->createAction('print');
        $viewData = $view->getData();
        print_prince($this->createAction('print'));
    }

    // 显示促销
    public function printAction()
    {
        $id = Request::get('id');
        $template_id = Request::get('template_id');
        if ($template_id == 117) {

            $this->layout = 'layouts.print3';

            $master = DB::table('stock_record10 as m')->where('m.id', $id)
            ->leftJoin('stock_type as st', 'st.id', '=', 'm.type_id')
            ->leftJoin('department', 'department.id', '=', 'm.department_id')
            ->leftJoin('warehouse', 'warehouse.id', '=', 'm.warehouse_id')
            ->selectRaw('m.*, st.name as type_name, warehouse.name as warehouse_name, department.name as department_name')
            ->first();

            $rows = DB::table('stock_record10_data as d')
            ->leftJoin('stock_record10 as m', 'm.id', '=', 'd.record10_id')
            ->leftJoin('product as p', 'p.id', '=', 'd.product_id')
            ->leftJoin('product_unit as pu', 'pu.id', '=', 'p.unit_id')
            ->leftJoin('stock_type as st', 'st.id', '=', 'm.type_id')
            ->where('m.id', $id)
            ->selectRaw('
                d.*,
                p.name as product_name,
                p.code as product_code,
                p.spec as product_spec,
                st.name as type_name,
                pu.name as product_unit
            ')
            ->get();

            $form = [
                'template' => DB::table('model_template')->where('id', $template_id)->first()
            ];

            $tpl = $this->display([
                'master' => $master,
                'rows' => $rows,
                'form' => $form,
            ], 'print/'.$template_id);
            return $tpl;

        } else {
            $this->layout = 'layouts.print2';
            $tpl = $this->createAction('print');
            print_prince($tpl);
        }
    }

    // 显示促销
    public function print3Action()
    {
        $this->layout = 'layouts.print2';
        $id = Request::get('id');
        $template_id = Request::get('template_id');
        if ($template_id == 117) {

            $this->layout = 'layouts.print2';

            $master = DB::table('stock_record10 as m')->where('m.id', $id)
            ->leftJoin('stock_type as st', 'st.id', '=', 'm.type_id')
            ->leftJoin('department', 'department.id', '=', 'm.department_id')
            ->leftJoin('warehouse', 'warehouse.id', '=', 'm.warehouse_id')
            ->selectRaw('m.*, st.name as type_name, warehouse.name as warehouse_name, department.name as department_name')
            ->first();

            $rows = DB::table('stock_record10_data as d')
            ->leftJoin('stock_record10 as m', 'm.id', '=', 'd.record10_id')
            ->leftJoin('product as p', 'p.id', '=', 'd.product_id')
            ->leftJoin('product_unit as pu', 'pu.id', '=', 'p.unit_id')
            ->leftJoin('stock_type as st', 'st.id', '=', 'm.type_id')
            ->where('m.id', $id)
            ->selectRaw('
                d.*,
                p.name as product_name,
                p.code as product_code,
                p.spec as product_spec,
                st.name as type_name,
                pu.name as product_unit
            ')
            ->get();

            $form = [
                'template' => DB::table('model_template')->where('id', $template_id)->first()
            ];

            $template = "report.fr3";    
            $ver = 3.0;

            $id = (int) Request::get('id');
            $header['action'] = 'print';
            $header['code'] = 'stock_record10';
            $header['id'] = $id;
            $form = Form::make($header);

            $Tables = [];
            foreach($form['prints'] as $print) {
                $fields = [];
                foreach($print['fields'] as $field) {
                    $type = 'str';
                    $size = 255;
                    if ($field['type'] == 'INT' || $field['type'] == 'TINYINT') {
                        $type = 'int';
                        $size = 0;
                    }
                    if ($field['type'] == 'DATE') {
                        $type = 'str';
                    }
                    if ($field['type'] == 'DECIMAL') {
                        $type = 'float';
                        $size = 0;
                    }
                    $fields[] = ["type" => $type, "size" => $size, "name" => $field['field'],  "required" => false];
                }
                $Tables[] = [
                    'Name' => $print['name'],
                    'Cols' => $fields,
                    'Data' => $print['data'],
                ];
            }
            $jsonObject = [
                "template" => $template,
                "ver" => $ver,
                "Tables" => $Tables,
            ];

            $jsonStr = json_encode($jsonObject);


            $tpl = $this->display([
                'master' => $master,
                'jsonStr' => $jsonStr,
                'rows' => $rows,
                'form' => $form,
            ], 'print3/'.$template_id);
            return $tpl;

        } else {
            $tpl = $this->createAction('print');
            print_prince($tpl);
        }
    }

    // 删除
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $ids = Request::get('id');
            return Form::remove(['code' => 'stock_record10', 'ids' => $ids]);
        }
    }
}
