<?php namespace Gdoo\Index\Controllers;

use Validator;
use DB;
use Request;

use App\Support\AES;

use Gdoo\Model\Models\Bill;
use Gdoo\Model\Models\Model;

use Gdoo\Model\Services\ModelService;
use Gdoo\Model\Services\StepService;

use Gdoo\Model\Models\Run;
use Gdoo\Model\Models\RunStep;
use Gdoo\Model\Models\RunLog;
use Gdoo\Model\Models\Step;
use Gdoo\Model\Models\Field;
use Gdoo\Model\Form;
use Gdoo\Model\Models\Permission;

use App\Support\Hook;
use App\Support\Dialog;
use App\Support\JPush;
use Gdoo\Model\Models\Template;

class WorkflowController extends DefaultController
{
    public function __construct()
    {
        // 流程审核权限
        $this->permission[] = 'flowDraft';
        $this->permission[] = 'flowAudit';
        $this->permission[] = 'flowLog';
        $this->permission[] = 'flowRead';
        $this->permission[] = 'flowStep';
        $this->permission[] = 'flowUser';
        $this->permission[] = 'flowReturn';
        $this->permission[] = 'flowRevise';
        $this->permission[] = 'flowReset';
        
        parent::__construct();
    }

    /**
     * 流程审核
     */
    public function flowAuditAction()
    {
        $gets = Request::all();

        if (Request::method() == 'POST') {
            $master = $gets['master'];
            $keys = AES::decrypt($master['key'], config('app.key'));
            list($bill_id, $id) = explode('.', $keys);
            $bill = Bill::find($bill_id);
            $models = ModelService::getModels($bill->model_id);
            $model = $models[0];

            if (intval($gets['step_next_id']) == 0) {
                return $this->json('审批进程必须选择。');
            }

            // 获取审核用户
            $step_user_ids = array_filter(explode(',', $gets['step_next_user']));
            
            $run_id = $master['run_id'];
            $step_id = $master['step_id'];
            // 审批步骤数据
            if ($run_id > 0) {
                $step = RunStep::where('bill_id', $bill_id)
                ->where('run_id', $run_id)
                ->where('step_id', $step_id)
                ->first();
            } else {
                $step = Step::where('bill_id', $bill_id)
                ->where('type', 'start')
                ->first();
            }
            if (empty($step)) {
                return $this->json('当前审批节点不存在。');
            }

            if ($run_id > 0) {
                $step_next = RunStep::where('bill_id', $bill_id)
                ->where('run_id', $run_id)
                ->where('step_id', $gets['step_next_id'])
                ->first();
            } else {
                $step_next = Step::where('bill_id', $bill_id)
                ->where('id', $gets['step_next_id'])
                ->first(['*', 'id as step_id']);
            }

            if (empty($step_next)) {
                return $this->json('转入审核节点不存在。');
            }

            if ($step_next['type'] == 'end') {
                // 流程结束节点
                $gets['step_next_type'] = 'end';
            } else {
                if (empty($step_user_ids)) {
                    return $this->json('审核人不能为空。');
                }
            }

            // 执行模式
            $run_mode = $step_next['run_mode'];

            if ($run_mode == 1) {
                if (count($step_user_ids) > 1) {
                    return $this->json('单人执行不能多个审核人。');
                }
            }

            $gets['remark'] = trim($gets['remark']);

            // 表单存在审核意见字段
            if (isset($gets['step_remark'])) {
                // 审核对话框没有填写审核意见
                if ($gets['remark'] == '') {
                    $gets['remark'] = $gets['step_remark'][$step_id];
                } else {
                    if (empty($gets['step_remark'][$step_id])) {
                        $gets['step_remark'][$step_id] = $gets['remark'];
                    }
                }
            } else {
                if ($gets['remark'] != '') {
                    $gets['step_remark'][$step_id] = $gets['remark'];
                }
            }

            // 转到步骤条件检查
            if ($gets['step_next_type'] == 'next' || $gets['step_next_type'] == 'end') {
                // 获取数据
                if (intval($bill['form_type']) == 0) {
                    foreach($models as $model) {
                        if ($model['parent_id'] == 0) {
                            $gets[$model['table']] = DB::table($model['table'])->where('id', $id)->first();
                        } else {
                            $gets[$model['table']]['rows'] = DB::table($model['table'])->where($model['relation'], $id)->get()->toArray();
                        }
                    }
                }

                // 检查表单
                $valid = Form::flowRules($models, $gets);
                if ($valid['rules']) {
                    $v = Validator::make($gets, $valid['rules'], $valid['messages'], $valid['attributes']);
                    if ($v->fails()) {
                        $errors = $v->errors()->all();
                        return $this->json(join('<br>', $errors));
                    }
                }
            }

            $gets['run_mode'] = $run_mode;
            $gets['step_user_ids'] = $step_user_ids;

            $step_next_inform = array_filter((array)$gets['step_next_inform']);
            $notify_user_ids = array_keys($step_next_inform);
            $notify_step_ids = array_values($step_next_inform);

            $gets['step_next_inform'] = $step_next_inform;
            $gets['notify_step_ids'] = $notify_step_ids;
            $gets['notify_user_ids'] = $notify_user_ids;

            // 保存数据
            if ($bill['form_type'] == 1) {
                $id = Form::store($bill, $models, $gets, $id, 'audit');
            } else {
                $id = Form::audit($bill, $models, $gets, $id);
            }

            $url = url($master['uri'].'/show', ['id' => $id, 'client' => $master['client']]);
            return $this->json($bill['name'].'审核成功', $url);
        }

        $keys = AES::decrypt($gets['key'], config('app.key'));
        list($bill_id, $id) = explode('.', $keys);
        $bill = Bill::find($bill_id);
        $model = ModelService::getModel($bill->model_id);

        $run_id = $gets['run_id'];
        $step_id = $gets['step_id'];

        // 有办理记录
        if ($run_id > 0) {
            $run_step = RunStep::where('bill_id', $bill_id)
            ->where('run_id', $run_id)
            ->where('step_id', $step_id)
            ->first();
            $join = explode(',', $run_step->join);
            $run_steps = RunStep::where('bill_id', $bill_id)
            ->where('run_id', $run_id)
            ->where('option', 1)
            ->whereIn('step_id', $join)
            ->get();
        } else {
            $run_step = Step::where('bill_id', $bill_id)
            ->where('type', 'start')
            ->first();
            $join = explode(',', $run_step->join);
            $run_steps = Step::where('bill_id', $bill_id)
            ->where('option', 1)
            ->whereIn('id', $join)
            ->get(['*', 'id as step_id']);
        }

        return view('model/flowAudit', [
            'run_step' => $run_step,
            'run_steps' => $run_steps,
            'table' => $model->table,
        ]);
    }

