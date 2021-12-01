<?php namespace Gdoo\Model\Controllers;

use DB;
use Auth;
use Request;
use Validator;

use Gdoo\Model\Models\Bill;
use Gdoo\Model\Models\Step;
use Gdoo\Model\Models\Model;
use Gdoo\Model\Models\Field;
use Gdoo\Model\Models\Permission;

use Gdoo\Model\Services\StepService;
use Gdoo\Model\Services\FlowService;

use Gdoo\Index\Controllers\DefaultController;

class StepController extends DefaultController
{
    public $permission = ['condition', 'steps', 'index2', 'save', 'add', 'show'];

    public function index()
    {
        // 更新排序
        if (Request::method() == 'POST') {
            $sorts = Request::get('sort');
            StepService::setSort($sorts);
            return $this->json('恭喜你，操作成功。', true);
        }

        $bill_id = Request::get('bill_id');
        $bill = Bill::find($bill_id);
        $rows = Step::where('bill_id', $bill_id)->orderBy('sort', 'asc')->get()->keyBy('id');

        if ($bill->audit_type == 1) {
            // 开始结果
            $count = Step::where('bill_id', $bill_id)->whereIn('type', ['start', 'end'])->count();
            if ($count == 0) {
                Step::insert([
                    'bill_id' => $bill_id,
                    'name' => '开始',
                    'sort' => 0,
                    'type' => 'start',
                ]);
                Step::insert([
                    'bill_id' => $bill_id,
                    'name' => '结束',
                    'sort' => 255,
                    'type' => 'end',
                ]);
            }
        }

        return $this->display([
            'rows' => $rows,
            'bill_id' => $bill_id,
        ]);
    }

    public function index2()
    {
        // 更新排序
        if (Request::method() == 'POST') {
            $bill_id = Request::get('bill_id');
            $rows = Step::where('bill_id', $bill_id)->orderBy('sort', 'asc')->get()->keyBy('id')->toArray();
            $top = 100;
            $left = 0;
            foreach ($rows as &$row) {
                $row['top'] = $row['posY'];
                $row['left'] = $row['posX'];
                /*
                if ($row['type'] == 1) {
                } else {
                    $left = $left + 40;
                    $row['top'] = $top;
                }
                $row['left'] = $row['left'] + $left;
                if ($row['join']) {
                    $joins = explode(',', $row['join']);
                    $_top = count($joins) * 45;
                    $_left = $left;
                    foreach ($joins as $join) {
                        $rows[$join]['top'] = $_top;
                        $rows[$join]['left'] = $_left;
                        $_top = $_top + 65;
                    }
                }
                */
            }
            return $this->json($rows, true);
        }

        $bill_id = Request::get('bill_id');
        $bill = Bill::find($bill_id);
        $rows = Step::where('bill_id', $bill_id)->orderBy('sort', 'asc')->get()->keyBy('id');

        if ($bill->audit_type == 1) {
            // 开始结果
            $count = Step::where('bill_id', $bill_id)->whereIn('type', ['start', 'end'])->count();
            if ($count == 0) {
                Step::insert([
                    'bill_id' => $bill->id,
                    'name' => '开始',
                    'sort' => 0,
                    'type' => 'start',
                ]);
                Step::insert([
                    'bill_id' => $bill->id,
                    'name' => '结束',
                    'sort' => 255,
                    'type' => 'end',
                ]);
            }
        }

        return $this->display([
            'rows' => $rows,
            'bill' => $bill,
            'bill_id' => $bill_id,
        ]);
    }

