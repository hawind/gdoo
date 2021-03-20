<?php

namespace Gdoo\Model;

use XMLReader;

use DB;
use Request;
use Validator;
use URL;
use View;
use Auth;

use App\Support\AES;
use App\Support\Hook;
use App\Support\License;
use Gdoo\Model\Models\Bill;
use Gdoo\Model\Models\Model;
use Gdoo\Model\Models\Field;
use Gdoo\Model\Models\Permission;
use Gdoo\Model\Models\Template;
use Gdoo\Model\Models\Step;
use Gdoo\Model\Models\StepLog;
use Gdoo\Index\Models\Attachment;
use Gdoo\Index\Models\Access;
use Gdoo\Index\Services\AttachmentService;
use Gdoo\User\Models\User;
use Gdoo\User\Models\UserAsset;

use Gdoo\Model\Models\Run;
use Gdoo\Model\Models\RunStep;
use Gdoo\Model\Models\RunLog;

use Gdoo\Model\Services\FieldService;
use Gdoo\Model\Services\ModuleService;
use Gdoo\Index\Services\NotificationService;
use Gdoo\Model\Services\StepService;
use Gdoo\User\Services\UserAssetService;
use Gdoo\User\Services\UserService;
use Illuminate\Support\Arr;

class Form
{
    /**
     * 关联字段处理
     */
    public static function fieldRelated($table, &$field, &$join, &$select, &$links)
    {
        if ($field['data_type']) {
            $data_type = $field['data_type'];
            $data_field = $field['data_field'];
            $data_link = $field['data_link'];
            $_table = $data_link.'_'.$data_type;

            if ($field['type']) {
                $join[$_table] = [$data_type.' as '.$_table, $_table.'.id', '=', $table.'.'.$data_link, $table, 1];
            }

            $links[$data_link][$data_link] = 'id';

            if ($field['field'] == $data_link) {
                $column = $field['field'].'_'.$data_field;
            } else {
                $column = $field['field'];
            }

            $field_count = mb_substr_count($data_field, ':');
            if ($field_count > 0) {
                $var1 = explode(':', $data_field);
                list($_v1, $_v2) = explode('.', $var1[0]);
                list($_t1, $_t2) = explode('.', $var1[1]);

                if ($field['type']) {
                    $_table = $data_link.'_'.$_t1;
                } else {
                    $_table = $field['field'].'_'.$_t1;
                }
                
                $join[$_table] = [$_t1.' as '.$_table, $_table.'.id', '=', $data_link.'_'.$data_type.'.'.$_v2, $data_link.'_'.$data_type, 1];

                $index = $_table.'.'.$_t2;
                if ($field['field'] == $data_link) {
                    $column = $field['field'].'_'.$_v1;
                    // 这里本地字段和右表字段一样时，直接取右表名称
                    $links[$data_link][$column] = $_v1;
                } else {
                    // $_v2 右表关联字段，$_t2 右表映射字段
                    if ($field['type']) {
                        $links[$data_link][$column] = $_v2;
                        // 右表 id_name
                        $links[$data_link][$column.'_'.$_t2] = $_v2.'_'.$_t2;

                        $field['dest_column'] = $column.'_'.$_t2;
                        $column = $column.'_'.$_t2;
                    } else {
                        $links[$data_link][$column] = $_v2.'_'.$_t2;
                    }
                }
            } else {
                if ($field['type']) {
                    if ($field['field'] == $data_link) {
                        $index = $data_link.'_'.$data_type.'.'.$data_field;
                    } else {
                        $index = $table.'.'.$field['field'];
                    }
                } else {
                    $index = $data_link.'_'.$data_type.'.'.$data_field;
                }
                $links[$data_link][$column] = $data_field;
            }

            $field['_column'] = $column;
            $select[] = $index. ' as '.$column;
        }
    }

