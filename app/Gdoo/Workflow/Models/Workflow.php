<?php namespace Gdoo\Workflow\Models;

use Auth;
use DB;
use Schema;
use URL;

use App\Support\Dialog;

use App\Jobs\SendSms;
use Gdoo\Index\Models\BaseModel;

class Workflow extends BaseModel
{
    protected $table = 'work';

    public static $_options = [
        'todo'  => '待办中',
        'trans' => '已办结',
        'done'  => '已结束',
    ];

    public static $_timeout = [
        'step'  => '超时步骤',
        'count' => '超时统计',
        'rank'  => '超时排名',
    ];

    // 过滤经办人选择
    public function getfilterUsers($type, $department_id, $role_id, $user_id)
    {
        $model = DB::table('user');
        switch ($type) {
            // 获取全部指定经办人
            case 0:
                if ($department_id) {
                    $sql[] = 'department_id IN('.$department_id.')';
                }
                if ($role_id) {
                    $sql[] = 'role_id IN('.$role_id.')';
                }
                if ($user_id) {
                    $sql[] = 'id IN('.$user_id.')';
                }

                if (count($sql) > 0) {
                    $model->where('department_id IN('.join(" OR ", $sql).')');
                }
            break;
            // 选择本部门
            case 1:
            
                if ($department_id > 0) {
                    $model->where('department_id=?', $department_id);
                }
            break;
            // 选择上级部门
            case 2:
                $nodes = DB::table('department')->getTreeById($department_id, 2);
                $ids = array_fetch($nodes, 'id');
                if (count($ids) > 0) {
                    $model->where('department_id IN('.join(",", $ids).')');
                }
            break;
            // 选择下级部门
            case 3:
                $nodes = DB::table('department')->getTreeById($department_id);
                $ids = array_fetch($nodes, 'id');
                if (count($ids) > 0) {
                    $model->where('department_id IN('.join(",", $ids).')');
                }
            break;
            // 选择本岗位
            case 4:
                if ($role_id > 0) {
                    $model->where('role_id=?', $role_id);
                }
            break;
        }

        $rows = $model->from('user', 'id,role_id,department_id,username,name,phone')
        ->where('status=1')->pk()->select();

        return $rows;
    }

    // 自动选人选择
    public function getSelectUser($data)
    {
        // 工作流办理主表
        $process = DB::table('work_process')->where('id', $data['process_id'])->first();

        // 工作流设计步骤表
        $step = DB::table('work_step')->where('id', $data['step_id'])->first();

        $user_type = $step['select_user_type'];
        $select_user_id = $select_user_sign = 0;

        switch ($user_type) {
            // 选择工作发起人
            case 1:
                $select_user_id = $process['start_user_id'];
            break;

            // 选择经办人本部门主管
            case 2:
            // 选择经办人上级主管领导
            case 3:
            // 选择经办人上级分管领导
            case 4:
                $department = DB::table('department')->where('id', Auth::user()->department_id)->first();
                if (is_array($department)) {
                    if ($user_type == 2) {
                        $select_user_id = $department['manager'];
                    }
                    if ($user_type == 3) {
                        $select_user_id = $department['leader'];
                    }
                    if ($user_type == 4) {
                        $select_user_id = $department['superior'];
                    }
                }
            break;

            // 选择经办人一级部门主管
            case 5:
                $department = DB::table('department')->where('parent_id', 0)->first();
                if (is_array($department)) {
                    $select_user_id = $department['manager'];
                }
            break;

            // 选择指定人员
            case 6:
                $select_user_id   = $step['select_user_id'];
                $select_user_sign = $step['select_user_sign'];
            break;

            case 7:
                $select_user_id = $data['data_'.$step['select_user_sign']];
            break;

            // 选择指定步骤办理人
            case 8:
                if ($data['process_id'] > 0 && $step['select_user_sign'] > 0) {
                    $process_data = DB::table('work_process_data')
                    ->where('process_id', $data['process_id'])
                    ->where('step_id', $step['select_user_sign'])
                    ->orderBy('process_id', 'asc')
                    ->get();
                    $select_user_id = $process_data[0]['user_id'];
                }
            break;

            // 选择发起人本部门主管
            case 9:
            // 选择发起人上级主管领导
            case 10:
            // 选择发起人上级分管领导
            case 11:
                $user = DB::table('user')->where('id', $process['start_user_id'])->first();
                $department = DB::table('department')->where('id', $user['department_id'])->first();
                if (is_array($department)) {
                    if ($user_type == 9) {
                        $select_user_id = $department['manager'];
                    }
                    if ($user_type == 10) {
                        $select_user_id = $department['leader'];
                    }
                    if ($user_type == 11) {
                        $select_user_id = $department['superior'];
                    }
                }
            break;

            // 选择经办人一级部门主管
            case 12:
                $department = DB::table('department')->where('parent_id', 0)->first();
                if (is_array($department)) {
                    $select_user_id = $department['manager'];
                }
            break;

            // 选择发起人直属领导
            case 13:
                $user = DB::table('user')->where('id', $process['start_user_id'])->first();
                if (is_array($user)) {
                    $select_user_id = $user['leader_id'];
                }
            break;

            // 选择经办人直属领导
            case 14:
                $user = DB::table('user')->where('id', Auth::id())->first();
                if (is_array($user)) {
                    $select_user_id = $user['leader_id'];
                }
            break;
        }
        $step['select_user_id']   = $select_user_id;
        $step['select_user_sign'] = $select_user_sign;
        return $step;
    }

    public function getMacroUser($user_id)
    {
        if (empty($user_id)) {
            return null;
        }

        return DB::table('user')
        ->LeftJoin('role', 'role.id', '=', 'user.role_id')
        ->LeftJoin('department', 'department.id', '=', 'user.department_id')
        ->where('user.id', $user_id)
        ->first(['user.post as position', 'user.name as user_name', 'role.name as role_name', 'department.name as department_name']);
    }