    /**
     * 撤回流程
     */
    public function recallAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {

            $auth = auth()->user();

            $bill_id = $gets['bill_id'];
            $data_id = $gets['data_id'];
            $log_id = $gets['log_id'];
            $remark = $gets['remark'];

            if (trim($remark) == '') {
                return $this->json('撤回原因必须填写');
            }

            $bill = Bill::find($bill_id);
            $model = Model::find($bill->model_id);

            // 单据生效后无法撤回
            $data = DB::table($model['table'])->where('id', $data_id)->first();
            if ($data['status'] == 1) {
                return $this->json($bill->name.'['.$data['sn'].']已生效，无法撤回。');
            }

            // 撤回的节点
            $log = RunLog::where('bill_id', $bill_id)
            ->where('id', $log_id)
            ->first();

            // 当前流程实例运行id
            $run_id = $log['run_id'];

            $next_logs = RunLog::where('bill_id', $bill_id)
            ->where('run_id', $run_id)
            ->where('parent_id', $log_id)->get();

            foreach ($next_logs as $next_log) {
                // 已经办理无法撤回
                if ($next_log['status'] == 1) {
                    return $this->json($bill->name.'['.$next_log['run_name'].']已办理，无法撤回。');
                }
            }

            DB::beginTransaction();
            try {

                foreach ($next_logs as $next_log) {
                    if ($next_log['option'] > 0) {
                        $next_log['remark'] = $remark;
                        $next_log['run_status'] = 'recall';
                        $next_log['updated_id'] = $auth['id'];
                        $next_log['updated_by'] = $auth['name'];
                        $next_log['updated_at'] = time();
                        $next_log->save();
                    } else {
                        // 知会节点直接删除
                        $next_log->delete();
                    }
                }

                // 更新撤回节点数据
                if ($log['parent_id'] > 0) {
                    // 不是开始节点更新所有节点
                    RunLog::where('bill_id', $bill_id)
                    ->where('run_id', $run_id)
                    ->where('parent_id', $log['parent_id'])
                    ->update([
                        'status' => 0,
                        //'updated_id' => 0,
                        //'updated_at' => 0,
                        //'updated_by' => '',
                    ]);
                } else {
                    // 是开始节点只更新自己
                    $log->status = 0;
                    //$log->updated_id = 0;
                    //$log->updated_at = 0;
                    //$log->updated_by = '';
                    $log->save();
                }

                $status = $log['parent_id'] > 0 ? '-1' : '0';

                DB::table($model['table'])
                ->where('id', $data_id)
                ->update([
                    'status' => $status,
                ]);

                DB::commit();

            } catch (\Exception $e) {
                DB::rollback();
                abort_error($bill->name.'撤回失败:'.$e->getMessage());
            }
            return $this->json($bill->name.'流程撤回成功', true);
        }