    public static function make($options)
    {
        $assets = UserAssetService::getNowRoleAssets();

        // 权限查询类型
        $code = $options['code'];
        $bill = Bill::where('code', $code)->first();

        // 表数据
        $flow = DB::table('model')
        ->where('id', $bill['model_id'])
        ->first();

        // 主表字段
        $fields = DB::table('model_field')
        ->where('model_id', $bill['model_id'])
        ->orderBy('sort', 'asc')
        ->get()->keyBy('field');

        $table = $flow['table'];

        // 查询主表数据
        $join = $select = $links = [];
        $select[] = $table.'.*';
        foreach($fields as $field) {
            static::fieldRelated($table, $field, $join, $select, $links);
        }
        $join = Grid::sortJoin($join);

        $q = DB::table($table)
        ->where($table.'.id', (int)$options['id']);
        foreach($join as $j) {
            $q->leftJoin($j[0], $j[1], $j[2], $j[3]);
        }
        $row = $q->select($select)->first();

        $auth = auth()->user();

        $action = $options['action'];
        if ($action == 'show') {
            $tpl = 'show';
            $type_sql = '(' . join(' or ', [db_instr('type', $tpl)]) . ')';
        } else {
            $tpl = $row['id'] > 0 ? 'edit' : 'create';
            $type_sql = '(' . join(' or ', [db_instr('type', $tpl)]) . ')';
        }

        if ($action == 'print') {
            $type_sql = '(' . join(' or ', [db_instr('type', 'print')]) . ')';
        }

        $key = AES::encrypt($bill['id'].'.'.(int)$row['id'], config('app.key'));

        $run_id = 0;
        $step_id = 0;
        $run_log_id = 0;
        $run_step_id = 0;
        $recall_log_id = 0;
        $recall_btn = $audit_btn = $abort_btn = $read_btn = false;

        if ($bill['audit_type'] == 1) {

            $run = DB::table('model_run')
            ->where('bill_id', $bill['id'])
            ->where('data_id', $row['id'])
            ->first();

            // 流程是新建的
            if (empty($run)) {
                $step = DB::table('model_step')
                ->where('bill_id', $bill['id'])
                ->where('type', 'start')
                ->first();
                $step_id = $step['id'];
            } else {
                $step = DB::table('model_run_log')
                ->leftJoin('model_run_step', 'model_run_step.id', '=', 'model_run_log.run_step_id')
                ->where('model_run_log.run_id', $run['id'])
                ->where('model_run_log.user_id', $auth['id'])
                ->where('model_run_log.status', 0)
                ->orderBy('model_run_log.id', 'asc')
                ->first([
                    'model_run_log.*',
                    'model_run_step.type as step_type', 
                    'model_run_step.step_id',
                    'model_run_step.permission_id'
                ]);

                $run_id = $run['id'];
                $step_id = $step['step_id'];
                $run_step_id = $step['run_step_id'];
                $run_log_id = $step['id'];
                $option = $step['option'];

                // 判断是有审核权限
                $audit_status = $step['user_id'] == auth()->id();
                if ($option == 1) {
                    $audit_btn = $audit_status;
                } else {
                    $read_btn = $audit_status;
                }
            }

            if ($row['status'] == '0') {
                $audit_btn = 1;
            }

            if ($row['status'] == '1') {
                $abort_btn = 1;
            }
            
            // 获取表单操作权限
            $_permission = DB::table('model_permission')
            ->permission('receive_id')
            ->where('id', $step['permission_id'])
            ->first();

            // 获取最后已转交节点
            $passed = DB::table('model_run_log')
            ->where('run_id', $run['id'])
            ->where('user_id', $auth['id'])
            ->where('status', '>', 0)
            ->orderBy('id', 'desc')
            ->first();

            if ($passed) {
                $recall_log_id = $passed['id'];
                $nodes = DB::table('model_run_log')
                ->where('parent_id', $passed['id'])
                ->get();

                $node_count = $nodes->count();
                $todo_count = $nodes->where('status', 0)->count();
                // 全部未办理才能撤回
                if ($node_count > 0) {
                    if ($node_count == $todo_count) {
                        $recall_btn = true;
                    }
                }
            }

            /*
            if ($audit_btn == true && $run_id > 0) {
                // 更新待办接收时间
                if ($step['received_id'] == 0) {
                    DB::table('model_run_log')->where('id', $step['id'])->update([
                        'updated_id' => 0,
                        'updated_by' => '',
                        'updated_at' => 0,
                        'received_id' => $auth['id'],
                        'received_by' => $auth['name'],
                        'received_at' => time(),
                    ]);
                }
            }
            */

        } else {
            $_permission = DB::table('model_permission')
            ->permission('receive_id')
            ->whereRaw($type_sql)
            ->where('bill_id', $bill['id'])
            ->first();
        }

        $permission = json_decode($_permission['data'], true);

        $model = DB::table('model_template')
        ->permission('receive_id', null, false, false)
        ->whereRaw($type_sql)
        ->where('bill_id', $bill['id']);
        if ($options['template_id'] > 0) {
            $model->where('id', $options['template_id']);
        }
        $template = $model->first();

        if (empty($template)) {
            $model = DB::table('model_template')
            ->where('receive_id', 'all')
            ->whereRaw($type_sql)
            ->where('bill_id', $bill['id']);
            if ($options['template_id'] > 0) {
                $model->where('id', $options['template_id']);
            }
            $template = $model->first();
        }

        $prints_btn = '';
        if (isset($assets['print']) && $row['id'] > 0) {
            // 获取打印模板
            $prints = DB::table('model_template')
            ->permission('receive_id')
            ->where('type', 'print')
            ->where('bill_id', $bill['id'])
            ->orderBy('sort', 'asc')
            ->get();

            if ($prints) {
                $prints_btn .= '<div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
                    <span class="fa fa-print"></span> 打印
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">';
                foreach($prints as $print) {
                    $prints_btn .= '<li><a href="'.url('print', ['id' => $row['id'], 'template_id' => $print['id']]).'" target="_blank"> '.$print['name'].'</a></li>';
                }
                $prints_btn .= '</ul></div> ';
            }
        }

        $views = json_decode($template['tpl'], true);

        $js = '<script type="text/javascript">formGridList.' . $flow['table'] . ' = [];';
        $js .= '$(function() {';

        $html = '';
        $sublist_status = false;
        $tabs = [];

        $_master_data = Hook::fire($table . '.onBeforeForm', ['options' => $options, 'permission' => $permission, 'model' => $flow, 'fields' => $fields, 'views' => $views, 'row' => $row]);
        extract($_master_data);
        
        $tpls = [];
        $sublist = [];
        $print_row = [];

        foreach ($views as $group) {

            $_replace = [];
            $tpl = '';
            $_sub_view = [];
            $col = 0;

            if ($group['border'] == '') {
                $group['border'] = 1;
            } 

            foreach ($group['fields'] as $view) {

                if ($view['role_id']) {
                    $role_ids = explode(',', $view['role_id']);
                    if (in_array($auth->role_id, $role_ids)) {
                        continue;
                    }
                }

                // 显示流程记录
                if ($view['field'] == '{flowlog}') {
                    if ($action == 'print') {
                        $tpl = StepService::getFlowLogTpl($run_id, $row, $flow, $tpl);
                    } else {
                        $flowlog = StepService::getFlowLogTpl($run_id, $row, $flow, $flowlog);
                    }
                    continue;
                }

                // 处理流程意见字段
                list($view_type, $view_step_id) = explode('.', $view['field']);
                if ($view_type == 'flow_step') {
                    $view_step = StepService::getFlowField($run_id, $row, $flow, $steps, $step, $view, $view_step_id, $action, $permission);
                    if ($view_step) {
                        $view['title'] = $steps[$view_step_id]['name'].'意见';
                        $_replace['{' . $view['field'] . '}'] = $view_step;
                    } else {
                        continue;
                    }
                }

                $field = $fields[$view['field']];
                // 是多行子表
                if ($view['type'] == 0) {
                    if ($view['hidden']) {
                        $tpl .= '<div style="display:none;">{' . $view['field'] . '}</div>';
                    } else {
                        if ($col == 0) {
                            if ($action == 'print') {
                                $tpl .= '<div class="row '.($group['border'] == 0 ? 'no-border' : '').'">';
                            } else {
                                $tpl .= $action == 'show' ? '<div class="row">' : '<div class="form-group">';
                            }
                        }

                        // 补全错位
                        if ($col > 0 && $view['col'] == 12) {
                            $line = 12 - $col;
                            if ($action == 'print') {
                                $tpl .= '</div><div class="row">';
                            } else {
                                $tpl .= '<div class="col-xs-8 col-sm-' . ($line) . ' control-text"></div>';
                                $tpl .= ($action == 'show' || $action == 'print') ? '</div><div class="row">' : '</div><div class="form-group">';
                            }
                            $col = 0;
                        }

                        if ($col + $view['col'] > 12) {
                            $line = $col + $view['col'] - 12;
                            if ($action == 'print') {
                                $tpl .= '</div><div class="row '.($group['border'] == 0 ? 'no-border' : '').'">';
                            } else {
                                $tpl .= '<div class="col-xs-8 col-sm-' . ($line) . ' control-text"></div>';
                                $tpl .= ($action == 'show' || $action == 'print') ? '</div><div class="row">' : '</div><div class="form-group">';
                            }
                            $col = 0;
                        }
                        
                        $col += $view['col'];

                        $field['name'] = empty($view['title']) ? $field['name'] : $view['title'];

                        $label = '{' . $field['name'] . '}';
                        if ($view['hide_title'] == 1) {
                            $label = '';
                        }

                        if ($view['type'] == 1) {
                            $right_col = $view['col'];
                        } else {
                            $right_col = $view['col'] - 1;
                        }

                        if ($action == 'print') {
                            $right_col += 1;
                        } else {
                            if ($label) {
                                $tpl .= '<div class="col-xs-4 col-sm-1 control-label">' . $label . '</div>';
                            } else {
                                $right_col += 1;
                            }
                        }

                        if ($action == 'print') {
                            if ($view['custom'] == 1) {
                                $tpl .= '<div id="'.$view['field'].'" class="col-sm-' . $right_col . ' control-text">' . ($label == '' ? '' : $label . ': ') . $view['content'] . '</div>';
                            } else {
                                $tpl .= '<div id="'.$view['field'].'" class="col-sm-' . $right_col . ' control-text">' . ($label == '' ? '' : $label . ': ').'{' . $view['field'] . '}</div>';
                            }
                        } else {
                            if ($view['custom'] == 1) {
                                $tpl .= '<div id="'.$view['field'].'" class="col-xs-8 col-sm-' . $right_col . ' control-text" '.($action == 'show' ? '' : 'style="padding-top:10px;"' ).'>' . $view['content'] . '</div>';
                            } else {
                                $tpl .= '<div id="'.$view['field'].'" class="col-xs-8 col-sm-' . $right_col . ' control-text">{' . $view['field'] . '}</div>';
                            }
                        }

                        if ($col == 12) {
                            $col = 0;
                            $tpl .= '</div>';
                        }
                    }

                    $field['model'] = $flow;

                    $attribute = [];

                    $permission_table = $permission[$flow['table']];
                    $p = $permission_table[$field['field']];
                    $field['is_print'] = $action == 'print';
                    $field['is_write'] = $p['w'] == 1 ? 1 : 0;
                    $field['is_read'] = $p['w'] == 1 ? 0 : 1;
                    $field['is_auto'] = $p['m'] == 1 ? 1 : 0;
                    $field['is_hide'] = $p['s'] == 1 ? 1 : $field['is_hide'];

                    if ($action == 'show' || $action == 'print') {
                        $field['is_show'] = 1;
                        $field['is_write'] = 0;
                    }

                    $validate = (array) $p['v'];

                    if ($action == 'print') {
                    } else {
                        $required = '';
                        if (in_array('required', $validate)) {
                            $required = '<span class="red">*</span> ';
                            if ($field['is_write']) {
                                $attribute['required'] = 'required';
                                if ($field['is_auto'] == 0) {
                                    $attribute['class'][] = 'input-required';
                                } else {
                                    $attribute['class'][] = 'input-auto';
                                }
                            }
                        }
                    }

                    $field['verify'] = $validate;
                    $field['attribute'] = $attribute;
                    $field['table'] = $table;

                    $tooltip = $field['tips'] ? ' <a class="hinted" href="javascript:;" title="' . $field['tips'] . '"><i class="fa fa-question-circle"></i></a>' : '';

                    if ($action == 'show' || $action == 'print') {
                        $tooltip = '';
                        $required = '';
                    }

                    $_replace['{' . $field['name'] . '}'] = $required . $field['name'] . $tooltip;
                    
                    $data_type = $field['data_type'];
                    $data_field = $field['data_field'];
                    $data_link = $field['data_link'];
                    if ($data_type) {
                        $related = [];
                        if (strpos($data_field, ':')) {
                            list($var1, $var2) = explode(':', $data_field);
                            list($_v1, $_v2) = explode('.', $var1);
                            list($_t1, $_t2) = explode('.', $var2);
                            if ($field['type']) {
                                $related['table'] = $table;
                                $related['field'] = $field['field'];
                            } else {
                                $related['table'] = $_t1;
                                $related['field'] = $_v2;
                            }
                        } else {
                            if ($field['type']) {
                                $related['table'] = $table;
                                $related['field'] = $field['field'];
                            } else {
                                $related['table'] = $data_type;
                                $related['field'] = $data_field;
                            }
                        }
                        $field['related'] = $related;
                    }

                    $value = $row[$field['field']];

                    if ($field['form_type']) {
                        $field['view'] = $view;
                        $field['bill'] = $bill->toArray();
                        $vv = FieldService::{'content_' . $field['form_type']}($field, $value, $row, $permission_table);
                        $print_row[$field['field']] = $vv;
                        $_replace['{'.$field['field'].'}'] = $vv;
                    }
                } else {
                    $sublist[] = $view;
                    $_sub_view = $view;
                }
            }

            $_field_data = Hook::fire($table.'.onFormFieldFilter', ['table' => $table, 'tpl' => $tpl, 'master' => $master, 'field' => $field, '_replace' => $_replace]);
            extract($_field_data);
            
            $tpls[] = ['title' => $group['title'], 'tpl' => strtr($tpl, $_replace)];

            // 插入子表位置
            if ($action == 'print') {
                if ($_sub_view) {
                    $tpls[] = ['title' => '', 'table' => 1, 'tpl' => '{'.$_sub_view['field'].'}'];
                }
            } else {
                if ($sublist && $sublist_status == false) {
                    $tpls[] = ['title' => '', 'tpl' => '{__sublist__}'];
                    $sublist_status = true;
                }
            }
        }

        $tabs = static::sublist(['select' => $options['select'], 'sublist' => $sublist, 'permission' => $permission, 'action' => $action, 'table' => $table, 'row' => $row, 'bill' => $bill]);

        if ($flowlog) {
            $tabs[] = [
                'tpl' => '<div class="b-a">'.$flowlog.'</div>',
                'id' => 'flowlog',
                'tool' => '',
                'name' => '审核记录',
            ];
        }

        if ($action == 'print') {

            $prints = [[
                'name' => 'master',
                'fields' => $fields->toArray(),
                'data' => [$print_row],
            ]];

            $_prints['master'] = [$print_row];
            $_prints['flowlog'] = $flowlog;

            foreach($tpls as $index => $tpl) {
                foreach($tabs as $tab) {
                    if ('{'.$tab['id'].'}' == $tpl['tpl']) {
                        $tpls[$index]['table'] = true;
                        $tpls[$index]['tpl'] = $tab['print'];
                        $prints[] = [
                            'name' => $tab['id'],
                            'fields' => $tab['fields'],
                            'data' => $tab['rows'],
                        ];
                        $_prints[$tab['id']] = $tab['rows'];
                    }
                }
            }

        } else {
            if ($tabs) {
                $_tabs = '
                <div class="panel-heading tabs-box">
                    <ul class="nav nav-tabs">';
                        foreach($tabs as $tab_index => $tab):
                            $js .= $tab['js'];
                            $active = $tab_index == 0 ? 'active' : '';
                            $_tabs .= '<li class="'.$active.'">
                            <a data-toggle="tab" class="text-sm" href="#'.$tab['id'].'">'.$tab['name'].'</a>
                        </li>';
                        endforeach;
                        $_tabs .= '</ul>
                </div>
                <div id="tab-content-'.$table.'" class="tab-content">';
                    foreach($tabs as $tab_index => $tab):
                        $active = $tab_index == 0 ? ' in active ' : '';

                        $tool = $tab['tool'];

                        if ($tool) {
                            $tool .= ' <span id="'.$tab['id'].'_tool"></span> ';
                        }

                        if (isset($row['id'])) {
                            $tool .= ' <a href="javascript:;" onclick="flow.exportDataAsExcel(\''.$tab['id'].'\');" class="btn btn-sm btn-default">导出</a> ';
                        }
 
                        // 操作按钮
                        if (isset($assets['closeRow'])) {
                            $tool .= ' <a href="javascript:;" onclick="flow.closeRow(\''.$tab['id'].'\');" class="btn btn-sm btn-default">关闭行(恢复)</a> ';
                        }
                        if (isset($assets['closeAllRow'])) {
                            $tool .= ' <a href="javascript:;" onclick="flow.closeAllRow(\''.$tab['id'].'\');" class="btn btn-sm btn-default">关闭所有行(恢复)</a> ';
                        }

                        $tool = $tool == '' ? $tool : '<div class="system_btn m-b-sm">'.$tool.'</div>';

                        // 审核记录不显示工具栏
                        if ($tab['id'] == 'flowlog') {
                            $tool = '';
                        }

                        $_tabs .= '<div class="tab-pane fade '.$active.'" id="'.$tab['id'].'">
                        <div class="wrapper-sm">
                            <div class="grid-tool" id="'.$table.'-tool">
                                '.$tool.'
                            </div>
                            '.$tab['tpl'].'
                        </div>
                    </div>';
                    endforeach;
                $_tabs .= '</div>';
            }   
        }

        // 重新构建
        $_tpls = [];

        foreach($tpls as $index => $tpl) {
            if ($tpl['tpl']) {
                if ($tpl['tpl'] == '{__sublist__}') {
                    $tpl['tpl'] = $_tabs;
                }
                $_tpls[] = $tpl;
            }
        }
        $tpls = $_tpls;

        $_master_data = Hook::fire($table . '.onAfterForm', ['options' => $options, 'tpls' => $tpls, 'model' => $flow, 'row' => $row]);
        extract($_master_data);

        $html = '';
        $tpls_count = count($tpls) - 1;
        $a = 0;
        foreach($tpls as $index => $tpl) {

            if(empty($tpl['tpl'])) {
                continue;
            }

            $end = ($tpls_count == $index) ? ' m-b-none' : '';
            $heading = $tpl['title'] == '' ? '' : '<div class="panel-heading"><i class="fa fa-clone"></i> '.$tpl['title'].'</div>';

            if ($action == 'print') {
                if ($tpl['table']) {
                    $html .= '<div id="print_'.$index.'" class="print">'.$tpl['tpl'].'</div>';
                    $a = 1;
                } else {
                    if ($a == 1) {
                        $html .= '<div id="print_'.$index.'" class="print">'.$tpl['tpl'].'</div>';
                        $a = 0;
                    } else {
                        $html .= '<div id="print_'.$index.'" class="print">'.$tpl['tpl'].'</div>';
                    }
                }
                
            } else {
                $html .= '<div class="panel no-border'.$end.'">'.$heading.''.$tpl['tpl'].'</div>';
            }

        }

        if ($action == 'print') {

        } else {

        if ($row['id']) {
            $html .= '<input type="hidden" name="' . $table . '[id]" id="' . $table . '_id" value="' . $row['id'] . '">';
        }

        // 子表关联逻辑
        foreach($tabs as $tab) {
            $html .= $tab['buttons'];
        }
        $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
        $html .= '<input type="hidden" name="master[key]" id="master_key" value="'.$key.'">';
        $html .= '<input type="hidden" name="master[uri]" id="master_uri" value="' . Request::module() . '/' . Request::controller() . '">';
        $html .= '<input type="hidden" name="master[permission_id]" value="' . $_permission['id'] . '">';
        $html .= '<input type="hidden" name="master[model_id]" value="' . $flow['id'] . '">';
        $html .= '<input type="hidden" name="master[bill_id]" value="' . $bill['id'] . '">';
        $html .= '<input type="hidden" name="master[created_id]" value="' . $row['created_id'] . '">';
        $html .= '<input type="hidden" name="master[id]" value="' . $row['id'] . '">';

        if (!is_weixin()) {

            $page = static::getPage([
                'table' => $table, 
                'id' => $row['id'], 
                'region' => $options['region'],
                'authorise' => $options['authorise'],
            ]);

            $btn = '<div class="btn-group hidden-xs"><a class="btn btn-sm btn-default" href="'.url('show', ['id' => $page['start']]).'">首张</a> ';
            if ($row['id'] > 0) {
                if ($page['prev'] > 0) {
                    $btn .= '<a class="btn btn-sm btn-default" href="'.url('show', ['id' => $page['prev']]).'">上一张</a> ';
                } else {
                    $btn .= '<a class="btn btn-sm btn-default disabled" href="javascript:;">上一张</a> ';
                }
                if ($page['next'] > 0) {
                    $btn .= '<a class="btn btn-sm btn-default" href="'.url('show', ['id' => $page['next']]).'">下一张</a> ';
                } else {
                    $btn .= '<a class="btn btn-sm btn-default disabled" href="javascript:;">下一张</a> ';
                }
            }
            $btn .= '<a class="btn btn-sm btn-default" href="'.url('show', ['id' => $page['end']]).'">末张</a> ';
            $btn .= ' <a class="btn btn-sm btn-default" href="javascript:location.reload();">刷新</a> ';
            $btn .= '</div> ';
        }

        // 流程审核
        if ($bill['audit_type'] == 1) {
            $html .= '<input type="hidden" name="master[run_id]" id="master_run_id" value="' . $run_id . '">';
            $html .= '<input type="hidden" name="master[step_id]" id="master_step_id" value="' . $step_id . '">';
            $html .= '<input type="hidden" name="master[run_step_id]" id="master_run_step_id" value="' . $run_step_id . '">';
            $html .= '<input type="hidden" name="master[run_log_id]" id="master_run_log_id" value="' . $run_log_id . '">';
            $html .= '<input type="hidden" name="master[recall_log_id]" id="master_recall_log_id" value="' . $recall_log_id . '">';
            
            if (isset($assets['create'])) {
                $btn .= ' <a class="btn btn-sm btn-default" href="'.url('create').'">添加</a> ';
            }

            // 流程单据删除
            if (isset($assets['delete']) && $row['id'] > 0) {
                // 已经审核的单据不能删除
                if ($row['status'] <= 0) {
                    $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.remove(\''.url('delete', ['id' => $row['id']]).'\');"><i class="fa fa-times"></i> 删除</a> ';
                } else {
                    $btn .= '<a class="btn btn-sm btn-default disabled"><i class="fa fa-times"></i> 删除</a> ';
                }
            }

            $op_btn = '';

            if (isset($assets['abort']) && $abort_btn == true) {
                $op_btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.abort(\''.$table.'\');"><i class="fa fa-ban"></i> 弃审</a> ';
            }

            if (isset($assets['recall']) && $recall_btn == true) {
                $op_btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.recall(\''.$table.'\');"><i class="fa fa-reply"></i> 撤回</a> ';
            }

            if ($action == 'show') {

                if (isset($assets['audit']) && $audit_btn == true) {

                    $btn .= '<a class="btn btn-sm btn-default" href="'.url('audit', ['id' => $row['id']]).'"><i class="fa fa-pencil"></i> 修改</a> ';
                    
                    if (intval($bill['form_type']) == 0) {
                        if (intval($row['status']) == 0) {
                            $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.audit(\''.$table.'\');"><i class="fa fa-check"></i> 提交</a> ';
                        } else {
                            $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.audit(\''.$table.'\');"><i class="fa fa-check"></i> 审核</a> ';
                        }
                    }
                }
                
                if ($read_btn == true) {
                    $op_btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.read(\''.$table.'\', \''.$run_log_id.'\');"><i class="fa fa-eye"></i> 已阅</a> ';
                }

            } else {

                $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.draft(\''.$table.'\');"><i class="icon icon-coffee-cup"></i> 保存</a> ';

                if (isset($assets['audit']) && $audit_btn == true && intval($bill['form_type']) == 1) {
                    
                    if (intval($row['status']) == 0) {
                        $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.audit(\''.$table.'\');"><i class="fa fa-check"></i> 提交</a> ';
                    } else {
                        $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.audit(\''.$table.'\');"><i class="fa fa-check"></i> 审核</a> ';
                    }
                }
            }

            if ($op_btn) {
                $btn .= '<div class="btn-group">'.$op_btn.'</div> ';
            }

            if ($run_id > 0) {
                $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.auditLog(\''.$key.'\');"><i class="fa fa-file-text-o"></i> 审核记录</a> ';
            }

            if ($run_id > 0 && $auth['id'] == 1) {

                $btn .= '<div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <span class="fa fa-wrench"></span> 工具
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="javascript:;" onclick="flow.revise(\''.$key.'\');">流程修正</a></li>
                    <li><a href="javascript:;" onclick="flow.reset(\''.$table.'\');">流程重置</a></li>
                </ul>
                </div>';
            }

        // 普通审核
        } else if($bill['audit_type'] == 3) {

            if ($action == 'show') {
                
                if (isset($assets['create'])) {
                    $btn .= ' <a class="btn btn-sm btn-default" href="'.url('create').'">添加</a> ';
                }

                // 审核单据删除
                if (isset($assets['delete']) && $row['id'] > 0) {
                    // 已经审核的单据不能删除
                    if ($row['status'] == 1) {
                        $btn .= '<a class="btn btn-sm btn-default disabled"><i class="fa fa-times"></i> 删除</a> ';
                    } else {
                        $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.remove(\''.url('delete', ['id' => $row['id']]).'\');"><i class="fa fa-times"></i> 删除</a> ';
                    }
                }

                $btn .= '<div class="btn-group">';

                if ($row['status'] == 1) {
                    if (isset($assets['abort'])) {
                        $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.abort2(\''.$table.'\');"><i class="fa fa-ban"></i> 弃审</a> ';
                    }
                } else {
                    if (isset($assets['audit'])) {
                        $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.audit2(\''.$table.'\');"><i class="fa fa-check"></i> 审核</a> ';
                    }
                    if (isset($assets['edit'])) {
                        $btn .= '<a class="btn btn-sm btn-default" href="'.url('edit', ['id' => $row['id']]).'"><i class="fa fa-pencil"></i> 编辑</a> ';
                    }
                }

                $btn .= '</div> ';

            } else {
                $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.store(\''.$table.'\');"><i class="fa fa-check"></i> 保存</a> ';
            }

        } else {

            if ($action == 'show') {

                if (isset($assets['create'])) {
                    $btn .= ' <a class="btn btn-sm btn-default" href="'.url('create').'">添加</a> ';
                }

                // 普通单据删除
                if (isset($assets['delete']) && $row['id'] > 0) {
                    $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.remove(\''.url('delete', ['id' => $row['id']]).'\');"><i class="fa fa-times"></i> 删除</a> ';
                }

                $btn .= '<div class="btn-group">';
                if (isset($assets['edit'])) {
                    $btn .= '<a class="btn btn-sm btn-default" href="'.url('edit', ['id' => $row['id']]).'"><i class="fa fa-pencil"></i> 修改</a> ';
                }
                $btn .= '</div> ';
            } else {
                $btn .= '<a class="btn btn-sm btn-default" href="javascript:;" onclick="flow.store(\''.$table.'\');"><i class="fa fa-check"></i> 保存</a> ';
            }

        }

        if (!is_weixin()) {
            $btn .= ' <a class="btn btn-sm btn-default" data-toggle="closetab" data-id="'.Request::module().'_'.Request::controller().'_show"><i class="fa fa-sign-out"></i> 退出</a> ';
        }

        if (isset($assets['print']) && $row['id'] > 0 && $prints_btn) {
            $btn .= $prints_btn;
        }

        if (!is_weixin()) {
            $joint = $options['joint'];
            if ($joint) {
                $btn .= '<div class="btn-group">
                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    联查 <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu text-xs" role="menu">';
                foreach($joint as $v) {
                    $btn .= '<li><a data-toggle="joint" data-action="'.$v['action'].'" data-id="'.$row[$v['field']].'" href="javascript:;">'.$v['name'].'</a></li>';
                }
                $btn .= '</ul></div>';
            }
        }

        $js .= '
        $.each(select2List, function(k, v) {
            select2List[k].el = $("#" + k).select2Field(v.options);
        });';

        $js .= '});
        flow.bill_url = "' . Request::module() . '/' . Request::controller() . '";</script>';
        $html .= $js;

        }

        View::share([
            'model_view' => 1,
        ]);

        return [
            'table' => $table,
            'model_id' => $flow['id'],
            'run_id' => $run_id,
            'row' => $row, 
            'action' => $action, 
            'actions' => $permission['actions'],
            'permission' => $permission,
            'btn' => $btn, 
            'key' => $key, 
            'tpl' => $html, 
            'tabs' => $tabs, 
            'access' => $assets,
            'template' => $template,
            'prints' => $prints,
            'print_data' => $_prints,
            'print_type' => $template['print_type'],
            'width' => $template['width'],
            'bill_code' => $options['code'],
        ];
    }

    /**
     * 多行子表构建
     */
    public static function sublist($options) 
    {
        $sublist = $options['sublist'];
        $permission = $options['permission'];
        $action = $options['action'];
        $table = $options['table'];
        $row = $options['row'];
        $bill = $options['bill'];
        $auth = auth()->user();

        $tabs = [];
        $buttons = '';

        foreach ($sublist as $_view) {

            $model = DB::table('model')->where('table', $_view['field'])->first();

            $fields = Field::where('model_id', $model['id'])
            ->orderBy('sort', 'asc')
            ->get()->toArray();
            $fields = array_by($fields, 'field');

            $columns = [
                ['field' => "id", 'hide' => true],
                ['field' => $model['relation'], 'hide' => true],
                ['field' => 'bill_id', 'hide' => true],
                ['field' => 'bill_data_id', 'hide' => true],
            ];

            $permission_table = $permission[$model['table']];
            $permission_option = $permission_table['@option'];

            $tool = '';

            $tool .= '<span style="display:none;"><div id="'.$_view['field'].'_quick_filter_text" class="wrapper-xs"><input placeholder="请输入过滤关键字" class="form-control input-sm"></div></span><a href="javascript:;" onclick="flow.quickFilter(\''.$_view['field'].'\');" class="btn btn-sm btn-default"> <i class="fa fa-filter"></i/> 过滤 </a> ';

            if ($action == 'show') {
                $permission_option['w'] = false;
            } else {

                if ($permission_option['w']) {
                    $tool .= ' <a href="javascript:;" onclick="flow.createRow(\''.$_view['field'].'\');" class="btn btn-sm btn-default">新增</a> ';
                }
    
                if ($permission_option['d']) {
                    $tool .= ' <a href="javascript:;" onclick="flow.deleteRow(\''.$_view['field'].'\');" class="btn btn-sm btn-default">删除</a> ';
                }

                if ($permission_option['w']) {
                    $columns[] = [
                        'suppressSizeToFit' => true, 
                        'headerName' => '', 
                        'cellRenderer' => 'optionCellRenderer', 
                        'width' => 60, 
                        'sortable' => false, 
                        'cellClass' => 'text-center',
                        'suppressNavigable' => true,
                    ];
                }

            }

            $columns[] = [
                'suppressSizeToFit' => true, 
                'headerName' => '序号', 
                'type' => 'sn', 
                'width' => 60, 
                'sortable' => false, 
                'cellClass' => 'text-center',
                'suppressNavigable' => true,
            ];

            // 查询子表数据
            $q = DB::table($model['table'])->where($model['table'].'.'.$model['relation'], $row['id']);

            $views = $_view['fields'];

            $_data = Hook::fire($model['table'] . '.onBeforeForm', ['q' => $q, 'model' => $model, 'fields' => $fields, 'views' => $views]);
            extract($_data);

            $join = [];
            $select = [$model['table'].'.id', $model['table'].'.'.$model['relation']];
            $links = [];

            $buttons .= '<input type="hidden" name="models[' . $model['table'] . '][type]" value="' . $model['type'] . '">';
            $buttons .= '<input type="hidden" name="models[' . $model['table'] . '][relation]" value="' . $model['relation'] . '">';    

            $tabContent .= '<table class="table"><tbody>';
            $tabContent .= '<tr><td align="center">序号</td>';

            $__views = [];

            foreach ($views as $view) {

                if ($view['role_id']) {
                    $role_ids = explode(',', $view['role_id']);
                    if (in_array($auth->role_id, $role_ids)) {
                        if ($action == 'print') {
                            continue;
                        } else {
                            $view['hidden'] = true;
                        }
                    }
                }

                $field = $fields[$view['field']];
                $field['raw_field'] = $field['field'];

                if ($field['type']) {
                    $select[] = $model['table'].'.'.$field['field'];
                }

                static::fieldRelated($model['table'], $field, $join, $select, $links);

                $column = [];

                if ($field['data_format']) {
                    // 数据类型格式化
                    switch ($field['data_format']) {
                        case 'number':
                        case 'money':
                            list($_, $len) = explode(',', $field['length']);
                            $len = $len > 0 ? $len : 2;
                            $column['type'] = 'number';
                            $column['numberOptions'] = [
                                'separator' => '.',
                                'thousands' => ',',
                                'places' => (int)$len,
                                'default' => number_format(0, $len),
                            ];
                            break;
                    }
                } else {
                    // 数据类型格式化
                    switch ($field['type']) {
                        case 'DECIMAL':
                            list($_, $len) = explode(',', $field['length']);
                            $column['type'] = 'number';
                            $column['numberOptions'] = [
                                'separator' => '.',
                                'thousands' => ',',
                                'places' => (int)$len,
                                'default' => number_format(0, $len),
                            ];
                            break;
                    }
                }

                if (is_string($field['setting'])) {
                    $setting = json_decode($field['setting'], true);
                }

                $view['align'] = $setting['align'] ? $setting['align'] : 'left';
                $__views[$view['field']] = $view;

                if ($setting['align']) {
                    $column['cellClass'] = 'text-'.$setting['align'];
                }

                // 行计事件
                if ($setting['row_count']) {
                    $column['calcRow'] = $setting['row_count'];
                }

                // 列计事件
                if ($setting['cell_count']) {
                    $column['calcFooter'] = $setting['cell_count'];
                }

                $permission_field = $permission_table[$field['field']];
                $validates = $permission_field['v'];

                $required = false;

                if ($validates) {
                    $rules = [];

                    foreach ($validates as $validate) {
                        // 设置验证规则
                        $rules[$validate] = 1;
                    }

                    // 整形规则格式化
                    if ($rules['integer']) {
                        $column['formatter'] = 'integer';
                    }

                    // 如果规则有必填和整形设置大于0
                    if ($rules['required'] && $rules['integer']) {
                        $rules['minValue'] = 1;
                    }
                    $column['rules'] = $rules;
                    $required = isset($rules['required']) ? true : false;
                    // $column['headerClass'] .= isset($rules['required']) ? 'cell-required' : '';
                    // $required = isset($rules['required']) ? '<span class="red">*</span> ' : '';
                }

                if ($action == 'show') {
                    $required = false;
                    $rules = [];
                }

                if ($required) {
                    $column['headerClass'] = 'cell-required';
                }

                $column['headerName'] = $field['name'];
                if($field['is_sort'] == 1) {
                    $column['sortable'] = true;
                }

                if ($field['form_type'] == 'label') {
                    $column['_editable'] = false;
                } else {
                    $column['_editable'] = $permission_field['w'] == 1 ? true : false;
                }

                if ($action == 'show') {
                    $column['editable'] = false;
                }

                if ($column['_editable']) {
                    $column['suppressNavigable'] = false;
                }

                // 是否隐藏
                $column['hide'] = $permission_field['s'] == 1 ? true : (bool) $view['hidden'];

                // 字段宽度
                if ($view['width']) {
                    $column['width'] = $view['width'];
                } else {
                    if ($setting['width']) {
                        if ($setting['width'] == 'auto') {
                            $column['minWidth'] = 260;
                        } else {
                            $column['width'] = $setting['width'];
                        }
                    }
                }

                $column['width'] = (int)$column['width'];
                
                if ($field['form_type'] == 'date') {
                    $column['cellEditorParams'] = [
                        'form_type' => $field['form_type'],
                        'type' => $setting['type'],
                        'field' => $field['field'],
                    ];
                    $column['cellEditor'] = 'dateCellEditor';
                }

                if ($field['form_type'] == 'checkbox') {
                    $_checkbox = explode("\n", $setting['content']);
                    $values = [];
                    foreach ($_checkbox as $t) {
                        $n = $v;
                        list($n, $v) = explode('|', $t);
                        $v = is_null($v) ? trim($n) : trim($v);
                        $values[$v] = $n;
                    }
                    $column['cellEditorParams'] = [
                        'values' => $values,
                    ];
                    $column['cellRenderer'] = 'checkboxCellRenderer';
                    $column['cellEditor'] = 'checkboxCellEditor';
                }

                if ($field['form_type'] == 'select') {
                    $a1 = true;
                    if ($field['data_type']) {
                        $query = [];
                        if ($setting['query']) {
                            list($k, $v) = explode('=', $setting['query']);
                            if (strpos($v, '$') === 0) {
                                $v = substr($v, 1);
                                $query[$k] = $row[$v];
                            } else {
                                $query[$k] = explode(',', $v);
                            }
                        }
                        $_model = DB::table($field['data_type'])->where('status', 1);
                        foreach ($query as $k => $v) {
                            if (is_array($v)) {
                                $_model->whereIn($k, $v);
                            } else {
                                $_model->where($k, $v);
                            }
                        }
                        $values = $_model->orderBy('sort', 'asc')->get([$field['data_field'], 'id'])->toArray();
                    } else {
                        $_select = explode("\n", $setting['content']);
                        $values = [];
                        foreach ($_select as $t) {
                            $n = $v;
                            list($n, $v) = explode('|', $t);
                            if (is_null($v)) {
                                $a1 = false;
                            }
                            $v = is_null($v) ? trim($n) : trim($v);
                            $values[] = ['id' => $v, 'name' => $n];
                        }
                    }
                    if ($a1 == true) {
                        $columns[] = ['name' => $field['field'], 'hide' => true];
                        $field['field'] = $field['field'].'_name';
                    }

                    $column['cellEditorParams'] = [
                        'values' => $values,
                        'select_key' => $field['raw_field'],
                    ];
                    $column['cellEditor'] = 'selectCellEditor';
                    $field['select_values'] = $values;
                }

                if ($field['form_type'] == 'option') {
                    $values = option($setting['type']);
                    $column['cellEditorParams'] = [
                        'values' => $values,
                        'select_key' => $field['raw_field'],
                    ];
                    $column['cellEditor'] = 'selectCellEditor';

                    $columns[] = ['field' => $field['field'], 'hide' => true];
                    $field['field'] = $field['_column'];
                }

                if ($field['form_type'] == 'dialog') {

                    if ($field['data_type']) {
                        $type = $field['data_type'];
                    } else {
                        $type = $setting['type'];
                    }
                    $dialog = ModuleService::dialogs($type);

                    if ($field['_column']) {
                        $columns[] = ['name' => $field['field'], 'hide' => true];
                        $field['field'] = $field['_column'];
                    }

                    // 没有关联字段时显示自己
                    $data_link = $field['data_link'] == '' ? $field['field'] : $field['data_link'];

                    $query = [
                        'form_id' => $model['table'],
                        'id' => $data_link,
                        'name' => $field['field'],
                    ];
                    if ($setting['query']) {
                        list($k, $v) = explode('=', $setting['query']);
                        if (strpos($v, '$') === 0) {
                            $v = substr($v, 1);
                            $query[$k] = $row[$v];
                        } else {
                            $query[$k] = $v;
                        }
                    }
                    
                    $cellEditorParams = [
                        'form_type' => $field['form_type'],
                        'title' => $dialog['name'],
                        'type' => $setting['type'],
                        'field' => $field['field'],
                        'url' => $dialog['url'],
                        'query' => $query,
                    ];

                    $column['cellEditorParams'] = $cellEditorParams;
                    $column['cellEditor'] = 'dialogCellEditor';
                }

                if ($field['form_type'] == 'text') {
                    if ($field['dest_column']) {
                        $columns[] = ['name' => $field['field'], 'hide' => true];
                        $field['field'] = $field['dest_column'];
                    }
                }

                if ($view['hidden'] == 0) {
                    $tabContent .= '<td align="center">'.$view['name'].'</td>';
                }
                $field['setting'] = $setting;
                $column['field'] = $field['field'];
                $fields[$field['raw_field']] = $field;
                $columns[] = $column;
            }

            $tabContent .= '</tr>';

            foreach($join as $j) {
                $q->leftJoin($j[0], $j[1], $j[2], $j[3]);
            }

            $_data = Hook::fire($model['table'] . '.onQueryForm', ['q' => $q, 'model' => $model, 'fields' => $fields, 'views' => $views]);
            extract($_data);

            $q->select($select);

            if ($options['select']) {
                $q->addSelect(DB::raw($options['select']));
            }

            // 子表查询
            $rows = $q->get();

            $rows->transform(function($row) use ($fields) {
                foreach($fields as $column) {
                    $field = $column['field'];
                    $raw_field = $column['raw_field'];
                    $value = $row[$field];
                    $raw_value = $row[$raw_field];

                    if ($column['form_type'] == 'text') {
                        if ($column['type'] == 'DECIMAL') {
                            $value = floatval($value) == '0' ? '' : $value;
                        }
                        if ($column['type'] == 'INT' || $column['type'] == 'TINYINT') {
                            $value = floatval($value) == '0' ? '' : $value;
                        }
                    }

                    if ($column['type'] == 'DATE') {
                        $value = $value == '1900-01-01' ? '' : $value;
                    }

                    if ($column['form_type'] == 'select') {
                        if ($column['select_values']) {
                            foreach($column['select_values'] as $_values) {
                                if ($raw_value == $_values['id']) {
                                    $value = $_values['name'];
                                }
                            }
                        }
                    }
                    $row[$field] = $value;
                }
                return $row;
            });

            $_data = Hook::fire($model['table'] . '.onAfterForm', ['rows' => $rows, 'gets' => $gets, 'fields' => $fields, 'id' => $id]);
            extract($_data);

            if ($action == 'print') {
                // 打印渲染
                $footers = [];
                $_rows = [];
                foreach ($rows as $i => $_row) {
                    $tabContent .= '<tr><td align="center">'.($i + 1).'</td>';
                    foreach ($__views as $k => $v) {
                        if ($v['hidden'] == 0) {
                            $field = $fields[$k];
                            $setting = $field['setting'];
                            if ($setting['cell_count'] == 'sum') {
                                $footers[$k] += (float)$_row[$field['field']];
                            }
                            $field['is_show'] = 1;
                            $field['is_print'] = 1;
                            $field['is_sub'] = 1;
                            $vv = FieldService::{'content_'.$field['form_type']}($field, $_row[$field['raw_field']], $_row);
                            $_rows[$i][$field['field']] = $vv;
                            $tabContent .= '<td align="'.$v['align'].'">'.$vv.'</td>';
                        }
                    }
                    $tabContent .= '</tr>';
                }

                if (count($footers) > 0) {
                    $tabContent .= '<tr><td align="center">合计</td>';
                    foreach ($__views as $k => $v) {
                        if ($v['hidden'] == 0) {
                            $field = $fields[$k];
                            $value = $footers[$k];
                            if (isset($value)) {
                                if ($field['form_type'] == 'text') {
                                    if ($field['type'] == 'DECIMAL') {
                                        list($_, $len) = explode(',', $field['length']);
                                        $value = number_format($value, $len > 0 ? $len : 2);
                                    }
                                    if ($column['type'] == 'INT' || $column['type'] == 'TINYINT') {
                                        $value = number_format($value);
                                    }
                                }
                                $tabContent .= '<td align="'.$v['align'].'">'.$value.'</td>';
                            } else {
                                $tabContent .= '<td></td>';
                            }
                        }
                    }
                    $tabContent .= '</tr>';
                }
                $tabContent .= '</tbody></table>';
            }

            $_options = [
                'columns' => $columns,
                'data' => $rows,
                'links' => $links,
                'table' => $model['table'],
                'title' => $model['name'],
            ];

            $js = 'gdoo.forms["'.$model['table'].'"] = gridForms("' . $table . '","' . $model['table'] . '", ' . json_encode($_options, JSON_UNESCAPED_UNICODE) . ');';
            $tab = '<div id="grid-editor-container" class="form-grid"><div id="grid_' . $model['table'] . '" style="width:100%;" class="ag-theme-balham"></div></div>';
            $tabs[] = ['tpl' => $tab, 'tool' => $tool, 'buttons' => $buttons, 'rows' => $_rows, 'fields' => $fields, 'print' => $tabContent, 'id' => $model['table'], 'name' => $model['name'], 'js' => $js];
        }
        return $tabs;
    }

    public static function make2($options)
    {
        $assets = UserAssetService::getNowRoleAssets();

        // 权限查询类型
        $table = $options['table'];
        $row = $options['row'];

        $auth = auth()->user();

        // 表数据
        $flow = DB::table('model')
        ->where('table', $table)
        ->first();

        $fields = DB::table('model_field')
        ->where('model_id', $flow['id'])
        ->orderBy('sort', 'asc')
        ->get()->keyBy('field');

        $file = $options['file'];
        $views = $options['views'];

        $js = '<script type="text/javascript">formGridList.' . $flow['table'] . ' = [];';
        $js .= '$(function() {';

        $html = '';
        $col = 0;
        $sublist_status = false;
        $tabs = [];
        $permission = [];

        $_master_data = Hook::fire($table . '.onBeforeForm', ['options' => $options, 'permission' => $permission, 'model' => $flow, 'fields' => $fields, 'views' => $views, 'row' => $row]);
        extract($_master_data);
        
        $tpls = [];
        $sublist = [];

        $tpl = '';

        libxml_disable_entity_loader(false);
        $xml = new XMLReader();  
        $xml->open($options['file']);

        while ($xml->read()) {
            
            $depth = $xml->depth;

            if ($xml->nodeType == XMLReader::ELEMENT) {

                $nodeName = $xml->name;

                $attr = [];
                $attrs = [];
                if ($xml->hasAttributes) {
                    while($xml->moveToNextAttribute()) {
                        $attr[$xml->name] = $xml->value;
                        $attrs[] = $xml->name.'="'.$xml->value.'"';
                    }
                }
                
                switch($nodeName) {
                    case 'style':
                        $tpl .= '<style type="text/css">';
                    break;
                    case 'script':
                        $tpl .= '<script type="text/javascript">';
                    break;
                    case 'form':
                        $tpl .= '<form '.join(' ', $attrs).'>';
                    break;
                    case 'div':
                        $tpl .= '<div '.join(' ', $attrs).'>';
                    break;
                    case 'group':
                        $tpl .= '<div class="form-group">';
                    break;
                    case 'field':
                        $field = $fields[$attr['name']];
                        $input = static::getField($flow, $table, $action, $row, $attr, $field);
                        if ($attr['hidden'] == 1) {
                            $tpl .= $input;
                        } else {
                            if ($attr['label']) {
                                $col = $attr['col_type'].'-'.$attr['col_label'];
                                $tpl .= '<div class="col-'.$col.' control-label">' . $field['name'] . '</div>';
                            }
                            $col = $attr['col_type'].'-'.$attr['col_name'];
                            $tpl .= '<div class="col-'.$col.' control-text">' . $input . '</div>';
                        }
                    break;
                }
            }

            if ($xml->nodeType == XMLReader::TEXT) {
                $tpl .= $xml->value;
            }

            if ($xml->nodeType == XMLReader::END_ELEMENT)
            {
                $nodeName = $xml->name;
                switch($nodeName) {
                    case 'group':
                    case 'div':
                        $tpl .= '</div>';
                    break;
                    case 'form':
                        $tpl .= '</form>';
                    break;
                    case 'style':
                        $tpl .= '</style>';
                    break;
                    case 'script':
                        $tpl .= '</script>';
                    break;
                }
            }
        }
        return $tpl;
    }

    public static function getField($flow, $table, $action, $row, $attr, $field) {
        $field['model'] = $flow;
        $attribute = [];
        if ($action == 'show') {
            $field['is_show'] = true;
        }

        $p = [];

        $p['w'] = $attr['read'] == 1 ? 0 : 1;

        $p['s'] = $attr['hidden'] == 1 ? 1 : 0;

        $field['is_print'] = $action == 'print';
        $field['is_write'] = $p['w'] == 1 ? 1 : 0;
        $field['is_read'] = $p['w'] == 1 ? 0 : 1;
        $field['is_auto'] = $p['m'] == 1 ? 1 : 0;
        $field['is_hide'] = $p['s'] == 1 ? 1 : $field['is_hide'];

        $validate = (array) $p['v'];

        if ($action == 'print') {
            $field['is_show'] = true;
        }

        if ($action == 'print') {
        } else {
            $required = '';
            if (in_array('required', $validate)) {
                $required = '<span class="red">*</span> ';
                if ($field['is_write']) {
                    $attribute['required'] = 'required';
                    if ($field['is_auto'] == 0) {
                        $attribute['class'][] = 'input-required';
                    } else {
                        $attribute['class'][] = 'input-auto';
                    }
                }
            }
        }

        $field['verify'] = $validate;
        $field['attribute'] = $attribute;
        $field['table'] = $table;

        $tooltip = $field['tips'] ? ' <a class="hinted" href="javascript:;" title="' . $field['tips'] . '"><i class="fa fa-question-circle"></i></a>' : '';

        if ($action == 'show' || $action == 'print') {
            $tooltip = '';
            $required = '';
        }

        $_replace['{' . $field['name'] . '}'] = $required . $field['name'] . $tooltip;
        
        $data_type = $field['data_type'];
        $data_field = $field['data_field'];
        $data_link = $field['data_link'];
        if ($data_type) {
            $related = [];
            if (strpos($data_field, ':')) {
                list($var1, $var2) = explode(':', $data_field);
                list($_v1, $_v2) = explode('.', $var1);
                list($_t1, $_t2) = explode('.', $var2);
                if ($field['type']) {
                    $related['table'] = $table;
                    $related['field'] = $field['field'];
                } else {
                    $related['table'] = $_t1;
                    $related['field'] = $_v2;
                }
            } else {
                if ($field['type']) {
                    $related['table'] = $table;
                    $related['field'] = $field['field'];
                } else {
                    $related['table'] = $data_type;
                    $related['field'] = $data_field;
                }
            }
            $field['related'] = $related;
        }
        $value = $row[$field['field']];

        $field['view'] = $attr;
        if ($field['form_type']) {
            return FieldService::{'content_' . $field['form_type']}($field, $value, $row, $permission = []);
        } else {
            return FieldService::{'content_text'}($field, $value, $row, $permission = []);
        }
    }

    public static function flowRules($models, $gets)
    {
        $master = $gets['master'];

        $rules = $messages = $attributes = [];

        $_permission = DB::table('model_permission')
        ->find($master['permission_id']);
    
        $permissions = json_decode($_permission['data'], true);

        foreach ($models as $model) {
            $table = $model->table;

            $fields = $model->fields->keyBy('field');
            foreach ((array)$permissions[$table] as $key => $row) {
                $field = $fields[$key];
                $_rules = (array)$row['v'];
                if ($_rules) {
                    $t = $model['type'] == 1 ?  $table . '.rows.*.' . $key : $table . '.' . $key;
                    $data_type = $field['data_type'];
                    $data_field = $field['data_field'];
                    $data_link = $field['data_link'];
                    $data_status = 0;
                    if ($data_type) {
                        if (empty($field['type'])) {
                            $data_status = 1;
                            $t = $model['type'] == 1 ?  $data_type . '.rows.*.' . $data_field : $data_type . '.' . $data_field;
                        }
                    }
                    foreach($_rules as &$_rule) {
                        // 处理唯一判断
                        if ($_rule == 'unique') {
                            if ($data_status == 1) {
                                $_rule = 'unique:'.$data_type.','.$data_field.','.$gets[$data_type][$data_link].','.$data_link;
                            } else {
                                $_rule = 'unique:'.$table.','.$key.','.$gets[$table]['id'].',id';
                            }
                        }
                    }
                    $rules[$t] = join('|', $_rules);
                    $attributes[$t] = $fields[$key]['name'];
                }
            }
        }

        // 获取表单上的审核意见
        if ($master['run_id'] && $gets['step_remark']) {

            $run_steps = DB::table('model_run_step')
            ->where('run_id', $master['run_id'])
            ->get()->keyBy('step_id');

            $steps = $permissions['flow_step'];
            foreach((array)$steps as $step_id => $step) {
                $v = (array)$step['v'];
                if ($v) {
                    $rules['step_remark.'.$step_id] = join('|', $step['v']);
                    $attributes['step_remark.'.$step_id] = $run_steps[$step_id]['name'].'审核意见';
                }
            }
        }

        return ['rules' => $rules, 'messages' => $messages, 'attributes' => $attributes];
    }

    /**
     * 数据导入
     */
    public static function import($params) 
    {
        // 上传文件
        $table = $params['table'];
        $keys = $params['keys'];

        $file = Request::file('file');

        if (empty($file)) {
            return response_json('文件必须选择');
        }

        if ($file->isValid()) {
            set_time_limit(0);
            $datas = readExcel($file->getPathName());
            $flow = DB::table('model')
            ->where('table', $table)
            ->first();

            $fields = DB::table('model_field')
            ->where('model_id', $flow['id'])
            ->get();

            $options = [];
            $names = [];
            $links = [];

            // 记录提醒的字段的名称
            $tips = [];

            foreach ($fields as $field) {
                $setting = json_decode($field['setting'], true);
                $field['setting'] = $setting;

                if ($field['form_type'] == 'option') {
                    $options[$field['field']] = option($field['setting']['type'])->pluck('id', 'name');
                }

                if ($field['data_type']) {
                    $data_type = $field['data_type'];
                    $data_field = $field['data_field'];
                    $data_link = $field['data_link'];

                    $key = $data_type.'_'.$data_link;
                    $_link = $links[$key];
                    $_link['table'] = $data_type;
                    $_link['link'] = $data_link;
                    if (strpos($data_field, ':')) {
                        list($var1, $var2) = explode(':', $data_field);
                        list($_t, $_f) = explode('.', $var2);
                        $_link['join'][$_t] = [$_t, $_t.'.id', '=', $data_type.'.'.$var1];
                        $_link['key'] = $var2;
                        $_link['field'] = $_f;
                        $_link['value'] = $data_type.'.id';
                    } else {
                        $_link['key'] = $data_field;
                        $_link['field'] = $data_field;
                        $_link['value'] = 'id';
                    }
                    $_link['base'] = $field['type'] == '' ? 0 : 1;
                    $links[$key] = $_link;
                    $tips[$data_link] = $field['name'];
                } else {
                    $tips[$field['field']] = $field['name'];
                }
                $names[trim($field['name'])] = $field;
            }

            // 获取数据的第一行记录
            $header = Arr::pull($datas, 1);

            $fields = [];
            foreach ($header as $i => $col) {
                $col = trim($col);
                if ($names[$col]) {
                    $fields[$i] = $names[$col];
                }
            }

            $types = [];

            $rows = [];
            $i = 0;
            foreach ($datas as $data) {
                $row = [];
                foreach ($data as $j => $col) {
                    $field = $fields[$j];
                    $col = trim($col);

                    if ($field['type']) {
                        if ($field['form_type'] == 'option') {
                            $row[$table][$field['field']] = $options[$field['field']][$col];
                        }
                        if ($field['form_type'] == 'dialog') {
                            $row[$table][$field['field']] = (int)$col;
                        }
                        if ($field['form_type'] == 'text') {
                            $row[$table][$field['field']] = $col;
                        }
                        if ($field['type'] == 'INT') {
                            $types[$table][$field['field']] = 'int';
                        }
                    }
                    if ($field['data_type']) {
                        $data_type = $field['data_type'];
                        $data_field = $field['data_field'];
                        $data_link = $field['data_link'];
                        if ($col) {
                            $link = $links[$data_type.'_'.$data_link];
                            $row[$data_type][$link['field']] = $col;
                            //if ($link['base'] == 0) {
                            $row[$table][$field['data_link']] = $col;
                            //}
                            $links[$data_type.'_'.$data_link]['in'][$col] = $col;
                        }
                    }
                }
                $rows[$i] = $row;
                $i++;
            }

            $options = [];
            foreach ($links as $i => $row) {
               $model = DB::table($row['table']);
                if ($row['join']) {
                    foreach ($row['join'] as $join) {
                        $model->leftJoin($join[0], $join[1], $join[2], $join[3]);
                    }
                }
                if ($row['in']) {
                    $items = $model->whereIn($row['key'], $row['in'])
                    ->pluck($row['value'], $row['key'])->toArray();
                    $options[$row['link']] = $items;
                }
            }

            $items = [];
            foreach ($rows as $i => $data) {
                $row = $data[$table];
                foreach ($row as $field => $col) {
                    if (isset($options[$field])) {
                        $row[$field] = $options[$field][$col];
                    }
                    $type = $types[$table][$field];
                    if (isset($type)) {
                        if ($type == 'int') {
                            $row[$field] = (int)$row[$field];
                        }
                    }
                }
                $items[$i] = $row;
            }

            DB::beginTransaction();
            try {
                $start = microtime(true);
                $update = $insert = 0;
                foreach ($items as $i => $item) {
                    $model = DB::table($table);
                    foreach ($keys as $key) {
                        if (empty($item[$key])) {
                            abort_error('表格第'.($i + 1).'行['.$tips[$key].']数据不存在。');
                        }
                        $model->where($key, $item[$key]);
                    }
                    $ret = $model->first();
                    
                    $_hook = Hook::fire($table.'.onBeforeImport', ['table' => $table, 'item' => $item, 'ret' => $ret]);
                    extract($_hook);
                    
                    if ($ret['id']) {
                        $update++;
                        DB::table($table)->where('id', $ret['id'])->update($item);
                    } else {
                        $insert++;
                        $item['id'] = DB::table($table)->insertGetId($item);
                    }
                    $_hook['item'] = $item;
                    Hook::fire($table.'.onAfterImport', $_hook);
                }
                DB::commit();

                $end = microtime(true) - $start;
                return response_json('导入成功，耗时: '.number_format($end, 2).'秒，新建：'.$insert.'，更新：'.$update, true);
            } catch(\Exception $e) {
                DB::rollBack();
                abort_error($e->getMessage());
            }
            
        } else {
            return response_json($file->getError());
        }
    }

    /**
     * 检查CSRF
     */
    public static function tokensMatch()
    {
        $req = request();

        $sessionToken = $req->session()->token();
        $token = $req->input('_token') ?: $req->header('X-CSRF-TOKEN');

        if (!$token && $header = $req->header('X-XSRF-TOKEN')) {
            $token = decrypt($header);
        }

        if (! is_string($sessionToken) || ! is_string($token)) {
            abort_error('Token Mismatch');
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function dataFilter($table, $fields, $permissions, $master, $values, &$dataFiles)
    {
        $_permissions = $permissions[$table];

        foreach ($fields as $field) {

            $key = $field['field'];
            $setting = $field['setting'];
            $value = $values[$key];
            $permission = $_permissions[$key];

            // 权限可写
            if ($permission['w'] == 1) {

                // 自定义过滤器
                $_field_data = Hook::fire($table.'.onFieldFilter', ['table' => $table, 'master' => $master, 'field' => $field, 'values' => $values]);
                extract($_field_data);

                if ($field['data_format']) {
                    switch ($field['data_format']) {
                        case 'number':
                        case 'money':
                            list($_, $len) = explode(',', $field['length']);
                            $len = $len > 0 ? $len : 2;
                            $value = round(floatval($value), $len);
                            break;
                    }
                } else {
                    switch ($field['type']) {
                        case 'DECIMAL':
                            list($_, $len) = explode(',', $field['length']);
                            $value = round(floatval($value), $len);
                            break;
                    }
                }

                switch ($field['form_type']) {
                    case 'autocomplete':
                        $value = str_replace('draft_', '', $value);
                        break;
                    case 'address':
                        $value = join("\n", (array)$value);
                        break;
                    case 'files':
                        $value = (array)$value;
                        $dataFiles = array_merge($dataFiles, $value);
                        $value = join(",", $value);
                        break;
                    case 'images':
                        $value = join("\n", (array)$value);
                        break;
                    case 'date':
                        if ($setting['save'] == 'u') {
                            $value = empty($value) ? '' : strtotime($value);
                        }
                        break;
                    case 'checkbox':
                        if (is_array($value)) {
                            $value = join(",", (array)$value);
                        } else {
                            $value = intval($value);
                        }
                        break;
                }
                $values[$key] = $value;
            }
        }

        return $values;
    }

    /**
     * 保存数据
     */
    public static function store($bill, $models, $gets, $id, $store_type = 'store')
    {
        $model = $models[0];

        // 判断演示模式
        License::demoCheck();
        
        // 当前用户
        $auth = Auth::user();

        // 检查表单Token
        static::tokensMatch();

        // 主表名
        $table = $model->table;

        $master = $gets['master'];
        $run_id = $master['run_id'];
        $step_id = $master['step_id'];
        $run_step_id = $master['run_step_id'];
        $run_log_id = $master['run_log_id'];
        $permission_id = $master['permission_id'];
    
        $dataFiles = [];

        $permission = DB::table('model_permission')->find($permission_id);
        $permissions = json_decode($permission['data'], true);

        $datas = $deleteds = [];

        foreach ($models as $m) {

            $t = $m->table;
            $fields = $m->fields->keyBy('field');

            // 获取setting内容
            foreach ($fields as $key => $field) {
                $setting = json_decode($field['setting'], true);
                $field['setting'] = $setting;
                $fields[$key] = $field;
            }

            // 获取多行子表数据
            if ($m->parent_id > 0) {

                $deleteds[$m->table] = $gets[$m->table]['deleteds'];
                $rows = (array)$gets[$m->table]['rows'];

                // 格式化子表数据格式
                foreach ($rows as $i => $row) {
                    $rows[$i] = static::dataFilter($table, $fields, $permissions, $master, $row, $dataFiles);
                }

                $datas[] = [
                    'table' => $m->table,
                    'type' => $m->type,
                    'relation' => $m->relation,
                    'data' => $rows,
                    'deleteds' => (array)$gets[$m->table]['deleteds']
                ];
            } else {
                // 处理主表的字段格式
                $gets[$t] = static::dataFilter($table, $fields, $permissions, $master, $gets[$t], $dataFiles);
            }
        }

        // 主表数据
        $master = $gets[$table];

        DB::beginTransaction();
        try {

            // 是否自动处理表单写入相关，有时候我们希望自己处理相关写入功能
            $terminate = true;

            $_data = Hook::fire($table.'.onBeforeStore', ['table' => $table, 'gets' => $gets, 'master' => $master, 'datas' => $datas, 'terminate' => $terminate]);
            extract($_data);

            if ($terminate == true) {

                // 更新表单的流程意见
                $step_remark = $gets['step_remark'];
                if (not_empty($step_remark)) {
                    foreach ($step_remark as $step_id => $remark) {

                        // 审核操作无审核意见
                        if ($remark == '') {
                            $remark = $gets['step_next_type'] == 'back' ? '退回' : '同意';
                        }

                        RunStep::where('run_id', $run_id)->where('step_id', $step_id)->update([
                            'run_remark' => $remark,
                            'run_updated_id' => $auth['id'],
                            'run_updated_by' => $auth['name'],
                            'run_updated_at' => time(),
                        ]);
                    }
                }

                // 更新主表
                if ($id) {
                    DB::table($table)->where('id', $id)->update($master);
                } else {
                    if ($bill['sn_length'] > 0) {
                        $make_sn = make_sn([
                            'table' => $table,
                            'data' => $master['sn'],
                            'bill_id' => $bill['id'],
                            'prefix' => $bill['sn_prefix'],
                            'rule' => $bill['sn_rule'],
                            'length' => $bill['sn_length'],
                        ], true);
                        // 更新单据编码
                        $master['sn'] = $make_sn['new_value'];
                    }
                    $id = DB::table($table)->insertGetId($master);
                }

                foreach ($datas as $data) {
                    $rows = $data['data'];
                    // 多行子表
                    if ($data['type'] == 1) {

                        foreach ($rows as $row) {
                            // 子表关联ID
                            $row[$data['relation']] = $id;

                            // 事件过滤数据
                            $_event = Hook::fire($data['table'].'.onBeforeStore', ['table' => $table, 'master' => $master, 'row' => $row]);
                            $row = $_event['row'];

                            if ($row['id']) {
                                DB::table($data['table'])->where('id', $row['id'])->update($row);
                            } else {
                                $row['id'] = DB::table($data['table'])->insertGetId($row);
                            }
                            $_event['row'] = $row;

                            Hook::fire($data['table'].'.onAfterStore', $_event);
                        }
                    } else {
                        // 附表暂时未实现
                    }
                }

                // 删除列表数据
                if (count($deleteds)) {
                    foreach($deleteds as $_deleted_table => $_deleteds) {
                        $_ids = [];
                        foreach((array)$_deleteds as $_deleted) {
                            if ($_deleted['id'] > 0) {
                                $_ids[] = $_deleted['id'];
                            }
                        }
                        if (count($_ids) > 0) {
                            DB::table($_deleted_table)->whereIn('id', $_ids)->delete();
                        }
                    }
                }

                // 附件发布
                AttachmentService::publish($dataFiles);

                // 重新赋值表主键id
                $master['id'] = $id;
            }

            $_data = Hook::fire($table.'.onAfterStore', ['master' => $master, 'datas' => $datas, 'gets' => $gets]);
            extract($_data);

            // 单据和流程一起转交
            $gets[$table] = $master;
            if ($store_type == 'audit') {
                static::storeFlowStep($bill, $models, $gets, $id);
            }

            // 提交事务
            DB::commit();

        } catch (\App\Exceptions\AbortException $e) {
            DB::rollback();
            system_log('bill.store', '保存:'.$bill['name'], $e->getMessage(), 'error');
            abort_error($bill['name']."<br>".$e->getMessage());
        } catch (\Exception $e) {
            DB::rollback();
            system_log('bill.store', '保存:'.$bill['name'], $e->getMessage(), 'error');
            abort_error($bill['name']."<br>".str_replace(base_path().DIRECTORY_SEPARATOR,'',$e->getFile()).'('.$e->getLine().")<br>".$e->getMessage());
        }
        return $master['id'];
    }

    /**
     * 审核单据
     */
    public static function audit($bill, $models, $gets, $id)
    {
        DB::beginTransaction();
        try {

            $master_id = static::storeFlowStep($bill, $models, $gets, $id);

            // 提交事务
            DB::commit();

            return $master_id;

        } catch (\App\Exceptions\AbortException $e) {
            DB::rollback();
            abort_error($bill['name']."<br>".$e->getMessage());
        } catch (\Exception $e) {
            DB::rollback();
            abort_error($bill['name']."<br>".str_replace(base_path().DIRECTORY_SEPARATOR,'',$e->getFile()).'('.$e->getLine().")<br>".$e->getMessage());
        }
    }

    /**
     * 审核单据
     */
    public static function storeFlowStep($bill, $models, $gets, $id)
    {
        $model = $models[0];

        // 判断演示模式
        License::demoCheck();
        
        // 当前用户
        $auth = Auth::user();

        // 主表名
        $table = $model->table;

        $master = $gets['master'];
        $run_id = $master['run_id'];
        $step_id = $master['step_id'];
        $run_step_id = $master['run_step_id'];
        $run_log_id = $master['run_log_id'];

        // 定义提醒方式
        $messages = [
            'audit' => [], 
            'notify' => [], 
            'uri' => $master['uri'].'/show'
        ];

        // 主表数据
        $master = $gets[$table];

        // 审核完成时执行
        if ($gets['step_next_type'] == 'end') {
            $_data = Hook::fire($table.'.onBeforeAudit', ['table' => $table, 'master' => $master, 'id' => $master['id']]);
            extract($_data);
        }

        /*
        back 退回
        draft 草稿
        next 执行中
        active 生效
        recall 撤回
        abort 弃审
        */

        if ($bill['audit_type'] == 1) {

            if ($gets['step_next_type']) {
                // 设置流程主表状态
                switch ($gets['step_next_type']) {
                    case 'next':
                        $master['status'] = '2';
                        break;
                    case 'back':
                        $master['status'] = '-2';
                        break;
                    case 'end':
                        $master['status'] = '1';
                        break;
                }
            }

            $run = DB::table('model_run')
            ->where('bill_id', $bill['id'])
            ->where('data_id', $id)
            ->first();

            $run_index = $run['index'] + 1;

            // 草稿审核
            if (empty($run)) {
                $flow_run = [
                    'bill_id' => $bill['id'],
                    'data_id' => $id,
                    'name' => $bill['name'],
                    'sn' => $master['sn'],
                ];
                
                // 写入流程运行信息
                $run_id = DB::table('model_run')->insertGetId($flow_run);

                // 复制流程节点到运行节点
                $_steps = DB::table('model_step')->where('bill_id', $bill['id'])->get();
                foreach ($_steps as $_step) {
                    $_step['run_id'] = $run_id;
                    $_step['step_id'] = $_step['id'];
                    $_step['id'] = 0;
                    DB::table('model_run_step')->insert($_step);
                }

                // 读取第一步流程
                $run_step = DB::table('model_run_step')
                ->where('bill_id', $bill['id'])
                ->where('run_id', $run_id)
                ->where('type', 'start')
                ->first();

                $log = [
                    'bill_id' => $bill['id'],
                    'parent_id' => 0,
                    'user_id' => $auth['id'],
                    'role_id' => $auth['role_id'],
                    'run_id' => $run_id,
                    'run_step_id' => $run_step['id'],
                    'run_name' => $run_step['name'],
                    'run_status' => $gets['step_next_type'],
                    'updated_id' => $auth['id'],
                    'updated_at' => time(),
                    'status' => 1,
                ];

                // 更新审核意见到节点
                DB::table('model_run_step')
                ->where('id', $run_step['id'])
                ->update([
                    'run_remark' => $gets['remark'],
                    'run_updated_id' => $auth['id'],
                    'run_updated_by' => $auth['name'],
                    'run_updated_at' => time(),
                ]);

                // 写入第一步办理节点
                $run_log_id = DB::table('model_run_log')->insertGetId($log);
            } else {
                $run_id = $run['id'];
            }

            $run_mode = $gets['run_mode'];

            // 当前办理日志
            $run_log = DB::table('model_run_log')
            ->where('run_id', $run_id)
            ->where('id', $run_log_id)
            ->where('status', 0)
            ->first();

            // 当前办理日志的父节点
            $parent_run_log = DB::table('model_run_log')
            ->where('run_id', $run_id)
            ->where('id', $run_log['parent_id'])
            ->first();

            // 读取上一步的所有未办理记录不包含自己
            if ($run_log['parent_id'] > 0) {
                $run_logs = DB::table('model_run_log')
                ->where('run_id', $run_id)
                ->where('parent_id', $run_log['parent_id'])
                ->whereNotIn('id', [$run_log['id']])
                ->where('status', 0)
                ->get();
                // 其他人待办数量
                $run_logs_count = $run_logs->count();
            } else {
                $run_logs = [];
                // 其他人待办数量
                $run_logs_count = 0;
            }

            // 写入下一步待办
            $next_step_write = false;

            // 更新其他人待办
            $next_step_other = false;

            // 1:单人执行，2:多人执行，3:全体执行，4:竞争执行
            switch ($run_mode)
            {
                case 1: // 单人执行
                    $next_step_write = true;
                    $next_step_other = true;
                    break;
                case 2: // 多人执行
                    if ($run_logs_count > 0) {
                        $next_step_write = false;
                    } else {
                        $next_step_write = true;
                    }
                    $next_step_other = false;
                    break;
                case 3: // 全体执行
                    if ($run_logs_count > 0) {
                        $next_step_write = false;
                    } else {
                        $next_step_write = true;
                    }
                    $next_step_other = false;
                    break;
                case 4: // 竞争执行
                    $next_step_write = true;
                    $next_step_other = true;
                    break;
            }

            // 更新自己已办日志
            DB::table('model_run_log')
            ->where('run_id', $run_id)
            ->where('id', $run_log['id'])
            ->update([
                'run_status' => $gets['step_next_type'],
                'remark' => $gets['remark'],
            ]);

            // 更新其他人待办
            if ($next_step_other == true) {
                foreach ($run_logs as $log) {
                    // 更新已办日志
                    DB::table('model_run_log')
                    ->where('run_id', $run_id)
                    ->where('option', 1)
                    ->where('id', $log['id'])
                    ->update([
                        'run_status' => $gets['step_next_type'],
                        'remark' => $gets['remark'],
                    ]);
                }
            }

            // 获取审核人和抄送人
            $user_all_ids = array_merge($gets['step_user_ids'], $gets['notify_user_ids']);

            // 获取审核人和抄送人角色id
            $role_ids = DB::table('user')->whereIn('id', $user_all_ids)->pluck('role_id', 'id');

            // 写入下一步骤审核日志
            if ($next_step_write == true) {
                $run_log_id = $gets['step_next_type'] == 'back' ? $parent_run_log['parent_id'] : $run_log_id;

                $step_next = DB::table('model_run_step')
                ->where('bill_id', $bill['id'])
                ->where('run_id', $run_id)
                ->where('step_id', $gets['step_next_id'])
                ->first();

                // 结束流程直接跳过
                if ($step_next['type'] == 'end') {
                } else {
                    // 如果退回流程到开始
                    if ($gets['step_next_type'] == 'back' && $step_next['type'] == 'start') {
                        $master['status'] = 0;
                    }
                    $user_ids = (array)$gets['step_user_ids'];
                    foreach ($user_ids as $user_id) {
                        $messages['audit'][] = $user_id;
                        DB::table('model_run_log')->insert([
                            'bill_id' => $bill['id'],
                            'parent_id' => $run_log_id,
                            'run_id' => $run_id,
                            'user_id' => $user_id,
                            'role_id' => $role_ids[$user_id],
                            'run_step_id' => $step_next['id'],
                            'run_name' => $step_next['name'],
                            'run_status' => 'draft',
                            'run_index' => $run_index,
                            'status' => 0,
                        ]);
                    }
                }
            }

            // 写入知会节点
            if ($gets['step_next_type'] == 'next' || $gets['step_next_type'] == 'end') {
                $step_inform_ids = $gets['step_next_inform'];
                if ($step_inform_ids) {
                    // 查询知会节点
                    $notify_step_ids = array_keys($step_inform_ids);
                    $notify_steps = DB::table('model_run_step')
                    ->where('bill_id', $bill['id'])
                    ->where('run_id', $run_id)
                    ->whereIn('step_id', $notify_step_ids)
                    ->where('option', 0)
                    ->get()->keyBy('step_id');

                    foreach($step_inform_ids as $step_inform_id => $notify_user_ids) {
                        $user_ids = explode(',', $notify_user_ids);
                        foreach($user_ids as $user_id) {
                            if ($user_id) {
                                $notify = $notify_steps[$step_inform_id];
                                $messages['notify'][] = $user_id;
                                DB::table('model_run_log')->insert([
                                    'bill_id' => $bill['id'],
                                    'parent_id' => $run_log_id,
                                    'run_id' => $run_id,
                                    'user_id' => $user_id,
                                    'role_id' => $role_ids[$user_id],
                                    'run_step_id' => $notify['id'],
                                    'run_name' => $notify['name'],
                                    'run_status' => 'draft',
                                    'run_index' => $run_index,
                                    'option' => 0,
                                    'status' => 0,
                                ]);
                            }
                        }
                    }
                }
            } else if($gets['step_next_type'] == 'back') {
                // 退回流程删除知会记录
                $step_back_inform = array_values((array)$gets['step_back_inform']);
                DB::table('model_run_log')->whereIn('id', $step_back_inform)->delete();
            }

            // 更新办理序号
            $_run['index'] = $run_index;

            // 写入往来单位
            if ($master['customer_id'] > 0) {
                $_run['partner_id'] = $master['customer_id'];
                $_run['partner_type'] = 'customer';
            }
            if ($master['supplier_id'] > 0) {
                $_run['partner_id'] = $master['supplier_id'];
                $_run['partner_type'] = 'supplier';
            }

            // 流程结束时设置运行主表
            if ($gets['step_next_type'] == 'end') {
                $_run['actived_id'] = $auth['id'];
                $_run['actived_by'] = $auth['name'];
                $_run['actived_at'] = time();
            }

            DB::table('model_run')
            ->where('id', $run_id)
            ->update($_run);
        }

        // 更新数据主表
        DB::table($table)->where('id', $master['id'])->update($master);

        $messages['master'] = $master;
        $messages['table'] = $table;

        static::notification($bill, $messages);

        return $master['id'];
    }

    /**
     * 流程办理通知相关
     */
    public static function notification($bill, $params)
    {
        $gets = $params['gets'];
        $master = $params['master'];
        $auth = $params['auth'];

        $data = DB::table($params['table'])->where('id', $master['id'])->first();

        // 往来单位
        if ($data['customer_id']) {
            $partner = DB::table('customer')->where('id', $data['customer_id'])->first();
        }
        if ($data['supplier_id']) {
            $partner = DB::table('supplier')->where('id', $data['supplier_id'])->first();
        }

        $step_inform_sms = $gets['step_inform_sms'];
        $step_inform_text = $gets['step_inform_text'];
        if (empty($step_inform_text)) {
            $step_inform_text = '请您及时办理由'.$auth['name'].'转交的'.$bill['name'].'('.$data['sn'].')。';
        }

        // h5通知(微信公众号)
        $url = env('WAP_BASE_URL').'/#/pages/webview?title='.$bill['name'].'&url='.encodeURIComponent($params['uri'].'?id='.$data['id']);
        if (app()->environment() == 'development') {
            $template_id = 'gL6qSaU4xiUC7Bk26R1WZvHugqheyZ6SQc0W09LF9RY';
        } else {
            $template_id = '1LkZcva0fba8el6tbGC7eoCg9D1u4TZ_o6Qqhd1CJAI';
        }
        $msg = [
            'template_id' => $template_id,
            'url' => $url,
            'data' => [
                'first' => $bill['name'],
                'keyword1' => $partner['name'],
                'keyword2' => $data['sn'],
                'remark' => '待处理',
            ],
        ];

        // 暂时不启用短信提醒
        $step_inform_sms = 0;

        // 审核
        if ($params['audit']) {
            NotificationService::wechatTemplate($params['audit'], $msg);
            // 短信通知
            if ($step_inform_sms) {
                // 查询通知人手机号码
                $phones = DB::table('user')->whereIn('id', $params['audit'])->whereRaw("isnull(phone,'') <> ''")->pluck('phone');
                if ($phones->count()) {
                    NotificationService::sms($phones->toArray(), $step_inform_text);
                }
            }
        }

        // 知会
        if ($params['notify']) {
            NotificationService::wechatTemplate($params['notify'], $msg);
            // 短信通知
            if ($step_inform_sms) {
                // 查询通知人手机号码
                $phones = DB::table('user')->whereIn('id', $params['notify'])->whereRaw("isnull(phone,'') <> ''")->pluck('phone');
                if ($phones->count()) {
                    NotificationService::sms($phones->toArray(), $step_inform_text);
                }
            }
        }
    }

    // 删除表单数据
    public static function remove($params)
    {
        $code = $params['code'];
        $ids = array_filter((array)$params['ids']);
        if (empty($ids)) {
            return response_json('最少选择一行记录。');
        }

        // 获取应用
        $bill = DB::table('model_bill')->where('code', $code)->first();
        
        // 主模型字段
        $flow = DB::table('model')->where('id', $bill['model_id'])->first();

        // 查询子表
        $models = DB::table('model')->where('parent_id', $flow['id'])->get();

        DB::beginTransaction();
        try {
            // 数据主表
            $masters = DB::table($flow['table'])->whereIn('id', $ids)->get();

            // 数据子表
            $datas = [];
            if ($models->count()) {
                foreach ($models as $model) {
                    $data['table'] = $model['table'];
                    $data['data'] = DB::table($model['table'])->whereIn($model['relation'], $ids)->get()->toArray();
                    $datas[] = $data;
                }
            }

            // 删除使用过的关联表
            Hook::fire($flow['table'].'.onBeforeDelete', ['table' => $flow['table'], 'masters' => $masters, 'datas' => $datas, 'ids' => $ids]);

            // 删除主表数据
            DB::table($flow['table'])->whereIn('id', $ids)->delete();

            // 删除子表数据
            foreach ($models as $model) {
                DB::table($model['table'])->whereIn($model['relation'], $ids)->delete();
            }

            if ($bill['audit_type'] == 1) {
                $run_ids = DB::table('model_run')
                ->where('bill_id', $bill['id'])
                ->whereIn('data_id', $masters->pluck('id'))
                ->pluck('id');

                DB::table('model_run_step')
                ->whereIn('run_id', $run_ids)
                ->delete();

                DB::table('model_run_log')
                ->whereIn('run_id', $run_ids)
                ->delete();

                DB::table('model_run')
                ->whereIn('id', $run_ids)
                ->delete();
            }

            // 删除使用过的关联表
            Hook::fire($flow['table'].'.onAfterDelete', ['table' => $flow['table'], 'masters' => $masters, 'datas' => $datas, 'ids' => $ids]);

            // 最后清理附件和流程记录(未实现)

            DB::commit();
            return response_json('删除'.$flow['name'].'成功。', true);

        } catch(\Exception $e) {
            DB::rollBack();
            abort_error('删除'.$flow['name'].'失败:'.$e->getMessage());
        }
    }

    // 获取相关权限
    public static function getAuthorise($options) {
        $table = $options['table'];
        $authorise = $options['authorise'];
        $access = UserService::authoriseAccess($authorise['action']);
        $region = $options['region'];
        
        $m = DB::table($table);
        if ($region) {
            $_region = regionCustomer('customer');
            if ($_region['authorise']) {
                $model = DB::table('customer');
                foreach ($_region['whereIn'] as $k => $v) {
                    $ids = $model->whereIn($k, $v)->pluck('id');
                }
                $m->whereIn($table.'.'.$region['field'], $ids);
            } else {
                if ($authorise) {
                    if ($access) {
                        $m->whereIn($table.'.'.$authorise['field'], $access);
                    }
                }
            }
        } else {
            if ($authorise) {
                if ($access) {
                    $m->whereIn($table.'.'.$authorise['field'], $access);
                }
            }
        }
        return $m;
    }

    public static function getPage($options) {
        $table = $options['table'];

        $q = static::getAuthorise($options);

        Hook::fire($table.'.onBeforePage', ['q' => $q, 'options' => $options['table']]);

        $start = clone $q;
        $end = clone $q;
        $prev = clone $q;
        $next = clone $q;

        $id = $table.'.id';
        
        $page['start'] = $start->orderBy($id, 'asc')
        ->limit(1)->value($id);

        $page['end'] = $end->orderBy($id, 'desc')
        ->limit(1)->value($id);

        if ($options['id'] > 0) {
            $page['prev'] = $prev->where($id, '<', $options['id'])
            ->orderBy($id, 'desc')
            ->limit(1)->value($id);
            $page['next'] = $next->where($id, '>', $options['id'])
            ->orderBy($id, 'asc')
            ->limit(1)->value($id);
        }

        return $page;
    }
}