    // 转交工作
    public function nextStep($post)
    {
        if (!$post['work_id'] || !$post['step_id'] || !$post['data_id'] || !$post['process_id']) {
            return '转交工作失败参数错误。';
        }

        // 当前线程数据
        $_data = DB::table('work_process_data')->where('id', $post['data_id'])->first();

        if ($_data['flag'] == 2) {
            return '请不要重复转交工作。';
        }

        // 获取骤数据
        $step = DB::table('work_step')->where('id', $post['step_id'])->first();

        // 流程退回时如果节点是结束流程重置类型为普通节点
        if ($step['type'] == 3 && $post['step_type'] == 'last') {
            $step['type'] = 1;
        }

        // 运行进程数据
        $_process = [];

        switch ($step['type']) {
            // 普通步骤节点
            case 1:
            // 开始步骤节点
            case 2:
                // 检查下一步骤编号
                if (!$post['next_step_id']) {
                    return '下一步必须选择。';
                }

                // 检查下一步骤接收用户编号
                if (!$post['next_user_id']) {
                    return '下一步主办人不能为空。';
                }
                
                $_process_data = [
                    'process_id' => $post['process_id'],
                    'parent_id'  => $post['data_id'],
                    'step_id'    => $post['next_step_id'],
                    'user_id'    => $post['next_user_id'],
                    'number'     => $_data['number'] + 1,
                    'add_time'   => time(),
                ];

                // 写入下一步主办线程
                DB::table('work_process_data')->insert($_process_data);

                /*
                // 会签人数据
                if ($post['next_user_sign']) {

                    $next_user_sign = explode(',', $post['next_user_sign']);
                    // 设置会签人无法操作表单
                    $_process_data['option'] = 0;

                    foreach ($next_user_sign as $user_id) {
                        // 写入下一步会签线程
                        $_process_data['next_user_id'] = $user_id;
                        DB::table('work_process_data')->insert($_process_data);
                    }
                }*/
                
            break;

            // 结束步骤节点
            case 3:
                // 进入进程结束标志
                $_process['end_user_id'] = Auth::id();
                $_process['end_time']    = time();
            break;
            
            // 子流程步骤节点
            case 4:
                return '无子步骤节点功能。';
            break;
        }

        // 递增进程运行编号
        $_process['number'] = $_data['number'] + 1;

        // 下一步骤数据
        $next_step = DB::table('work_step')->where('id', $post['next_step_id'])->first();
        $_process['step_number'] = $next_step['number'];

        DB::table('work_process')->where('id', $post['process_id'])->update($_process);

        // 更新当前线程为已办理
        DB::table('work_process_data')->where('id', $post['data_id'])->update([
            'flag'         => 2,
            'deliver_time' => time()
        ]);

        // 保存会签意见
        // $feedback = Workflow::saveFeedback($post);

        // 组合保存表单数据
        $data = Workflow::saveForm($post);

        // 更新工作表数据
        DB::table('work_data_'.$post['work_id'])->where('process_id', $post['process_id'])->update($data);

        return true;
    }

    // 保存会签数据
    public function saveFeedback($post)
    {
        // 获取会签数据
        $feedback = $post['feedback'];

        if ($feedback['content']) {
            $data = [
                'process_id' => $post['process_id'],
                'step_id'    => $post['step_id'],
                'step_number'=> $post['step_number'],
                'content'    => $feedback['content'],
            ];
            // 保存会签附件
            if ($feedback['attachment']) {
                //$data['attachment'] = attachment_store('work_attachment', $feedback['attachment']);
            }
            DB::table('work_process_feedback')->insert($data);
        }
        return true;
    }


    // 保存表单数据
    public function saveForm($post, $draft = false)
    {
        // 获取表单结构
        $work_form_data = Workflow::getFormData($post['work_id']);

        // 当前步骤表信息
        $step = DB::table('work_step')->where('id', $post['step_id'])->first();
        $write_field = explode(',', $step['field']);

        $old = DB::table('work_data_'.$post['work_id'])->where('process_id', $post['process_id'])->first();

        $data = array();
        foreach ($work_form_data as $key => $value) {

            // 保存草稿不保存宏字段
            if ($value['class'] == 'auto' && $draft == true) {
                continue;
            }

            if ($value['class'] == 'listview') {
                $_data = (array)json_decode($old[$key], true);
                if (count($post[$key])) {
                    // 组合列表数据
                    foreach ($post[$key] as $i => $row) {
                        foreach ($row as $j => $col) {
                            $_data[$i][$j] = $col;
                        }
                    }
                    // 重新组合列表顺序
                    $__data = [];
                    foreach ($_data as $row) {
                        $__data[] = $row;
                    }
                    $data[$key] = json_encode($__data);
                }
                
            } elseif (isset($post[$key])) {
                // 授权写的字段
                if (in_array($value['title'], $write_field)) {
                    $data[$key] = $post[$key];
                }
            }
        }

        // 保存公共附件
        if ($post['attachment']) {
            // 设置附件为已经使用
            $data['attachment'] = attachment_store('work_attachment', $post['attachment']);
        }

        // 流程执行主表编号
        $data['process_id'] = $post['process_id'];

        $process = DB::table('work_process')->where('id', $post['process_id'])->first();

        // 操作日志
        // action_log('work_process', $post['process_id'], 'workflow/workflow/view', 1, $process['name']);

        return $data;
    }