        $keys = AES::decrypt($gets['key'], config('app.key'));
        list($bill_id, $data_id) = explode('.', $keys);
        $log_id = $gets['log_id'];
        return view('model/recall', [
            'bill_id' => $bill_id,
            'data_id' => $data_id,
            'log_id' => $log_id,
        ]);
    }

    /**
     * 弃审流程
     */
    public function abortAction()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {

            $bill_id = $gets['bill_id'];
            $data_id = $gets['data_id'];
            $remark = $gets['remark'];

            if ($remark == '') {
                return $this->json('弃审原因必须填写');
            }
            $bill = Bill::find($bill_id);
            $model = Model::find($bill->model_id);
            $run = Run::where('bill_id', $bill_id)->where('data_id', $data_id)->first();
            $data = DB::table($model->table)->find($data_id);
            if (empty($data)) {
                return $this->json('流程任务不存在。');
            }

            DB::beginTransaction();
            try {
                Hook::fire($model->table.'.onBeforeAbort', ['data' => $data, 'table' => $model->table, 'id' => $data_id]);

                // 获取结束节点
                $logs = RunLog::where('run_id', $run->id)
                ->where('bill_id', $bill_id)
                ->where('run_status', 'end')
                ->get();

                if ($logs->isEmpty()) {
                    return $this->json('流程无结束节点无法弃审。');
                }

                // 删除结束节点前下一步节点(主要是知会节点)
                RunLog::where('run_id', $run->id)
                ->where('bill_id', $bill_id)
                ->whereIn('parent_id', $logs->pluck('id'))
                ->delete();

                foreach ($logs as $log) {
                    $log->run_status = 'draft';
                    $log->status = 0;
                    //$log->updated_at = 0;
                    //$log->updated_id = 0;
                    //$log->updated_by = '';
                    $log->remark = $remark;
                    $log->save();
                }

                Run::where('bill_id', $bill_id)->where('data_id', $data_id)
                ->update([
                    'actived_at' => 0,
                    'actived_id' => 0,
                    'actived_by' => ''
                ]);

                DB::table($model->table)
                ->where('id', $data_id)
                ->update([
                    'status' => '2',
                ]);

                DB::commit();

                return $this->json($bill->name.'流程弃审成功', true);

            } catch (\Exception $e) {
                DB::rollback();
                abort_error($bill->name.'弃审:'.$e->getMessage());
            }
        }

        $keys = AES::decrypt($gets['key'], config('app.key'));
        list($bill_id, $data_id) = explode('.', $keys);
        return view('model/abort', [
            'bill_id' => $bill_id,
            'data_id' => $data_id,
        ]);
    }

    /**
     * 标记已阅读
     */
    public function flowReadAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::get('master');
            if ($gets['run_log_id']) {
                RunLog::where('id', $gets['run_log_id'])->update(['status' => 1, 'run_status' => 'read']);
            }
            return $this->json('阅读成功', true);
        }
    }

    /**
     * 流程清除重置
     */
    public function flowResetAction()
    {
        if (Request::method() == 'POST') {
            $master = Request::get('master');
            $keys = AES::decrypt($master['key'], config('app.key'));
            list($bill_id, $id) = explode('.', $keys);
            $bill = Bill::find($bill_id);
            $model = ModelService::getModel($bill->model_id);
            if ($master['run_id']) {
                DB::beginTransaction();
                try {
                    Run::where('id', $master['run_id'])->delete();
                    RunStep::where('run_id', $master['run_id'])->delete();
                    RunLog::where('run_id', $master['run_id'])->delete();
                    DB::table($model->table)->where('id', $id)->update(['status' => 0]);

                    DB::commit();
                    return $this->json('流程重置成功。', true);

                } catch (\Exception $e) {
                    DB::rollBack();
                    return $this->json($e->getMessage());
                }
            }
        }
    }
    
    /**
     * 保存草稿
     */
    public function flowDraftAction()
    {
        $gets = Request::all();
        $master = $gets['master'];
        $keys = AES::decrypt($master['key'], config('app.key'));
        list($bill_id, $id) = explode('.', $keys);
        $bill = Bill::find($bill_id);
        $models = ModelService::getModels($bill->model_id);

        if (Request::method() == 'POST') {
            // 检查表单
            $valid = Form::flowRules($models, $gets);
            if ($valid['rules']) {
                $v = Validator::make($gets, $valid['rules'], $valid['messages'], $valid['attributes']);
                if ($v->fails()) {
                    $errors = $v->errors()->all();
                    return $this->json(join('<br>', $errors));
                }
            }
            // 保存草稿
            $id = Form::store($bill, $models, $gets, $id);

            // 保存草稿跳转到编辑界面
            if ($bill['audit_type'] == 1 && $bill['form_type'] == 1) {
                $uri = $master['uri'].'/audit';
            } else {
                $uri = $master['uri'].'/show';
            }
            
            // 保存草稿跳转到编辑界面
            $url = url($uri, ['id' => $id, 'client' => $master['client']]);
            return $this->json($bill['name'].'保存成功。', $url);
        }
    }

    /**
     * 获取办理步骤
     */
    public function flowStepAction()
    {
        $gets = Request::all();
        $master = $gets['master'];
        $keys = AES::decrypt($master['key'], config('app.key'));
        list($bill_id, $id) = explode('.', $keys);

        $type = $gets['step_next_type'];
        $bill_id = $master['bill_id'];
        $run_id = $master['run_id'];
        $step_id = $master['step_id'];
        $run_log_id = $master['run_log_id'];

        $bill = Bill::find($bill_id);
        $model = Model::where('id', $bill->model_id)->first();

        $auth = auth()->user();
        $table = $model->table;
        
        if ($bill['form_type'] == 1) {
            $data = $gets[$table];
        } else {
            $data = DB::table($table)->where('id', $master['id'])->first();
            $gets[$table] = $data;
        }

        $gets['master']['table'] = $table;
        $gets['master']['auth'] = $auth;

        // 有办理记录
        if ($run_id > 0) {
            $run_step = RunStep::where('bill_id', $bill_id)
            ->where('run_id', $run_id)
            ->where('step_id', $step_id)
            ->first();
            // 获取本流程所有节点
            $steps = RunStep::where('bill_id', $bill_id)
            ->where('run_id', $run_id)
            ->get()->keyBy('step_id')->toArray();
        } else {
            // 新建表单
            $run_step = Step::where('bill_id', $bill_id)
            ->where('type', 'start')
            ->first(['*', 'id as step_id']);
            // 获取本流程所有节点
            $steps = Step::where('bill_id', $bill_id)
            ->get(['*', 'id as step_id'])
            ->keyBy('step_id')->toArray();
        }

        $log = RunLog::where('id', $run_log_id)->first();

        $next_steps = [];

        switch ($type) {
            case 'next':
                $next_steps = StepService::getNextSteps($steps, $run_step['step_id'], $gets);
                break;
            case 'back':
                $next_steps = StepService::getBackSteps($steps, $run_step['step_id'], $gets);
                break;
        }

        $modes = [
            1 => '单人执行',
            2 => '多人执行',
            3 => '全体执行',
            4 => '竞争执行',
        ];

        $tree_steps = array_nest($next_steps);

        $tpl = '';
        $inform_text = '请您及时办理由'.$auth->name.'转交的'.$model['name'].'('.$data['sn'].')。';
        $step_ids = $informs = $users = $inform_sms = [];

        if (not_empty($tree_steps)) {
            
            $notify_user_ids = [];
            $notify_step_ids = [];

            foreach ($tree_steps as $tree_step) {

                if ($type == 'back') {
                    
                } else {
                    if ($tree_step['option'] == 0) {
                        $parent_id = $tree_step['parent'][0];
                        $user_ids = $tree_step['user_ids'];
                        if (not_empty($user_ids)) {
                            $notify_user_ids[$parent_id] = array_merge((array)$notify_user_ids[$parent_id], $user_ids);
                            $notify_step_ids[$parent_id][$tree_step['step_id']] = join(',', $user_ids);
                        }
                    }
                }
            }

            $index = 0;
            foreach ($tree_steps as $step) {

                if ($step['hide']) {
                    continue;
                }

                if ($step['option'] == 1) {
                    $name = 'step_next_id';
                    $step['name'] = $step['type'] == 'end' ? $step['name'] : $step['name'].' <small class="label bg-light">'.$modes[$step['run_mode']].'</small>';
                    $tpl .= '<div><label class="i-checks i-checks-sm">';
                    
                    $checked = $index == 0 ? 'checked="checked"' : '';
                    $index++;

                    $tpl .= '<input class="step_next_id" '.$checked.' type="radio" name="'.$name.'" value="'.$step['step_id'].'"><i></i>'.$step['name'];
                    $tpl .= '</label></div>';

                    // 通知信息
                    $inform = json_decode($step['notify'], true);
                    $inform_sms[$step['step_id']] = (bool)$inform['sms'];

                    // 审核人
                    if ($step['type'] != 'end') {
                        $user = Dialog::user('user', 'step_next_user', join(',', $step['user_ids']), 1, $step['select_org'] == 0);
                        $users[$step['step_id']] = $user;
                    }

                    if ($type == 'back') {
                    } else {
                        // 知会人
                        $parent_id = $step['parent'][0];
                        $user_ids = (array)$notify_user_ids[$parent_id];
                        $step_ids = (array)$notify_step_ids[$parent_id];

                        if ($type == 'next' || $type == 'end') {
                            $html = Dialog::user('user', 'step_next_cc', join(',', $user_ids), 1, 1);
                            foreach($step_ids as $step_id => $step_user_ids) {
                                $html .= '<input type="hidden" name="step_next_inform['.$step_id.']" value="'.$step_user_ids.'">';
                            }
                            $informs[$step['step_id']] = $html;
                        }
                    }
                }
            }

            // 退回流程时删除当前知会节点
            if ($type == 'back') {
                $informs = RunLog::where('run_id', $run_id)
                ->where('parent_id', $log['parent_id'])
                ->where('option', 0)
                ->get(['id']);
                foreach($informs as $inform) {
                    $tpl .= '<input type="hidden" name="step_back_inform[]" value="'.$inform['id'].'">';
                }
            }

        } else {
            $tpl = '无';
        }

        return $this->json([
            'tpl' => $tpl,
            'users' => $users,
            'inform_sms' => $inform_sms,
            'inform_text' => $inform_text,
            'informs' => $informs,
        ], true);
    }

    /**
     * 审批记录
     */
    public function flowLogAction()
    {
        $key = Request::get('key');
        $keys = AES::decrypt($key, config('app.key'));
        list($bill_id, $data_id) = explode('.', $keys);

        $auth = auth()->user();

        $run = Run::where('bill_id', $bill_id)
        ->where('data_id', $data_id)
        ->first();
        
        $type_sql = '(' . join(' or ', [db_instr('type', 'edit')]) . ')';
        $template = Template::where('receive_id', 'all')
        ->whereRaw($type_sql)
        ->where('bill_id', $bill_id)
        ->first();

        $tpl = json_decode($template['tpl'], true);

        $step_ids = [];
        foreach ($tpl as $group) {
            foreach ($group['fields'] as $view) {

                list($type, $step_id) = explode('.', $view['field']);
                $show = 1;

                // 跳过指定角色
                if ($view['role_id']) {
                    $role_ids = explode(',', $view['role_id']);
                    if (in_array($auth->role_id, $role_ids)) {
                        $show = 0;
                    }
                }
                
                if ($type == 'flow_step') {
                    $step_ids[$step_id] = $show;
                }
            }
        }

        $rows = DB::select("
            SELECT b.type, b.step_id, a.run_id, a.run_index, a.run_name, a.run_status, a.remark, a.[option], max(a.updated_id) as updated_id, max(a.updated_at) as updated_at, max(a.created_at) as created_at, STRING_AGG(a.user_id, ',') AS user_ids
            FROM model_run_log as a
            left join model_run_step as b on b.id = a.run_step_id
            WHERE a.run_id = ?
            GROUP BY b.type, b.step_id, a.run_index, a.[option], a.run_id, a.run_name, a.run_status, a.remark
            ORDER BY a.run_index asc
        ", [$run['id']]);

        $users = DB::table('user')->get(['id', 'name'])->keyBy('id');
        $res = [];
        foreach($rows as $row) {

            if (isset($step_ids[$row['step_id']])) {
                if ($step_ids[$row['step_id']] == 0) {
                    continue;
                }
            }

            $user_ids = explode(',', $row['user_ids']);
            $unames = [];
            foreach($user_ids as $user_id) {
                $unames[] = $users[$user_id]['name'];
            }
            $row['user_name'] = join(',', $unames);
            $res[] = $row;
        }

        return view('model/flowLog', [
            'rows' => $rows,
        ]);
    }

    /**
     * 回退已经生效的流程
     */
    public function flowReturnAction()
    {
        $gets = Request::all();
        $run_id = $gets['run_id'];
        $step_id = $gets['step_id'];
        $data_id = $gets['data_id'];
        if (Request::method() == 'POST') {
            if (empty($run_id)) {
                return $this->json('流程无法回退。');
            }

            DB::beginTransaction();
            try {
                $run = DB::table('model_run')
                ->where('id', $run_id)
                ->where('data_id', $data_id)
                ->first();

                $bill = DB::table('model_bill')
                ->where('id', $run['bill_id'])
                ->first();

                $model = DB::table('model')
                ->where('id', $bill['model_id'])
                ->first();

                $data = DB::table($model['table'])
                ->where('id', $data_id)->first();
                
                $step = DB::table('model_run_step')
                ->where('step_id', $step_id)
                ->orderBy('id', 'desc')
                ->first();

                $run_index = $run['index'] + 1;

                $auth = auth()->user();

                // 获取本流程的办理最后一条记录
                $log = DB::table('model_run_log')
                ->where('run_step_id', $step['id'])
                ->orderBy('run_index', 'desc')
                ->first();

                $user_ids = StepService::getUser($gets, $step, $auth, $model['table'], $data, $data_id);
                foreach($user_ids as $user_id) {
                    DB::table('model_run_log')->insert([
                        'bill_id' => $bill['id'],
                        'parent_id' => $log['parent_id'],
                        'user_id' => $user_id,
                        'run_id' => $run_id,
                        'run_step_id' => $step['id'],
                        'run_name' => $step['name'],
                        'run_status' => 'draft',
                        'status' => 0,
                        'run_index' => $run_index,
                    ]);
                }

                DB::table($model['table'])
                ->where('id', $data_id)
                ->update([
                    'status' => 2,
                    'is_return' => 1,
                ]);

                $run = DB::table('model_run')
                ->where('id', $run_id)
                ->where('data_id', $data_id)
                ->update(['index' => $run_index]);

                DB::commit();
                return $this->json('流程回退成功。', true);

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->json($e->getMessage());
            }
        }
        $step = DB::table('model_run_step')
        ->where('run_id', $gets['run_id'])
        ->where('step_id', $gets['step_id'])
        ->orderBy('id', 'desc')
        ->first();
        return view('model/flowReturn', [
            'gets' => $gets,
            'step' => $step
        ]);
    }

    /**
     * 流程修正
     */
    public function flowReviseAction()
    {
        $gets = Request::all();
        $keys = AES::decrypt($gets['key'], config('app.key'));
        list($bill_id, $id) = explode('.', $keys);

        $run = DB::table('model_run')->where('data_id', $id)->first();
        $bill = DB::table('model_bill')->where('id', $bill_id)->first();
        $run_id = $run['id'];

        if (Request::method() == 'POST') {
            if ($bill['audit_type'] == 1) {

                DB::beginTransaction();
                try {

                    $run_log = $gets['log'];
                    $master = $gets['master'];

                    if ($run_log['step_id'] > 0) {
                        
                        $model = DB::table('model')->where('id', $bill['model_id'])->first();
                        $row = DB::table($model['table'])->where('id', $id)->first();
                        $flow_run = [
                            'bill_id' => $bill['id'],
                            'data_id' => $id,
                            'name' => $bill['name'],
                            'sn' => $row['sn'],
                        ];
                        // 写入往来单位
                        if ($row['customer_id']) {
                            $flow_run['partner_id'] = $row['customer_id'];
                            $flow_run['partner_type'] = 'customer';
                        }
                        if ($row['supplier_id']) {
                            $flow_run['partner_id'] = $row['supplier_id'];
                            $flow_run['partner_type'] = 'supplier';
                        }
        
                        if (empty($run)) {
                            // 写入流程运行信息
                            $run_id = DB::table('model_run')->insertGetId($flow_run);
                            // 复制流程节点到运行节点
                            $steps = DB::table('model_step')->where('bill_id', $bill_id)->get();
                            foreach ($steps as $step) {
                                $step['run_id'] = $run_id;
                                $step['step_id'] = $step['id'];
                                $step['id'] = 0;
                                DB::table('model_run_step')->insert($step);
                            }
                        }
        
                        // 读取一个流程
                        $run_step = DB::table('model_run_step')
                        ->where('run_id', $run_id)
                        ->where('step_id', $run_log['step_id'])
                        ->first();

                        $log = DB::table('model_run_log')
                        ->where('run_step_id', $run_step['id'])
                        ->where('run_id', $run_id)
                        ->first();

                        $data = [];

                        if (empty($log)) {
                            $data = [
                                'bill_id' => $bill['id'],
                                'parent_id' => 0,
                                'run_id' => $run_id,
                                'run_step_id' => $run_step['id'],
                                'run_name' => $run_step['name'],
                                'run_status' => $run_log['run_status'],
                            ];
                        } else {
                            $data = $log;
                        }

                        $data['user_id'] = $run_log['user_id'];
                        if ($run_log['run_status'] == 'next') {
                            $data['run_status'] = 'next';
                            $data['updated_id'] = auth()->id();
                            $data['updated_by'] = auth()->user()->name;
                            $data['updated_at'] = time();
                        } else {
                            $data['status'] = 0;
                            $data['updated_id'] = 0;
                            $data['updated_by'] = '';
                            $data['updated_at'] = 0;
                        }

                        if (empty($log)) {
                            // 写入第一步办理节点
                            DB::table('model_run_log')->insertGetId($data);
                        } else {
                            // 写入第一步办理节点
                            DB::table('model_run_log')->where('id', $log['id'])->update($data);
                        }
                    }

                    $data = [
                        'status' => $master['status'],
                    ];
                    if ($master['created_id']) {
                        $data['created_id'] = $master['created_id'];
                    }

                    // 流程状态修正
                    DB::table($model['table'])->where('id', $id)->update($data);
                    DB::commit();

                    return $this->json($bill->name.'流程修正完成', true);

                } catch (\Exception $e) {
                    DB::rollback();
                    abort_error($bill->name.'流程修正:'.$e->getMessage());
                }
            }
        }

        if ($run) {
            $steps = DB::table('model_run_step')
            ->where('bill_id', $bill_id)
            ->where('run_id', $run['id'])
            ->where('option', 1)
            ->get();
        } else {
            $steps = DB::table('model_step')
            ->where('bill_id', $bill_id)
            ->where('option', 1)
            ->get(['*', 'id as step_id']);
        }

        $rows = DB::table('model_run_log')
        ->where('run_id', $run['id'])
        ->orderBy('id', 'asc')
        ->get();

        $flows = option('flow.status');
        return view('model/flowRevise', [
            'key' => $gets['key'],
            'steps' => $steps,
            'flows' => $flows,
            'rows' => $rows,
        ]);
    }

    /**
     * 统计待办流程数量
     */
    public function flowCountAction()
    {
        $rows = DB::table('model_step_log')->where('user_id', auth()->id())
        ->where('status', 0)
        ->selectRaw('[table],count(id) as count')
        ->groupBy('table')
        ->pluck('count', 'table');
        $rows['all'] = $rows->sum();
        return json_encode($rows);
    }
}