    // 克隆步骤
    public function add()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            // 是克隆步骤
            if ($gets['id'] > 0) {
                $step = DB::table('model_step')->where('id', $gets['id'])->first();
                unset($step['id'], $step['join']);
            }
            $count = DB::table('model_step')->where('bill_id', $gets['bill_id'])->count();
            $step['bill_id'] = $gets['bill_id'];
            $step['name'] = '新建节点';
            DB::table('model_step')->insert($step);
            return $this->json('节点添加成功', true);
        }
    }

    public function show()
    {
        $gets = Request::all();
        $row = DB::table('model_step')->where('id', $gets['id'])->first();
        return $this->json($row, true);
    }

    public function save()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $_join = [];
            foreach ($gets['join'] as $join) {
                $_join[$join['id']][] = $join['target'];
            }
            foreach ($gets['position'] as $position) {
                $position['join'] = join(',', (array)$_join[$position['id']]);
                $position['posX'] = (int)$position['posX'];
                $position['posY'] = (int)$position['posY'];
                DB::table('model_step')->where('id', $position['id'])->update($position);
            }
            return $this->json('节点添加成功', true);
        }
    }

    /**
     * 节点条件
     */
    public function condition()
    {
        $id = Request::get('id');
        $bill_id = Request::get('bill_id');

        if (Request::method() == 'POST') {
            $gets = Request::all();
            $step = Step::find($id);
            $step->condition = json_encode($gets['condition'], JSON_UNESCAPED_UNICODE);

            $step->save();
            return $this->json('恭喜你，流程步骤操作成功。', url('step/index', ['bill_id'=>$bill_id]));
        }

        $row = Step::findOrNew($id);

        if (empty($row->join)) {
            return '很抱歉，此进程没有下一步节点。';
        }

        $join = explode(',', $row->join);
        $condition = json_decode($row->condition, true);

        $bill = Bill::find($bill_id);
        $steps = Step::where('bill_id', $bill_id)->whereIn('id', $join)->orderBy('sort', 'ASC')->get();
        $model = Model::with('fields', 'children.fields')->where('id', $bill->model_id)->first();
        
        $fields = [
            ['name' => '[创建人ID]', 'field' => '[start_user_id]', 'auto' => 1],
            ['name' => '[创建人角色ID]', 'field' => '[start_role_id]', 'auto' => 1],
            ['name' => '[创建人部门ID]', 'field' => '[start_department_id]', 'auto' => 1],
            ['name' => '[创建人姓名]', 'field' => '[start_user]', 'auto' => 1],
            ['name' => '[创建人职位]', 'field' => '[start_post]', 'auto' => 1],
            ['name' => '[创建人组]', 'field' => '[start_group]', 'auto' => 1],
            ['name' => '[创建人角色]', 'field' => '[start_role]', 'auto' => 1],
            ['name' => '[创建人部门]', 'field' => '[start_department]', 'auto' => 1],
            ['name' => '[经办人姓名]', 'field' => '[edit_user]', 'auto' => 1],
            ['name' => '[经办人ID]', 'field' => '[edit_user_id]', 'auto' => 1],
            ['name' => '[经办人角色ID]', 'field' => '[edit_role_id]', 'auto' => 1],
            ['name' => '[经办人部门ID]', 'field' => '[edit_department_id]', 'auto' => 1],
            ['name' => '[经办人职位]', 'field' => '[edit_post]', 'auto' => 1],
            ['name' => '[经办人群组]', 'field' => '[edit_group]', 'auto' => 1],
            ['name' => '[经办人角色]', 'field' => '[edit_role]', 'auto' => 1],
            ['name' => '[经办人部门]', 'field' => '[edit_department]', 'auto' => 1],
        ];

        $fields = array_merge($fields, $model->fields->toArray());
        $columns[$model->table] = [
            'master' => 1,
            'name' => $model['name'],
            'data' => $fields
        ];

        foreach ($model->children as $children) {
            $columns[$children->table] = [
                'master' => 0,
                'name' => $children['name'],
                'data' => $children->fields->toArray(),
            ];
        }
        
        return $this->render([
            'model' => $model,
            'condition' => $condition,
            'columns' => $columns,
            'steps' => $steps,
            'row' => $row,
        ]);
    }

    /**
     * 创建步骤
     */
    public function create()
    {
        $id = Request::get('id');
        $bill_id = Request::get('bill_id');

        if (Request::method() == 'POST') {
            $gets = Request::all();

            $gets['field']  = json_encode($gets['field']);
            $gets['notify'] = json_encode($gets['notify']);
            $gets['select_org'] = (int)$gets['select_org'];
            $gets['type_value'] = (string)$gets['type_value'][$gets['type']];

            $rules = array(
                'name' => 'required',
            );
            $v = Validator::make($gets, $rules);

            if ($v->fails()) {
                return $this->back()->withErrors($v)->withInput();
            }

            $flow = Step::findOrNew($gets['id']);
            $flow->fill($gets);
            $flow->save();

            return $this->json('恭喜你，步骤操作成功。', true);
        }

        $row = Step::findOrNew($id);

        $row->condition = json_decode($row->condition, true);
        $row->field = json_decode($row->field, true);
        $row->notify = json_decode($row->notify, true);

        $bill = Bill::find($bill_id);
        $steps = Step::where('bill_id', $bill_id)->orderBy('sort', 'ASC')->get();
        $permissions = Permission::where('bill_id', $bill_id)->orderBy('id', 'ASC')->get();

        $model = Model::with('fields', 'children.fields')->where('id', $bill->model_id)->first();
        $columns[$model->table]['master'] = 1;
        $columns[$model->table]['fields'] = $model->fields;

        foreach ($model->children as $children) {
            $columns[$children->table]['master'] = 0;
            $columns[$children->table]['fields'] = $children->fields;
        }

        $regulars = FlowService::regulars();
        return $this->render([
            'bill' => $bill,
            'model' => $model,
            'columns' => $columns,
            'permissions' => $permissions,
            'regulars' => $regulars,
            'steps' => $steps,
            'row' => $row,
        ]);
    }

    /**
     * 流程移交
     */
    public function move()
    {
        $id = Request::get('id');
        $bill_id = Request::get('bill_id');

        if (Request::method() == 'POST') {
            $gets = Request::all();

            $gets['condition'] = json_encode($gets['condition']);
            $gets['field'] = json_encode($gets['field']);
            $gets['notify'] = json_encode($gets['notify']);

            $gets['type_value'] = (string)$gets['type_value'][$gets['type']];

            $rules = array(
                'name' => 'required',
                'sn' => 'required|numeric|min:1',
            );
            $v = Validator::make($gets, $rules);

            if ($v->fails()) {
                return $this->back()->withErrors($v)->withInput();
            }

            $flow = Step::findOrNew($gets['id']);
            $flow->fill($gets);
            $flow->save();

            return $this->success('index', ['bill_id'=>$bill_id], '恭喜你，流程步骤操作成功。');
        }

        $row  = Step::findOrNew($id);

        $row->join = explode(',', $row->join);
        $row->condition = json_decode($row->condition, true);
        $row->field = json_decode($row->field, true);
        $row->notify = json_decode($row->notify, true);

        $bill = Bill::find($bill_id);
        $steps = Step::where('bill_id', $bill_id)->orderBy('sort', 'ASC')->get();

        $permissions = Permission::where('bill_id', $bill_id)->orderBy('id', 'ASC')->get();

        $model = Model::with('fields', 'children.fields')->where('id', $bill->model_id)->first();
        $columns[$model->table]['master'] = 1;
        $columns[$model->table]['fields'] = $model->fields;

        foreach ($model->children as $children) {
            $columns[$children->table]['master'] = 0;
            $columns[$children->table]['fields'] = $children->fields;
        }

        return $this->render([
            'model' => $model,
            'columns' => $columns,
            'permissions' => $permissions,
            'steps' => $steps,
            'row' => $row,
        ]);
    }

    public function steps()
    {
        $table = Request::get('table');
        $model = Model::where('table', $table)->first();

        $rows = Step::where('model_id', $model->id)
        ->where('type', '!=', 'end')
        ->orderBy('sort', 'asc')
        ->get(['id', 'name']);
        return json_encode($rows);
    }

    public function delete()
    {
        $id = Request::get('id');
        if ($id > 0) {
            $step = Step::find($id);
            Step::where('id', $id)->delete();
            StepService::setFieldSort($step->bill_id);
            return $this->json('恭喜你，流程步骤删除成功。', true);
        }
    }
}