    // 通知相关办理人
    public function notification($post)
    {
        $step = DB::table('work_step')->where('id', $post['step_id'])->first();
        $process = DB::table('work_process')->where('id', $post['process_id'])->first();

        $notification_type = json_decode($step['notification_type'], true);
        $notification_text = json_decode($step['notification_text'], true);

        if (empty($notification_type) or empty($notification_text)) {
            return false;
        }

        // 要通知的三种人: 发起人、经办人、会签人
        $notification_user = array($process['start_user_id'], $post['next_user_id'], $post['next_user_sign']);
        
        // 取得要通知的人信息
        $_users = DB::table('user')->whereIn('id', $notification_user)->get(['id', 'username', 'email', 'phone']);
        $_users = array_by($_users);

        $users = [];

        foreach ($notification_type as $i => $type) {
            $user_id = $notification_user[$i];
            $text    = $notification_text[$i];

            // 用户编号和提醒内容为空时跳过循环
            if (!$user_id || !$text || !$type) {
                continue;
            }

            if (is_array($type)) {
                // 获取通知文本
                $users[$i]['text'] = $text;

                // 获取短信通知人
                if (in_array(1, $type) && $_users[$user_id]['phone']) {
                    $users[$i]['phone'] = $_users[$user_id]['phone'];
                }

                // 获取邮件通知人
                if (in_array(2, $type) && $_users[$user_id]['email']) {
                    $users[$i]['email'] = $_users[$user_id]['email'];
                }

                // 获取即时通通知人
                if (in_array(3, $type) && $_users[$user_id]['username']) {
                    $users[$i]['username'] = $_users[$user_id]['username'];
                }
            }
        }

        foreach ($users as $user) {
            $subject = '项目流程提醒! 主题：'.$process['title'].' - '.$user['text'];
            $body    = '您有新的工作流程办理: <br />工作主题: '.$process['title'].'<br />提醒内容: '.$user['text'].'<br /><a target="_blank" href="'.url('workflow/workflow/edit', ['process_id' => $post['process_id']]).'">[点击办理]</a>';

            // 即时通知
            // NotificationService::site([$user['username']], $subject, $body);

            // 短信通知
            // SendSms::dispatch([$user['phone']], $subject);
            
            // 邮件通知
            // NotificationService::mail([$user['email']], $subject, $body);
        }
        return true;
    }

    // 检查转入条件
    public function checkCondition($post)
    {
        // 读取表单结构
        $work_form_data = Workflow::getFormData($post['work_id']);

        // 获取用户职位
        $position = DB::table('user_position')->get();
        $position = array_by($position);

        // 发起工作用户信息
        $process = DB::table('work_process')->where('id', $post['process_id'])->first();

        $start_user = Workflow::getMacroUser($process['start_user_id']);
        $start_user['position_name'] = $position[$start_user['position']]['title'];

        // 当前经办工作用户信息
        $current_user = Workflow::getMacroUser(Auth::id());
        $current_user['position_name'] = $position[$current_user['position']]['title'];

        $form_data = array();
        $form_data['[发起人姓名]'] = "\$start_user['user_name']";
        $form_data['[发起人职位]'] = "\$start_user['position_name']";
        $form_data['[发起人岗位]'] = "\$start_user['role_name']";
        $form_data['[发起人部门]'] = "\$start_user['department_name']";

        $form_data['[经办人姓名]'] = "\$current_user['user_name']";
        $form_data['[经办人职位]'] = "\$current_user['position_name']";
        $form_data['[经办人岗位]'] = "\$current_user['role_name']";
        $form_data['[经办人部门]'] = "\$current_user['department_name']";

        $form_data['[步骤号]'] = "\$post['setp_id']";
        $form_data['[流程设计步骤号]'] = "\$post['step_number']";

        // 未完成
        $form_data['[公共附件名称]'] = "\$post['attachment']";
        $form_data['[公共附件个数]'] = "\$post['attachment']";

        // 组合表单名称和表名
        foreach ($work_form_data as $key => $row) {
            $form_data['`'.$row['title'].'`'] = "\$post['".$key."']";
        }

        $_step = Db::table('work_step')->find($post['step_id']);
        if ($_step['join']) {
            $_join = explode(',', $_step['join']);
            $rows = Db::table('work_step')->whereIn('id', $_join)->get()->toArray();
        }
        
        if (is_array($rows)) {
            $step_condition = [];
            $null_condition = 0;

            foreach ($rows as $step) {

                // 空条件时记录后跳出继续
                if (empty($step['condition'])) {
                    $null_condition ++;
                    continue;
                }

                // 分析转入条件
                $condition = trim(str_replace("\n", " ", html_entity_decode($step['condition'])));
                
                // 替换表单名称为数据表名
                $condition = strtr($condition, $form_data);
                
                $test = eval("return $condition;");

                // 条件满足记录步骤数组
                if ($test) {
                    $step_condition[] = $step;
                }
            }
            // 有一种情况，多个转入步骤都是空条件, 或单转入步骤条件为空
            if (count($rows) == $null_condition) {
                $step_condition = $rows;
            }
        }
        return $step_condition;
    }

    // 获取表单数据
    public function getFormData($work_id)
    {
        $file = storage_path('cache/workflow/form/'.$work_id.'.php');
        
        if (!is_file($file)) {
            Workflow::cacheForm($work_id);
        }
        return include $file;
    }

    public function cacheForm($work_id, $update = 0)
    {
        $form = DB::table('work')->where('id', $work_id)->first();

        if ($form) {
            if ($update == 1) {
                $form['template'] = preg_replace("/\\s+name\\s?=\\s?\"?data_\\d+\"?/i", "", $form['template']);
                $form['count'] = 0;
            }

            $template = Workflow::parseHTML($form['template'], $form['count']);

            $content = "<?php\n return ".var_export($template[0], true).";\n?>";
            $path = storage_path('cache/workflow/form');

            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            file_put_contents($path.'/'.$work_id.'.php', $content);

            $data['template_short'] = $template[1];
            // 更新表单数量
            if ($update == 1) {
                $data['count'] = count($template[0]);
            }

            DB::table('work')->where('id', $work_id)->update($data);
            return true;
        }
        return false;
    }

