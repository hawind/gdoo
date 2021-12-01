<?php namespace Gdoo\Model\Services;

use Auth;
use DB;

use App\Support\Hook;

use Gdoo\Model\Models\Step;

class StepService
{
    /**
     * 获取下一步审核节点和抄送节点
     */
    public static function getNextSteps($steps, $parent_id, $params) {
        static $ret;
        foreach ($steps as $step) {
            if ($step['join']) {
                // 获取自己的下级
                $join = explode(',', $step['join']);
                foreach ($join as $step_next_id) {

                    if ($step['step_id'] == $parent_id) {

                        $step_next = $steps[$step_next_id];
                        $step_id = $step_next['step_id'];

                        $step_next['run_step_id'] = $step_next['id'];
                        $step_next['id'] = $step_id;
                        $step_next['parent_id'] = $parent_id;

                        // 多条路径形成的bug(多个审核节点接连到相同节点时会出现)
                        if (isset($ret[$step_next['step_id']])) {
                            $step_id = $step_id.'.'.$parent_id;
                        }

                        // 检查条件(不满足就跳过，不管是知会还是审核)
                        if (static::checkCondition($step, [$step_next], $params)) {
                            
                            $step_user = static::getStepUser($step_next, $params);
                            $step_next = $step_user['step'];
                            $continue = $step_user['continue'];
                            $ret[$step_id] = $step_next;

                            // 节点是审核节点时不再获取下一个节点
                            if ($step_next['option'] == 1) {
                                if ($continue) {
                                    continue;
                                }
                            }

                        } else {
                            continue;
                        }

                        static::getNextSteps($steps, $step_next_id, $params);
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 获取上一步审核节点和抄送节点
     */
    public static function getBackSteps($steps, $parent_id, $params) {
        static $ret;
        foreach ($steps as $step) {
            if ($step['join']) {
                // 获取自己的下级
                $join = explode(',', $step['join']);
 
                foreach ($join as $step_next_id) {
                    if ($step_next_id == $parent_id) {

                        $step_next = $steps[$parent_id];
                        $step_id = $step['step_id'];
                        $step['run_step_id'] = $step['id'];
                        $step['id'] = $step_id;
                        $step['parent_id'] = $parent_id;

                        // 检查条件
                        if (static::checkCondition($step, [$step_next], $params)) {

                            $step_user = static::getStepUser($step, $params);
                            $step = $step_user['step'];
                            $continue = $step_user['continue'];
                            $ret[$step_id] = $step;
                            
                            // 节点是审核节点时不再获取下一个节点
                            if ($step['option'] == 1) {
                                if ($continue) {
                                    continue;
                                }
                            }

                        } else {
                            continue;
                        }

                        static::getBackSteps($steps, $step_id, $params);
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 获取上一步审核节点(单节点)
     */
    public static function getBackStep($log, $run_step, $gets)
    {
        $run_id = $log['run_id'];

        // 查找上级记录
        $parent_log = DB::table('model_run_log')
        ->where('run_id', $run_id)
        ->where('id', $log['parent_id'])->first();

        // 获取上级运行节点
        $parent_run_step = DB::table('model_run_step')
        ->where('run_id', $run_id)
        ->where('id', $parent_log['run_step_id'])
        ->first();

        if ($parent_log['parent_id'] > 0) {
            $parent_user_ids = DB::table('model_run_log')
            ->where('run_id', $run_id)
            ->where('parent_id', $parent_log['parent_id'])
            ->where('option', 1)
            ->pluck('user_id')->toArray();
        } else {
            // 退回到开始取最后一个开始节点
            $parent_user_id = DB::table('model_run_log')
            ->where('run_id', $run_id)
            ->where('parent_id', $parent_log['parent_id'])
            ->where('option', 1)
            ->orderBy('id', 'desc')
            ->value('user_id');
            $parent_user_ids = [$parent_user_id];
        }

        // 上级记录的相关用户
        $parent_run_step['user_ids'] = $parent_user_ids;
        
        $steps = [$parent_run_step['step_id'] => $parent_run_step];
        return $steps;
    }


    /**
     * 匹配节点相关人员
     */
    public static function getStepUser($step, $params) {
        $master = $params['master'];
        $table = $master['table'];
        $id = $master['id'];
        $data = $params[$table];
        $user_ids = static::getUser($params, $step, $master['auth'], $table, $data, $id);
        $step['user_ids'] = DB::table('user')->whereIn('id', (array)$user_ids)->where('status', 1)->pluck('id')->toArray();

        $continue = true;

        $nouser = intval($step['nouser']);

        // 找不到匹配的办理人
        if (empty($step['user_ids'])) {
            if ($nouser == 0) {
                $step['select_org'] = 1;
            } else if ($nouser == 1) {
                // 不跳过节点继续寻找下一个节点
                $continue = false;
                $step['hide'] = true;
            } else if ($nouser == 2) {
                $step['user_ids'] = [$step['nouser_user_id']];
            }
        }
        return ['step' => $step, 'continue' => $continue];
    }

    /**
     * 条件测试
     */ 
    public static function testCondition($a, $b, $t) {
        if($t == '==') {
            return $a == $b;
        }
        if($t == '<>') {
            return $a <> $b;
        }
        if($t == '>') {
            return $a > $b;
        }
        if($t == '<') {
            return $a < $b;
        }
        if($t == '>=') {
            return $a >= $b;
        }
        if($t == '<=') {
            return $a <= $b;
        }
    }

    /**
     * 检查转入条件
     */ 
    public static function checkCondition($run_step, $next_steps, $gets)
    {
        $form_data = static::formDataCondition($gets);

        $step_condition = false;
        $null_condition = 0;

        $next_steps_count = count($next_steps);
        if ($next_steps_count > 0) {
            // 流程转交条件检查
            $conditions = json_decode($run_step['condition'], true);

            foreach ($next_steps as $step) {
                $condition = $conditions[$step['step_id']];

                if (empty($condition)) {
                    $null_condition ++;
                    continue;
                }
                $test = static::testCheckCondition($form_data, $condition, $gets);
                // 条件满足记录步骤数组
                if ($test) {
                    $step_condition = true;
                }
            }
            // 有一种情况，多个转入步骤都是空条件, 或单转入步骤条件为空
            if ($next_steps_count == $null_condition) {
                $step_condition = true;
            }
        }
        return $step_condition;
    }

    /**
     * 
     * 检查条件的表单字段
     */ 
    public static function formDataCondition($gets)
    {
        $master = $gets['master'];

        // 获取单据创建者ID
        if ($master['created_id'] > 0) {
            $created_id = $master['created_id'];
        } else {
            $created_id = Auth::id();
        }

        // 创建人
        $start_user = static::getMacroUser($created_id);

        // 经办人
        $current_user = static::getMacroUser(Auth::id());

        $form_data = [];
        $form_data['[start_user_id]'] = $start_user['user_id'];
        $form_data['[start_role_id]'] = $start_user['role_id'];
        $form_data['[start_department_id]'] = $start_user['department_id'];

        $form_data['[start_user]'] = $start_user['user_name'];
        $form_data['[start_post]'] = $start_user['post_name'];
        $form_data['[start_group]'] = $start_user['group_name'];
        $form_data['[start_role]'] = $start_user['role_name'];
        $form_data['[start_department]'] = $start_user['department_name'];

        $form_data['[edit_user_id]'] = $current_user['user_id'];
        $form_data['[edit_role_id]'] = $current_user['role_id'];
        $form_data['[edit_department_id]'] = $current_user['department_id'];

        $form_data['[edit_user]'] = $current_user['user_name'];
        $form_data['[edit_post]'] = $current_user['post_name'];
        $form_data['[edit_group]'] = $current_user['group_name'];
        $form_data['[edit_role]'] = $current_user['role_name'];
        $form_data['[edit_department]'] = $current_user['department_name'];

        // $form_data['[步骤号]'] = $post['setp_id'];
        // $form_data['[流程设计步骤号]'] = $gets['step_number'];

        // $form_data['[公共附件名称]'] = $post['attachment'];
        // $form_data['[公共附件个数]'] = $post['attachment'];

        return $form_data;
    }

    /** 
     * 测试检查条件
     */
    public static function testCheckCondition($form_data, $conditions, $gets) 
    {
        // 存在条件
        $wheres = [];
        foreach ($conditions as $condition) {
            $f = $condition['f'];
            $c = $condition['c'];
            $v = $condition['v'];
            $t = $condition['t'];

            if (isset($form_data[$f])) {
                $condition['f'] = "\$form_data['".$f."']";
            } else {
                // 分割字段名称
                list($p, $k) = explode('.', $f);

                // 多行子表
                $lines = $gets['models'];
                
                // 子表处理
                if(isset($lines[$p])) {
                    $rows = $gets[$p]['rows'];
                    if ($t) {
                        $_test = false;
                        // 总数
                        if ($t == 'count') {
                            $count = count($rows);
                            if (static::testCondition($count, $v, $c)) {
                                $_test = true;
                            }
                        }
                        // 总和
                        if ($t == 'sum') {
                            $_sum = 0;
                            foreach ($rows as $row) {
                                $_sum += $row[$k];
                            }
                            if (static::testCondition($_sum, $v, $c)) {
                                $_test = true;
                            }
                        }
                        $condition['c'] = '';
                        $condition['v'] = '';
                        if($_test) {
                            $condition['f'] = 'true';
                        } else {
                            $condition['f'] = 'false';
                        }
                    } else {
                        continue;
                    }
                
                } else {
                    // 把变量名称作为字符串赋值
                    $condition['f'] = "\$gets['".$p."']['".$k."']";
                }
            }
            unset($condition['t']);
            $wheres[] = join(' ', $condition);
        }

        // 检查条件
        $where = join(' ', $wheres);
        $test = eval("return $where;");
        return $test;
    }

    /**
     * 检查转入条件
     */ 
    public static function runCheckCondition($run_step, $next_steps, $gets)
    {
        $form_data = static::formDataCondition($gets);

        $steps = [];

        $next_steps_count = count($next_steps);
        if ($next_steps_count > 0) {
            // 流程转交条件检查
            $conditions = json_decode($run_step['condition'], true);

            foreach ($next_steps as $step) {
                $condition = $conditions[$step['step_id']];
                if (empty($condition)) {
                    $steps[] = $step;
                    continue;
                }

                // 组合条件
                $test = static::testCheckCondition($form_data, $condition, $gets);

                // 条件满足记录步骤数组
                if ($test) {
                    $steps[] = $step;
                }
            }
        }
        return $steps;
    }

    /**
     * 
     */
    public static function getFlowStep($steps, $step, $gets, &$ret) {
        $ids = array_filter(explode(',', $step['join']));
        $next_steps = [];
        foreach($ids as $id) {
            if (isset($steps[$id])) {
                $next_steps[] = $steps[$id];
            }
        }
        $rows = static::runCheckCondition($step, $next_steps, $gets);
        foreach($rows as $row) {
            $ret[$row['step_id']] = $row;
            static::getFlowStep($steps, $row, $gets, $ret);
        }
    }

    /**
     * 获取流程字段的数据
     */
    public static function getFlowField($run_id, $row, $flow, $steps, $step, $view, $view_step_id, $action, $permission) 
    {
        $run_step = $steps[$view_step_id];
        if(empty($run_step)) {
            return;
        }

        $write = $permission['flow_step'][$view_step_id];
        $remark = $run_step['run_remark'];
        
        $by = '';
        $line = '&nbsp;&nbsp;';
        if ($run_step['run_updated_id'] > 0) {
            $updated_by = get_user($run_step['run_updated_id'], 'name', false);
            $by = $updated_by.' '.format_datetime($run_step['run_updated_at']);
        }

        if ($action == 'show' || $action == 'print') {
            if ($run_step['run_updated_at'] > 0) {
                if ($remark == '') {
                    $remark = '同意';
                }
            } else {
                if ($write['w'] == 1) {
                    $remark = '';
                }
            }
        }
        
        if ($action == 'show') {
            return '<div id="flow_step_'.$view_step_id.'">'.$remark.$line.$by.'</div>';
        } elseif ($action == 'print') {
            return $remark.$line.$by;
        } else {
            if ($write['w'] == 1) {
                $required = '';
                if (in_array('required', (array)$write['v'])) {
                    $required = 'input-required';
                }
                return '<textarea class="form-control input-sm '.$required.'" autocomplete="off" id="step_remark_'.$view_step_id.'" name="step_remark['.$view_step_id.']">'.$remark.'</textarea>';
            } else {
                return '<textarea class="form-control input-sm" autocomplete="off" id="flow_step_'.$view_step_id.'" disabled="disabled" readonly="readonly">'.$remark.$line.$by.'</textarea>';
            }
        }
    }

    /**
     * 获取流程日志
     */
    public static function getFlowLog($run_id, $data, $model)
    {
        $steps = DB::table('model_run_step')
        ->whereNotIn('type', ['end'])
        ->where('run_id', $run_id)
        ->orderBy('sort', 'asc')
        ->get();

        // 设置主表数据
        $gets['master'] = $data;
        $gets[$model['table']] = $data;

        $step = $steps[0];
        $ret = [];
        $ret[$step['step_id']] = $step;
        $steps = array_by($steps, 'step_id');
        static::getFlowStep($steps, $step, $gets, $ret);
        return $ret;
    }

    /**
     * 获取流程日志
     */
    public static function getFlowLogTpl($run_id, $data, $model, $html)
    {
        $steps = static::getFlowLog($run_id, $data, $model);
        foreach($steps as $log) {
            if ($log['option'] == 1) {
                $remark = $log['remark'];
                if ($log['run_updated_at'] > 0) {
                    if ($remark == '') {
                        if ($log['run_status'] == 'next' || $log['run_status'] == 'end') {
                            $remark = '同意';
                        }
                    }
                    $updated_by = get_user($log['updated_id'], 'name', false);
                    $remark.'&nbsp;&nbsp;'.$updated_by.' '.format_datetime($log['updated_at']);
                }
                $html .= '<div class="row"><div class="col-sm-12 control-text">'.$log['name'].': '.$remark.'</div></div>';
            }
        }
        return $html;
    }


    public static function getMacroUser($user_id)
    {
        if (empty($user_id)) {
            return null;
        }

        return DB::table('user')
        ->LeftJoin('role', 'role.id', '=', 'user.role_id')
        ->LeftJoin('user_group', 'user_group.id', '=', 'user.group_id')
        ->LeftJoin('department', 'department.id', '=', 'user.department_id')
        ->LeftJoin('user_post', 'user_post.id', '=', 'user.post_id')
        
        ->where('user.id', $user_id)
        ->first([
            'user.id as user_id',
            'user.group_id',
            'user.department_id',
            'user.role_id',
            'user.post_id',
            'user_post.name as post_name',
            'user.name as user_name',
            'role.name as role_name',
            'department.name as department_name',
            'user_group.name as group_name'
        ]);
    }

    public static function setSort($sorts)
    {
        $i = 0;
        foreach ($sorts as $id) {
            if ($id > 0) {
                $step = Step::find($id);
                if ($step['type'] == 'start') {
                    continue;
                }
                $step->sort = $i;
                $i++;
                $step->save();
            }
        }
    }

    public static function setFieldSort($model_id)
    {
        $rows = Step::where('bill_id', $model_id)
        ->where('type', '!=', 'end')
        ->orderBy('sort', 'asc')
        ->get();
        
        $j = 0;
        foreach ($rows as $row) {
            $row->sort = $j;
            $j++;
            $row->save();
        }
    }

    public static function getUser($gets, $step, $auth, $table, $data, $id)
    {
        $user_ids = [];
        switch ($step['type']) {
                // 指定办理人
            case 'user':
                $user_ids = explode(',', $step['type_value']);
                break;
                // 负责人
            case 'owner':
                $user_ids = [$auth->owner_id];
                break;
                // 指定角色办理人
            case 'role':
                $roles = explode(',', $step['type_value']);
                $user_ids = DB::table('user')->whereIn('role_id', $roles)->pluck('id')->toArray();
                break;
                // 单据创建者
            case 'created_id':
            case 'start':
                $row = DB::table($table)->find($id);
                $user_ids = [$row['created_id']];
                break;
                // 直属领导
            case 'leader':
                $user_ids = [$auth->leader_id];
                break;
                // 部门主管
            case 'manager':
                $user_ids = [$auth->department->manager];
                break;
                // 主表字段值
            case 'field':
                // 字段是供应商
                if ($step['type_value'] == 'supplier_id') {
                    $supplier = DB::table('supplier')->find($data['supplier_id']);
                    $user_ids = [$supplier['user_id']];
                // 字段是客户
                } else if ($step['type_value'] == 'customer_id') {
                    $customer = DB::table('customer')->find($data['customer_id']);
                    $user_ids = [$customer['user_id']];
                } else {
                    $user_ids = [$data[$step['type_value']]];
                }
                break;
                // 销售组(1级)
            case 'region1':
                $customer_id = $data['customer_id'];
                if ($customer_id > 0) {
                    $customer = DB::table('customer')->find($customer_id);
                    $region3 = DB::table('customer_region')->find($customer['region_id']);
                    $region2 = DB::table('customer_region')->find($region3['parent_id']);
                    $region1 = DB::table('customer_region')->find($region2['parent_id']);
                    $user_ids = [$region1['owner_user_id']];
                }
                break;
                // 销售组(2级)
            case 'region2':
                $customer_id = $data['customer_id'];
                if ($customer_id > 0) {
                    $customer = DB::table('customer')->find($customer_id);
                    $region3 = DB::table('customer_region')->find($customer['region_id']);
                    $region2 = DB::table('customer_region')->find($region3['parent_id']);
                    $user_ids = [$region2['owner_user_id']];
                }
                $region_id = $data['region_id'];
                if ($region_id > 0) {
                    $region3 = DB::table('customer_region')->find($region_id);
                    $region2 = DB::table('customer_region')->find($region3['parent_id']);
                    $user_ids = [$region2['owner_user_id']];
                }
                break;
                // 销售组(3级)
            case 'region3':
                $customer_id = $data['customer_id'];
                if ($customer_id > 0) {
                    $customer = DB::table('customer')->find($customer_id);
                    $region3 = DB::table('customer_region')->find($customer['region_id']);
                    $user_ids = [$region3['owner_user_id']];
                }
                break;
                // 自定义
            case 'custom':
                // 过滤数据
                $_data = Hook::fire($table . '.onSetpUser', ['gets' => $gets, 'step' => $step, 'auth' => $auth, 'table'=> $table, 'data'=> $data, 'user_ids' => $user_ids]);
                extract($_data);
                break;
        }
        return $user_ids;
    }
}
