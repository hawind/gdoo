<?php namespace Gdoo\Workflow\Controllers;

use DB;
use Request;

use Gdoo\Workflow\Models\Workflow;

use Gdoo\Index\Controllers\DefaultController;

class StepController extends DefaultController
{
    public $permission = ['dialog'];

    // 步骤列表
    public function indexAction()
    {
        $work_id = Request::get('work_id');

        if (Request::method() == 'POST') {
            $rows = DB::table('work_step')
            ->where('work_id', $work_id)
            ->orderBy('id', 'asc')
            ->get();

            $data['total'] = sizeof($rows);
            $data['rows'] = $rows;
            exit(json_encode($data));
        }
        $work = DB::table('work')->where('id', $work_id)->first();

        return $this->display([
            'work_id' => $work_id,
            'work'    => $work,
        ]);
    }

    // 添加步骤和克隆步骤
    public function addAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            // 是克隆步骤
            if ($gets['id'] > 0) {
                $step = DB::table('work_step')->where('id', $gets['id'])->first();
                unset($step['id'], $step['join']);
            }

            $count = DB::table('work_step')->where('work_id', $gets['work_id'])->count();
            $step['work_id'] = $gets['work_id'];
            $step['number']  = $count + 1;
            $step['title'] = '新建步骤'.$step['number'];
            DB::table('work_step')->insert($step);
            return $this->json('流程节点添加成功', true);
        }
    }

    // 步骤条件设置
    public function editAction()
    {
        if (Request::method() == 'POST') {
            $post = Request::all();

            // 验证字段组合
            if ($post['check']) {
                $field_check = array();
                $post['check'] = array_filter($post['check']);
                foreach ($post['check'] as $k => $v) {
                    $field_check[] = $v.'='.$post['check_select'][$k];
                }
                $post['field_check'] = join(',', $field_check);
            }

            // 组合自动选人规则
            if ($post['select_user_type'] > 0) {
                $select_keys = array(
                    7 => 'select_field_user',
                    8 => 'select_process_user'
                );

                $select_key = $select_keys[$post['select_user_type']];
                if ($select_key) {
                    $post['select_user_sign'] = $post[$select_key];
                }
            }

            // checkbox 选项处理
            $post['last'] = empty($post['last']) ? 0 : 1;
            $post['deny'] = empty($post['deny']) ? 0 : 1;
            $post['print'] = empty($post['print']) ? 0 : 1;

            // 通知人类型
            $post['notification_type'] = empty($post['notification_type']) ? '' : json_encode($post['notification_type']);
            
            $post['notification_text'] = array_filter($post['notification_text']);
            $post['notification_text'] = empty($post['notification_text']) ? '' : json_encode($post['notification_text']);

            // 写字段
            $post['field'] = join(',', (array)$post['write']);
            // 保密字段
            $post['field_secret'] = join(',', (array)$post['secret']);
            // 宏字段
            $post['field_auto'] = join(',', (array)$post['micro']);

            // 条件转入组合
            if (is_array($post['conditions'])) {
                foreach ($post['conditions'] as $step_id => $condition) {
                    // 检查条件是否是空的
                    $data['condition'] = $condition == 'empty' ? '' : join("\n", (array)$condition);
                    DB::table('work_step')->where('id', $step_id)->update($data);
                }
            }

            unset(
                $post['select_process_user'],
                $post['select_field_user'],
                $post['check_select'],
                $post['conditions'],
                $post['check'],
                $post['write'],
                $post['secret'],
                $post['micro']
            );

            DB::table('work_step')->where('id', $post['id'])->update($post);

            return $this->json('流程节点设置保存成功', true);
        }

        $id = (int)Request::get('id');
        $tabs['selected'] = Request::get('tab');

        // 子表步骤
        $row = DB::table('work_step')->where('id', $id)->first();
        $row['joinArray'] = explode(',', $row['join']);

        // 当前工作全部步骤节点
        $rows = DB::table('work_step')->where('work_id', $row['work_id'])->get();
        $rows = array_by($rows);

        // 取得工作流主表信息
        $fields = Workflow::getFormData($row['work_id']);
        $_fields = [];
        foreach ($fields as $key => $value) {
            if ($value['class'] == 'listview') {
                $titles = explode('`', $value['lv_title']);
                foreach ($titles as $index => $title) {
                    if ($title) {
                        $_value = $value;
                        $_value['itemid'] = $_value['itemid'].'_'.$index;
                        $_value['title'] = $value['title'].'['.$title.']';
                        $_value['desc'] = $this->getFieldDescription($_value);
                        $_fields[$key.'_'.$index] = $_value;
                    }
                }
            } else {
                $value['desc'] = $this->getFieldDescription($value);
                $_fields[$key] = $value;
            }
        }
        $fields = $_fields;

        $field_write = explode(",", $row['field']);
        $field_secret = explode(",", $row['field_secret']);
        $field_check = explode(",", $row['field_check']);
        $field_auto = explode(",", $row['field_auto']);
        $field_select = array();
        if ($row['field_check']) {
            $checkarr = $checkarr2 = $check = array();
            foreach ($field_check as $k => $v) {
                if ($v) {
                    $part = explode("=", $v);
                    $checkarr[] = $part[0];
                    $checkarr2[] = $part[1];
                }
            }
        }

        // 取得字段名称的id号
        foreach ($fields as $k => $v) {
            if (in_array($v['title'], $field_write)) {
                $field_select['write'][] = $v['itemid'];
            }
            if (in_array($v['title'], $field_auto)) {
                $field_select['auto'][] = $v['itemid'];
            }
            if (is_array($checkarr) && in_array($v['title'], $checkarr)) {
                $field_select['check'][$v['itemid']] = array_shift($checkarr2);
            }
            if (in_array($v['title'], $field_secret)) {
                $field_select['secret'][] = $v['itemid'];
            }
            if ($v['class'] != "sign") {
                $item_name_all[] = $v['title'];
            }

        }
        if (strstr($row['field'], "[attach@]")) {
            $field_select['write'][] = "attach";
        }
        $field_select['write'] = join(',', (array)$field_select['write']);
        $field_select['auto'] = join(',', (array)$field_select['auto']);
        $field_select['secret'] = join(',', (array)$field_select['secret']);
        if (is_array($field_select['check'])) {
            $field_select['check'] = json_encode($field_select['check']);
        }

        $row['notification_type'] = json_decode($row['notification_type'], true);
        $row['notification_text'] = json_decode($row['notification_text'], true);

        return $this->render([
            'row'         => $row,
            'rows'        => $rows,
            'tabs'        => $tabs,
            'fields'      => $fields,
            'field_select'=> $field_select,
        ]);
    }

    public function getFieldDescription($v)
    {
        // 步骤字段类型匹配
        $desc = '';
        switch ($v['tag']) {
            case "input":
                if ($v['type'] == "text") {
                    if ($v['class'] == "auto") {
                        $desc .= "宏控件:";
                    }
                    $desc .= '单行文本';
                } elseif ($v['type'] == "checkbox") {
                    $desc = '复选按钮';
                } elseif ($v['class'] == "date") {
                    $desc = '日历控件';
                } elseif ($v['class'] == "calc") {
                    $desc = '计算控件';
                } elseif ($v['class'] == "user") {
                    $desc = '用户控件';
                }
                break;
            case "textarea":
                $desc = '多行文本';
                if ($v['rich'] == 1) {
                    $desc .= ":富文本";
                }
                break;
            case "select":
                if ($v['class'] == "auto") {
                    $desc .= "宏控件:";
                }
                $desc .= '下拉菜单';
                break;
            case "button":
                if ($v['class'] == "data") {
                    $desc = $lang['DataSelectControl'];
                } elseif ($v['class'] == "fetch") {
                    $desc = $lang['DataGetControl'];
                    break;
                }
                break;
            case "img":
                if ($v['class'] == "radio") {
                    $desc = '单选按钮';
                } elseif ($v['class'] == "sign") {
                    $desc = '签章控件';
                } elseif ($v['class'] == "listview") {
                    $desc = '列表控件';
                } elseif ($v['class'] == "progressbar") {
                    $desc = $lang['progressbar'];
                } elseif ($v['class'] == "imgupload") {
                    $desc = '图片上传控件';
                } elseif ($v['class'] == "qrcode") {
                    $desc = '二维码控件';
                }
                break;
            default:
                $desc = "";
        }
        return $desc;
    }

    // 查看步骤信息
    public function viewAction()
    {
        $gets = Request::all();

        $row = DB::table('work_step')->where('id', $gets['id'])->first();
        return $this->json($row, true);
    }

    // 表单计数
    public function itemAction()
    {
        $id = Request::get('id');
        $row = DB::table('work_form')->where('id', $id)->first();
        return $row['max_item'] + 1;
    }

    // 保存步骤
    public function saveAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $_join = [];
            foreach ($gets['join'] as $join) {
                $_join[$join['id']][] = $join['target'];
            }
            foreach ($gets['position'] as $position) {
                $position['join'] = join(',', (array)$_join[$position['id']]);
                DB::table('work_step')->where('id', $position['id'])->update($position);
            }
            return $this->json('流程节点添加成功', true);
        }
    }

    public function dialogAction()
    {
        $gets = Request::all();
        // 返回json
        if (Request::ajax()) {
            $rows = [];
            if ($gets['work_id']) {
                $rows = DB::table('work_step')->where('work_id', $gets['work_id'])->get(['id','title']);
            }
            return $this->json($rows);
        }
    }

    // 删除步骤
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            DB::table('work_step')->where('id', $id)->delete();
            return $this->json('工作流删除成功。', true);
        }
    }
}