    // 获取表单必填字段
    public function getRequired($check)
    {
        $data = array();
        $list = explode(',', $check);
        foreach ($list as $value) {
            $keys = explode('=', $value);
            $data[$keys[0]] = $keys[1];
        }
        return $data;
    }

    // 解析表单
    public function parseForm($template_short, $work = array())
    {
        $item_secret = $js = $jsonload = array();

        $work_form_data = Workflow::getFormData($work['work_id']);

        // 获取字段列表
        $fields = array_by($work_form_data, 'title');

        // 获取是否是必填字段
        $field_check = Workflow::getRequired($work['field_check']);

        foreach ($work_form_data as $key => $item) {
            $id = $item['itemid'];
            $tag = $item['tag'];
            $element = $item['content'];
            $value = $item['value'];
            $title = $item['title'];
            $class = $item['class'];
            $style = $item['style'];
            $hidden = $type = $item['type'];
            $hide = $item['hide'];
            $item_name = "data_".$id;

            if ($value == '/') {
                $value = '';
            }

            // 把行高加上字体行高
            
            if ($tag == 'input') {
                $style = preg_replace_callback('/height:(\d+)px;/i', function ($match) {
                    $height = $match[1] < 28 ? $match[1] + 8 : $match[1];
                    return 'height:'.$height.'px;line-height:'.($height-2).'px;';
                }, $style);
            }

            // 设置隐藏字段
            if (array_find($work['field_secret'], $title)) {
                $template_short = str_ireplace("{" . $item_name . "}", "", $template_short);
                $item_secret[] = $id;
            }

            $attribute = array('name'=>$item_name, 'id'=>$item_name, 'type'=>$type, 'class'=>$class, 'style'=>$style, 'title'=>$title);

            // 取得流程表单数据
            if ($work['step_number'] > 1) {
                $item_value = $work['items'][$item_name];
            } else {
                $item_value = empty($work['items'][$item_name]) ? $value : $work['items'][$item_name];
            }

            // 设置只读字段
            $readonly = true;

            // 打印标识
            $print = $work['printflag'];

            // 必填标识
            $required = $field_check[$title];

            if (($work['work_type'] == 2 && array_find($work['freeitem'], $title)) || ($work['work_type'] == 1 && array_find($work['field_write'], $title)) && $work['opflag']) {
                $readonly = false;
            }

            switch ($class) {

                // 单行文本
                case "text":
                    $attribute['type']  = $hidden == true ? 'hidden' : 'text';
                    $attribute['class'] = self::formRequired($readonly, $required);
                    $item_value = $print == true ? self::formDisplay($item_value, $attribute) : self::formText($item_name, $item_value, $attribute);
                    break;

                // 计算器组件
                case "calc":

                    $attribute['type']  = $hidden == true ? 'hidden' : 'text';
                    $attribute['class'] = self::formRequired($readonly, $required);

                    if ($value == $item_value) {
                        $item_value = '';
                    }

                    if ($readonly === false) {

                        // 分离计算函数
                        $calc = preg_split("/[)(,\+\-\*\/\^\[\]]+/", $value);
                        $calc_type = $calc[0];

                        $var = '';
                        switch ($calc_type) {

                            case "LIST":
                                $s = $fields[$calc[1]]['name'];
                                $var = 'var v = listView.calc.list("'.$s.'",'.$calc[2].');';
                                $var .= '$("#'.$item_name.'").val((isNaN(v) ? 0 : v));';
                                break;

                            case "LISTS":
                                $keys   = ['LISTS('];
                                $values = [''];
                                foreach ($calc as $c) {
                                    $c = trim($c);
                                    list($f, $l) = explode(':', $c);
                                    if ($c && isset($fields[$f])) {
                                        $keys[]   = $c;
                                        $s = $fields[$f]['name'];
                                        $values[] = 'listView.calc.list("'.$s.'",'.$l.')';
                                    }
                                }
                                $value = rtrim($value, ')');
                                $var = 'var v = listView.calc.val('.str_replace($keys, $values, $value).', '.$item['prec'].');';
                                $var .= '$("#'.$item_name.'").val(v);';
                                break;

                            case "MAX":
                            case "MIN":
                            case "AVG":
                            case "MOD":
                            case "ABS":
                                $params = [];
                                foreach ($calc as $i => $_calc) {
                                    if ($i > 0) {
                                        $_calc = $fields[$_calc]['name'];
                                        $params[]= 'listView.calc.getVal("'.$_calc.'")';
                                    }
                                }
                                $n = strtolower($calc_type);
                                $var = 'var v = listView.calc.'.$n.'('.join(',', $params).');';
                                $var .= '$("#'.$item_name.'").val(v);';
                                break;

                            case "SUM":
                                $params = [];
                                foreach ($calc as $i => $c) {
                                    if ($i > 0) {
                                        $c = $fields[$c]['name'];
                                        $params[]= 'listView.calc.getVal("'.$c.'")';
                                    }
                                }
                                $var = 'var v = listView.calc.sum('.join(',', $params).');';
                                $var .= '$("#'.$item_name.'").val(v);';
                                break;
                            
                            case "RMB":
                                $s = $fields[$calc[1]]['name'];
                                $var = 'var v = listView.calc.rmb(listView.calc.getVal("'.$s.'"));';
                                $var .= '$("#'.$item_name.'").val(v);';
                                break;

                            case "DATE":
                            case "DAY":
                            case "HOUR":
                                $s = $fields[$calc[1]]['name'];
                                $d = $fields[$calc[2]]['name'];
                                $n = strtolower($calc_type);
                                $var = 'var v = listView.calc.'.$n.'(listView.calc.getVal("'.$d.'", "date")-listView.calc.getVal("'.$s.'", "date"));';
                                if ($calc_type != "DATE") {
                                    $var .= 'v = isNaN(v) ? 0 : v;';
                                }
                                $var .= '$("#'.$item_name.'").val(v);';
                                break;
                            default:
                                $keys = $values = [];
                                foreach ($calc as $c) {
                                    $c = trim($c);
                                    if ($c && isset($fields[$c])) {
                                        $keys[]   = $c;
                                        $values[] = 'listView.calc.getVal("'.$fields[$c]['name'].'")';
                                    }
                                }
                                $var = 'var v = listView.calc.val('.str_replace($keys, $values, $value).', '.$item['prec'].');';
                                $var .= '$("#'.$item_name.'").val(v);';
                                break;
                        }
                        $js[] = 'var timer = setInterval(function() {'.$var.'}, 1000);';
                    }
                    $item_value = $print == true ? self::formDisplay($item_value, $attribute) : self::formText($item_name, $item_value, $attribute);
                    break;

                // 多行文本
                case "textarea":
                    $attribute['class'] = self::formRequired($readonly, $required);
                    $item_value = $print == true ? self::formDisplay(nl2br($item_value), $attribute) : self::formTextarea($item_name, $item_value, $attribute);
                    break;

                // 单选按钮
                case "radio":
                    $radio_field = explode('`', $item['radio_field']);
                    $radio_check = $item_value == '' ? $item['radio_check'] : $item_value;
                    $disabled = $readonly == true ? ' disabled' : '';
                    $radio_value = null;
                    foreach ($radio_field as $k => $v) {
                        if ($v) {
                            $checked = $radio_check == $v ? ' checked' : '';
                            $radio_value .= '<label class="checkbox-inline"><input type="radio" name="'.$item_name.'" value="'.$v.'"'.$checked.''.$disabled.'>'.$v.'</label>&nbsp;';
                        }
                    }
                    $item_value = $print == true ? $item_value : $radio_value;
                    break;

                // 复选按钮
                case "checkbox checkbox-inline":
                case "checkbox-inline":
                case "checkbox":
                    $attribute['class'] = 'checkbox-inline';
                    $checked = $item_value == "on" ? ' checked' : '';
                    $readonly = $readonly == true ? ' onclick="this.checked='.($checked ? 1 : 0).';"' : '';
					if($print == true) {
						$item_value = $item_value == 'on' ? ' <i class="fa fa-check-square-o"></i> ' : ' <i class="fa fa-square-o"></i> ';
					} else {
						$item_value = '<input '.self::formAttribute($attribute).$readonly.$checked.' />';
					}
                    break;

                // 下拉菜单
                case "select":
                    $select_field = explode('`', $item['radio_field']);
                    $select_value = array();
                    unset($attribute['value']);
                    $select_value[] = '<option value="">请选择</option>';
                    foreach ($select_field as $k => $v) {
                        if ($v) {
                            $selected = $item_value == $v ? " selected" : '';
                            $select_value[] = '<option value="'.$v.'"'.$selected.'>'.$v.'</option>';
                        }
                    }
                    $readonly == true ? $attribute['readonly'] = 'readonly' : '';
                    $readonly == true ? $attribute['onfocus'] = 'this.defaultIndex=this.selectedIndex;' : '';
                    $readonly == true ? $attribute['onchange'] = 'this.selectedIndex=this.defaultIndex;' : '';
                    $item_value = $print == true ? $item_value : '<select '.self::formAttribute($attribute).'>'.join('', $select_value).'</select>';
                    break;

                // 日历控件
                case "date":
                    $attribute['value'] = $item_value;
                    $attribute['autocomplete'] = 'off';
                    $attribute['class'] = $readonly == true ? 'readonly': (isset($field_check[$title]) ? 'input-required': 'input-text');
                    $readonly = $readonly == true ? ' readonly' : ' onfocus="datePicker({dateFmt:\''.$item['date_format'].'\'});"';
                    $item_value = $print == true ? $item_value : '<input '.self::formAttribute($attribute).$readonly.' />';
                    break;

                // 宏控件
                case "auto":
                    $auto_value = '';
                    $dataField = $item['datafld'];
                    $attribute['class'] = 'input-text';
                    $attribute['autocomplete'] = 'off';
                    if ($tag == 'input') {
                        switch ($dataField) {
                            // 当前日期，形如 1999-01-01
                            case "sys_date":
                                $auto_value = date("Y-m-d");
                                break;

                            // 当前日期，形如 2009年1月1日
                            case "sys_date_cn":
                                $auto_value = date("Y年m月d日");
                                break;

                            // 当前日期，形如 2009年1月
                            case "sys_date_cn_short1":
                                $auto_value = date("Y年m月");
                                break;

                            // 当前日期，形如 1月1日
                            case "sys_date_cn_short2":
                                $auto_value = date("m月d日");
                                break;

                            // 当前日期，形如 2009年
                            case "sys_date_cn_short3":
                                $auto_value = date("Y年");
                                break;

                            // 当前年份，形如 2009
                            case "sys_date_cn_short4":
                                $auto_value = date("Y");
                                break;

                            // 当前时间
                            case "sys_time":
                                $auto_value = time();
                                break;

                            // 当前日期+时间
                            case "sys_datetime":
                                $auto_value = date('Y-m-d H:i:s');
                                break;

                            // 当前星期中的第几天，形如 星期一
                            case "sys_week":
                                $weekArray = array("日","一","二","三","四","五","六");
                                $auto_value = "星期".$weekArray[date("w")];
                                break;

                            // 当前用户id
                            case "sys_user_id":
                                $auto_value = Auth::id();
                                break;

                            // 当前用户姓名
                            case "sys_user_name":
                                $auto_value = Auth::user()->name;
                                break;

                            // 当前用户部门(长名称)
                            case "sys_department_name":
                                $department = DB::table('department')->where('id', Auth::user()->department_id)->first(['id', 'name']);
                                $auto_value = $department['name'];
                                break;

                            // 当前用户部门(短名称)
                            case "sys_department_short_name":
                                $department = DB::table('department')->where('id', Auth::user()->department_id)->first(['id', 'name']);
                                $auto_value = $department['name'];
                                break;

                            // 当前用户职位
                            case "sys_user_position":
                                $position = DB::table('user_position')->where('id', Auth::user()->post)->first(['id', 'name']);
                                $auto_value = $position['name'];
                                break;

                           // 当前用户辅助职位
                            case "sys_user_position_assist":
                                $position = DB::table('user_position')->where('id', Auth::user()->position_assist_id)->first(['id', 'name']);
                                $auto_value = $position['name'];
                                break;

                            // 当前用户姓名+日期
                            case "sys_user_name_date":
                                $auto_value = Auth::user()->name.date(' Y-m-d');
                                break;

                            // 当前用户姓名+日期+时间
                            case "sys_user_name_datetime":
                                $auto_value = Auth::user()->name.date(' Y-m-d H:i:s');
                                break;

                            // 当前业务员姓名
                            case "sys_salesman_name":
                                $user = DB::table('user')->where('id', Auth::user()->salesman_id)->first(['id', 'name']);
                                $auto_value = $user['name'];
                                break;

                            // 当前业务员姓名
                            case "sys_salesman_id":
                                $auto_value = Auth::user()->salesman_id;
                                break;

                            // 工作流名称
                            case "sys_workflow_name":
                                $auto_value = $work['work_title'];
                                break;

                            // 工作主题
                            case "sys_process_title":
                                $auto_value = $work['process']['title'];
                                break;

                            // 工作文号
                            case "sys_process_number":
                                $auto_value = $work['process']['number'];
                                break;
                        }
                        $attribute['type'] = $hide == true ? 'hidden' : 'text';
                        $item_value = $item_value == '{auto}' ? '' : $item_value;
                        if ($readonly == false) {
                            $extend = '';
                            $attribute['value'] = $item_value == '' ? $auto_value : $item_value;
                            
                            if (array_find($work['field_auto'], $title)) {
                                $extend = ' readonly';
                                $attribute['class'] = 'readonly';
                            }
                        } else {
                            $extend = ' readonly';
                            $attribute['class'] = 'readonly';
                            $attribute['value'] = $item_value;
                        }
                        $item_value = $print == true ? self::formDisplay($item_value, $attribute) : '<input '.self::formAttribute($attribute).$extend.' />';
                    }
                    if ($tag == 'select') {
                        switch ($dataField) {
                            case "sys_date":
                                $auto_value = $cur_date;
                                break;
                        }
                    }
                    break;

                // 部门人员列表
                case "user":
                    $multi = $item['multi'] == 'true' ? 1 : 0;
                    $item_value = $print == true ? $item_value : Dialog::user($item['selecttype'], $item_name, $item_value, $multi, $readonly, $item['user_width']);
                    break;

                case "listview":
                    $field_title = explode("`", rtrim($item['lv_title'], "`"));
                    $field_size = explode("`", rtrim($item['lv_size'], "`"));
                    $field_sum = explode("`", rtrim($item['lv_sum'], "`"));
                    $field_type = explode("`", rtrim($item['lv_coltype'], "`"));
                    $field_value = explode("`", rtrim($item['lv_colvalue'], "`"));

                    $table = $tbody = $thead = $tfoot = array();

                    $readonly == true;
                    $writes = $checks = [];
                    foreach ($field_title as $_title) {
                        $_name = $title.'['.$_title.']';
                        if (array_find($work['field_write'], $_name)) {
                            $writes[] = true;
                            $readonly = false;
                        } else {
                            $writes[] = false;
                        }
                        if (isset($field_check[$_name])) {
                            $checks[] = $field_check[$_name];
                        } else {
                            $checks[] = '';
                        }
                    }

                    $table[] = '<table class="workflow" id="'.$key.'">';

                    $thead[] = '<tr class="thead"><th style="width:40px;white-space:nowrap;" align="center">序号</th>';
                    $tfoot[] = '<tr class="tfoot"><th style="white-space:nowrap;" align="center">合计</th>';
                    foreach ($field_title as $k => $v) {
                        // 组合视图头
                        $thead[] = '<th align="left" style="white-space:nowrap;width:'.$field_size[$k].'px;">'.$v.'</th>';
                        // 组合视图脚合计
                        $tfoot[] = '<th align="right" id="total_'.$key.'_'.$k.'"></th>';
                    }

                    if ($readonly == false) {
                        $thead[] = '<th style="width:40px;white-space:nowrap;" align="center">操作</th>';
                        $tfoot[] = '<th></th>';
                    }

                    $thead[] = '</tr>';
                    $tfoot[] = '</tr>';

                    $tbody[] = '<tbody id="body_'.$key.'"></tbody>';
                    $table[] = join("\n", $thead);
                    $table[] = join("\n", $tbody);
                    $table[] = join("\n", $tfoot);
                    $table[] = '</table>';

                    $field = array('writes' => $writes, 'checks' => $checks, 'readonly'=>(int)$readonly,'size'=>$field_size,'sum'=>$field_sum,'type'=>$field_type,'value'=>$field_value);

                    $js[] = 'listView.field.'.$key.' = '.json_encode($field).';';
                    $js[] = 'listView.data.'.$key.' = '.(empty($item_value) ? '[]' : $item_value).';';
                    $jsonload[] = 'listView.init("'.$key.'");';
                    $item_value = join("\n", $table);
                    break;
                default:
                    $item_value = '';
            }
            $template_short = str_replace("{".$key."}", $item_value, $template_short);
        }
        return array('template'=>$template_short,'jsonload'=>join("\n", $jsonload),'js'=>join("\n", $js));
    }

    // 表单生成处理函数
    public function formRequired($readonly, $field)
    {
        $class = $readonly == true ? 'readonly': (isset($field) ? 'input-required': 'input-text');
        return $class;
    }

    // 单行文本
    public function formText($item_name, $item_value, $attribute)
    {
        $attr = [];
        unset($attribute['type']);
        foreach ($attribute as $k => $v) {
            if ($v) {
                $attr[] = $k.'="'.$v.'"';
            } else {
                $attr[] = $k;
            }
        }
        return '<input type="text" '.join(' ', $attr).' value="'.$item_value.'" />';
    }

    // 多行文本
    public function formTextarea($item_name, $item_value, $attribute)
    {
        $attr = [];
        unset($attribute['type']);
        foreach ($attribute as $k => $v) {
            if ($v) {
                $attr[] = $k.'="'.$v.'"';
            } else {
                $attr[] = $k;
            }
        }
        return '<textarea '.join(' ', $attr).'>'.$item_value.'</textarea>';
    }

    // 表单生成处理函数
    public function formDisplay($value, $attribute)
    {
        unset($attribute['name'],
            $attribute['type'],
            $attribute['class'],
            $attribute['value'],
            $attribute['title']
        );

        foreach ($attribute as $key => $val) {
            if ($val) {
                $_attribute[] = $key.'='.$val;
            } else {
                $_attribute[] = $key;
            }
        }
        $_attribute = join(' ', $_attribute);
        $value = '<span '.$_attribute.'>'.$value.'</span>';
        return $value;
    }

    // 表单生成处理函数
    public function formAttribute($data)
    {
        $compiled = null;
        foreach ($data as $key => $value) {
            if ($value) {
                $compiled .= ($compiled === null ? '' : ' ').$key.'="'.$value.'"';
            }
        }
        return $compiled;
    }

    /**
     * 创建工作流主表
     */
    public static function updateTable($workId)
    {
        $structure = array(
            'user' => "int NULL",
            'checkbox' => "nvarchar(30) NULL",
            'checkbox checkbox-inline' => "nvarchar(30) NULL",
            'select' => "nvarchar(120) NULL",
            'text' => "nvarchar(255) NULL",
            'textarea' => "nvarchar(max) NULL",
            'auto' => "nvarchar(100) NULL",
            'listview' => "nvarchar(max) NULL",
            'date' => "nvarchar(100) NULL",
            'calc' => "nvarchar(100) NULL",
            'radio' => "nvarchar(30) NULL",
        );

        $work_form_data = Workflow::getFormData($workId);

        $table ='work_data_'.$workId;

        // 检查当前流程数据主表是否存在
        $has_table = Schema::hasTable($table);

        $fields = [];
        if ($has_table) {
            $columns = Schema::getColumnListing($table);
            foreach ($columns as $column) {
                if (strpos($column, 'data_') !== false) {
                    $fields[] = $column;
                }
            }
        }

        $sql_add = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` mediumint(8) NOT NULL auto_increment COMMENT '工作主键',
            `process_id` mediumint(8) NOT NULL default '0' COMMENT '执行流程编号',
            `attachment` varchar(255) NOT NULL COMMENT '公共附件编号集',
            `add_user_id` mediumint(8) NOT NULL default '0' COMMENT '发起人编号',
            `add_time` int(10) NOT NULL default '0' COMMENT '发起时间',";

        foreach ($work_form_data as $key => $data) {
            // 新建表时字段组合
            $sql_add .= '`'.$key.'` '.$structure[$data['class']]." COMMENT '{$data['title']}',\n";
            // 更新表时字段组合
            if (in_array($key, $fields) == false) {
                $sql_update[] = "ALTER TABLE $table ADD ".$key.' '.$structure[$data['class']]." COMMENT '{$data['title']}'";
            }
        }

        $sql_add .= "PRIMARY KEY (`id`),
            KEY `idx_process_id` (`process_id`),
            KEY `idx_add_user_id` (`add_user_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

        // 更新或写入新表
        if ($has_table) {
            if (is_array($sql_update)) {
                foreach ($sql_update as $sql) {
                    DB::statement($sql);
                }
            }
        } else {
            DB::statement($sql_add);
        }
        return true;
    }

    public static function parseHTML($print_model, $start = 0)
    {
        $print_model_new = $print_model_short = $print_model;
        $pos = 0;
        $len = strlen($print_model);
        $item_id_max = $i = $start;
        $j = 0;
        while ($pos < $len) {
            $pos = strpos($print_model, "<", $pos);
            if ($pos === false) {
                break;
            }
            if (substr($print_model, $pos + 1, 1) == "/") {
                $pos += 2;
            } else {
                $pos1 = strpos($print_model, " ", $pos);
                $pos2 = strpos($print_model, ">", $pos);
                if ($pos2 < $pos1) {
                    $pos1 = $pos2;
                }
                $element_name = substr($print_model, $pos + 1, $pos1 - $pos - 1);
                $element_name = strtolower($element_name);
                $element = "";
                switch ($element_name) {
                    case "input":
                        $element = substr($print_model, $pos, $pos2 - $pos + 1);
                        $pos = $pos2 + 1;
                        break;
                    case "button":
                        $pos2 = stripos($print_model, "</button>", $pos2 + 1);
                        $element = substr($print_model, $pos, $pos2 - $pos + 9);
                        $pos = $pos2 + 9;
                        break;
                    case "select":
                        $pos2 = stripos($print_model, "</select>", $pos2 + 1);
                        $element = substr($print_model, $pos, $pos2 - $pos + 9);
                        $pos = $pos2 + 9;
                        break;
                    case "textarea":
                        $pos2 = stripos($print_model, "</textarea>", $pos2 + 1);
                        $element = substr($print_model, $pos, $pos2 - $pos + 11);
                        $pos = $pos2 + 11;
                        break;
                    case "img":
                        $element_tmp = substr($print_model, $pos, $pos2 - $pos + 1);
                        $eclass = self::getattr($element_tmp, "class");
                        if (array_find("listview,sign,radio,progressbar,imgupload,qrcode", $eclass)) {
                            $element = $element_tmp;
                        }
                        $pos = $pos2 + 1;
                        break;
                    default:
                        $pos = $pos2 + 1;
                }
                if ($element != "") {
                    $eclass = self::getattr($element, "class");
                    $ename = self::getattr($element, "name");
                    $etag = self::getattr($element, "tag");
                    if (!strstr($ename, "data_")) {
                        ++$i;
                        $ename = "data_" . $i;
                        $element_new = self::setattr($element, "name", $ename);
                        $item_id = $i;
                    } else {
                        $item_id = intval(substr($ename, strpos($ename, "_") + 1));
                        $element_new = $element;
                        if ($item_id_max < $item_id) {
                            $item_id_max = $item_id;
                        }
                    }
                    if (array_find("listview,sign,radio", $eclass)) {
                        $img_url = URL::to('assets')."/images/icon/";
                        switch ($eclass) {
                            case "listview":
                                $img_url .= "icon-th-list.png";
                                break;
                            case "sign":
                                $img_url .= "sign.gif";
                                break;
                            case "radio":
                                $img_url .= "icon-ok-circle.png";
                        }
                        $element_new = self::setattr($element_new, "src", $img_url);
                    }
                    $element_array[$ename]['itemid'] = $item_id;
                    $element_array[$ename]['tag'] = $etag;
                    $element_array[$ename]['content'] = $element_new;
                    $print_model_short = self::str_replace_once($element, "{" . $ename . "}", $print_model_short);
                    $print_model_new = self::str_replace_once($element, $element_new, $print_model_new);
                    $matches = self::getattr($element);
                    foreach ($matches[1] as $k => $attr) {
                        $attr = strtolower(trim($attr));
                        $value = trim($matches[2][$k]);
                        $value = str_replace("\"", "", $value);
                        $element_array[$ename][$attr] = $value;
                    }
                    if (strtolower($etag) == "textarea" || strtolower($etag) == "select") {
                        $evalue = self::getattr($element, "value");
                        $element_array[$ename]['value'] = $evalue;
                    }
                    $etype = self::getattr($element, "type");
                    if (strtolower($etype) == "checkbox") {
                        $checked = self::getattr($element, "checked");
                        $evalue = empty($checked) ? "" : "on";
                        $element_array[$ename]['value'] = $evalue;
                    }
                }
            }
        }
        $item_id_max = $i < $item_id_max ? $item_id_max : $i;
        $output = array($element_array,
            $print_model_short,
            $print_model_new,
            $item_id_max
        );
        return $output;
    }

    public static function str_replace_once($needle, $replace, $haystack)
    {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            return $haystack;
        }
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    public static function getattr($element, $attr = "")
    {
        $pos = strpos($element, " ");
        $etag = substr($element, 1, $pos - 1);
        if ($attr == "tag") {
            return $etag;
        }
        if (strtolower($etag) == "textarea" && $attr == "value") {
            $expr = "/>([^<>]+)</i";
            preg_match($expr, $element, $matches);
            if ($matches[1]) {
                return $matches[1];
            }
            return "";
        }
        if (strtolower($etag) == "select" && $attr == "value") {
            $expr = "/selected[^<>]*>([^<>]*)</i";
            preg_match($expr, $element, $matches);
            if ($matches[1]) {
                return $matches[1];
            }
            return "";
        }
        $attr_str = "prec|name|title|class|date_format|style|datafld|value|checked|src|type|selecttype|child|data_control|datasrc|lv_title|lv_size|lv_sum|lv_cal|data_control|data_type|data_query|progressstyle|single|data_field|radio_check|radio_field|rich|hide|validation|lv_coltype|lv_colvalue|data_table|data_fld_name|sign_type|sign_color|img_width|img_height|rich_width|rich_height|user_width|user_height|";
        if ($attr != "") {
            if (strstr($attr_str, $attr)) {
                $expr = "/\\s+(".$attr.")\\s*=\\s*(\"[^\"]+\"|[^>\\s]+)/i";
            } else {
                return;
            }
            preg_match_all($expr, $element, $matches);
            $return = str_replace("\"", "", $matches[2][0]);
            return $return;
        } else {
            $expr = "/\\s+(" . $attr_str . ")\\s*=\\s*(\"[^\"]+\"|[^>\\s]+)/i";
            preg_match_all($expr, $element, $matches);
        }
        return $matches;
    }

    public static function setattr($element, $attr, $value)
    {
        $evalue = self::getattr($element, $attr);
        if ($evalue) {
            $element = str_ireplace("{$attr}={$evalue}", "", $element);
            $element = str_ireplace("{$attr}=\"{$evalue}\"", "", $element);
        }
        $pos = strpos($element, " ");
        $e_tag = substr($element, 1, $pos - 1);
        $element = str_ireplace("<" . $e_tag, "<" . $e_tag . (" " . $attr . "=\"{$value}\""), $element);
        return $element;
    }
}
