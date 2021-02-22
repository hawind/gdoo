<?php namespace Gdoo\Workflow\Controllers;

use Auth;
use Request;
use DB;
use Paginator;

use App\Support\Dialog;

use Gdoo\User\Models\User;
use Gdoo\Workflow\Models\Workflow;
use Gdoo\Workflow\Models\WorkflowCategory;

use Gdoo\Index\Controllers\DefaultController;

class WorkflowController extends DefaultController
{
    public $permission = [
        'print',
        'step',
        'check',
        'next',
        'last',
        'draft',
        'state',
        'query',
        'export',
        'timeout',
        'end',
        'dialog',
        'correct',
        'log',
    ];

    // 已办工作列表
    public function indexAction($flag = 'index')
    {
        $page = Request::get('page', 1);

        $search = search_form([
            'category' => '',
            'work'     => '',
            'step'     => '',
            'option'   => 'todo',
            'done'     => '',
            'referer'  => 1,
            'limit'    => 25,
        ], [
            ['text','wp.title','工作主题'],
            ['category','w.category_id','工作类别'],
            ['text','wp.id','工作流程号(ID)'],
            ['department','user.department_id','发起人部门'],
            ['text','wp.name','工作文号'],
            ['text','wp.work_id','流程编号'],
            ['text','user.name','工作发起人'],
            ['text','handle_user','当前主办人'],
            
        ]);
        $query = $search['query'];

        $model = DB::table('work_process as wp')
        ->LeftJoin('work_process_data as wpd', 'wpd.process_id', '=', 'wp.id')
        ->LeftJoin('work as w', 'w.id', '=', 'wp.work_id')
        ->orderBy('wp.id', 'desc');

        // 我的工作
        if ($flag == 'index') {
            // 待办
            if ($query['option'] == 'todo') {
                $model->where('wpd.user_id', Auth::id())
                ->where('wpd.flag', 1)
                ->where('wp.state', 1);
            }

            // 已办
            if ($query['option'] == 'trans') {
                $model->where('wpd.user_id', Auth::id())
                ->where('wpd.flag', 2)
                ->where('wp.state', 1);
            }

            // 完成
            if ($query['option'] == 'done') {
                $model->where('wpd.user_id', Auth::id());
            }

            // 是否完成
            if ($query['option'] == 'done') {
                $model->whereRaw('wp.end_user_id > 0');
            } else {
                $model->whereRaw('wp.end_user_id = 0');
            }
        }

        // 回收站
        if ($flag == 'trash') {
            // 数据访问权限
            if (User::authoriseAccess('trash')) {
                $model->whereRaw('(wp.start_user_id=? OR wpd.user_id=?)', [Auth::id(), Auth::id()]);
            }
            $model->where('wp.state', 0);
        }

        // 监控工作
        if ($flag == 'monitor') {
            if ($query['done'] == 1) {
                $model->whereRaw('wp.end_user_id > 0');
            } else {
                $model->whereRaw('wp.end_user_id = 0');
            }
            $model->where('wp.state', 1);
        }

        $handle_user = false;
        foreach ($search['where'] as $where) {
            if ($where['active']) {
                if ($where['field'] == 'w.category_id') {
                    if ($where['search'][0]) {
                        $model->where('w.category_id', $where['search'][0]);
                    }
                    if ($where['search'][1]) {
                        $model->where('wp.work_id', $where['search'][1]);
                    }
                    if ($where['search'][2]) {
                        $model->where('wpd.step_id', $where['search'][2]);
                    }
                } else {
                    if ($where['field'] == 'handle_user') {
                        $handle_user = true;
                        $where['field'] = 'user.name';
                    }
                    $model->search($where);
                }
            }
        }

        // 主办人
        if ($handle_user) {
            // 搜索当前主办人时匹配最大流水号
            $model->whereRaw('wp.number = wpd.number')
            ->LeftJoin('user', 'user.id', '=', 'wpd.user_id');
        } else {
            $model->LeftJoin('user', 'user.id', '=', 'wp.start_user_id');
        }

        $model->leftJoin(DB::raw('
            (select top 1 a.id, a.title as name, b.process_id, b.add_time, b.user_id, b.number as serial, a.number, a.timeout
            from work_step as a
            left join work_process_data as b on a.id = b.step_id order by b.id desc) as sp'
        ), 'sp.process_id', '=', 'wpd.process_id');

        /*
        $step = DB::table('work_step as ws')
        ->LeftJoin('work_process_data as wpd', 'ws.id', '=', 'wpd.step_id')
        ->where('wpd.process_id', $row['process_id'])
        ->orderBy('wpd.id', 'desc')
        ->first([)
        '))
        */

        // 数据总计
        //$total = $model->distinct('wp.id')->count('wp.id');

        $rows = $model->paginate($query['limit'])->appends($query);

        /*
        $rows = $model->forPage($page, $query['limit'])
        ->selectRaw('distinct(wp.id), wp.*, wpd.process_id')
        ->get();

        $rows = Paginator::make($rows, $total)->appends($query);

        if ($rows->count()) {
            foreach ($rows as $key => $row) {
                $step = DB::table('work_step as ws')
                ->LeftJoin('work_process_data as wpd', 'ws.id', '=', 'wpd.step_id')
                ->where('wpd.process_id', $row['process_id'])
                ->orderBy('wpd.id', 'desc')
                ->first(['ws.id', 'ws.title as name', 'wpd.add_time', 'wpd.user_id', 'wpd.number as serial', 'ws.number', 'ws.timeout']);

                $row['step'] = $step;
                $rows->put($key, $row);
            }
        }
        */

        $categorys = WorkflowCategory::where('status', 1)->orderBy('sort', 'asc')->get(['id','title']);
        if ($q_work_id) {
            $steps = DB::table('work_step')->where('work_id', $q_work_id)->get();
        }

        // 返回json
        if (Request::wantsJson()) {
            return response()->json($rows);
        }

        return $this->display([
            'rows' => $rows,
            'steps' => $steps,
            'categorys' => $categorys,
            'works' => $works,
            'search' => $search,
            'options' => Workflow::$_options,
        ], $tpl);
    }

    // 监控流程
    public function monitorAction()
    {
        return $this->indexAction('monitor');
    }

    // 修正流程进程
    public function correctAction()
    {
        $gets = Request::all();

        if (Request::method() == 'POST') {
            return $this->json('流程纠正成功。', true);
        }

        return $this->render([
            'rows' => $rows,
        ]);
    }

    // 回收站
    public function trashAction()
    {
        return $this->indexAction('trash');
    }

    // 查询流程
    public function queryAction()
    {
        $id = Request::get('id');

        if ($id) {
            $fields = Workflow::getFormData($id);

            // 获取流程主表
            $work = DB::table('work')->where('id', $id)->first();

            $columns = [
                'run_id' => [
                    'field'  => 'p.id',
                    'name'   => '流水号',
                    'format' => 'number'
                ],
                'run_name' => [
                    'field'  => 'p.name',
                    'name'   => '名称/文号',
                    'format' => 'text'
                ],
                'run_status' => [
                    'field'  => 'p.end_user_id',
                    'name'   => '流程状态',
                    'format' => 'number'
                ],
                'run_user_id' => [
                    'field'  => 'p.start_user_id',
                    'name'   => '流程发起人',
                    'format' => 'text'
                ],
                'run_date' => [
                    'field'  => 'p.start_time',
                    'name'   => '流程开始日期',
                    'format' => 'date'
                ],
                'run_time' => [
                    'field'  => 'p.start_time',
                    'name'   => '流程开始时间',
                    'format' => 'datetime'
                ]
            ];

            return $this->display([
                'work_id' => $id,
                'work'    => $work,
                'columns' => $columns,
                'fields'  => $fields,
            ], 'query_form');
        } else {
            $works = Workflow::permission('query_id')->get();

            $rows = $categorys = [];
            foreach ($works as $rowId => $row) {
                $rows[$row->category_id][$rowId] = $row;
            }

            $j = 0;

            $_categorys = WorkflowCategory::get();

            foreach ($_categorys as $i => $category) {
                if ($i % 3 == false) {
                    $j++;
                }

                $categorys[$j][] = $category;
            }

            return $this->display([
                'rows'      => $rows,
                'categorys' => $categorys,
            ], 'query_list');
        }
    }

    // 导出流程
    public function exportAction()
    {
        $work_id = Request::get('work_id');

        if (Request::method() == 'POST') {
            $gets = Request::all();

            $fields = Workflow::getFormData($work_id);

            $query = DB::table('work_data_'.$work_id.' as d')
            ->LeftJoin('work_process as p', 'p.id', '=', 'd.process_id')
            ->LeftJoin('work_process_data as pd', 'pd.process_id', '=', 'p.id');

            // 流程开始日期
            $date_a = $gets['date_start_a'];
            $date_b = $gets['date_start_b'];

            if ($date_a && $date_b) {
                $query->whereBetween('p.start_time', [strtotime($date_a), strtotime($date_b)]);
            } elseif ($date_a) {
                $query->where('p.start_time', '>', strtotime($date_a));
            } elseif ($date_b) {
                $query->where('p.start_time', '<', strtotime($date_b));
            }

            /*
            // 流程结束日期
            $date_a = $gets['date_end_a'];
            $date_b = $gets['date_end_b'];

            if ($date_a && $date_b) {
                $query->whereBetween('p.end_time', [strtotime($date_a), strtotime($date_b)]);
            } elseif ($date_a) {
                $query->where('p.end_time', '>', strtotime($date_a));
            } elseif ($date_b) {
                $query->where('p.end_time', '<', strtotime($date_b));
            }
            */

            // 开始用户
            if ($gets['start_user_id']) {
                $query->where('p.start_user_id', $gets['start_user_id']);
            }

            // 工作流文号
            if ($gets['run_name']) {
                $query->where('p.name', 'like', '%'.$gets['run_name'].'%');
            }

            // 工作状态
            if (is_numeric($gets['status'])) {
                if ($gets['status'] == 0) {
                    $query->where('p.end_user_id', 0);
                } else {
                    $query->where('p.end_user_id', '>', 0);
                }
            }

            // 查询范围
            if (is_numeric($gets['user_type'])) {
                if ($gets['user_type'] == 1) {
                    $query->where('p.start_user_id', Auth::id());
                }
                if ($gets['user_type'] == 2) {
                    $query->where('pd.user_id', Auth::id());
                }
            }

            $selects = $xls = [];
            $selects[$gets['group_by']] = $gets['group_by'];
            $selects[$gets['order_by']] = $gets['order_by'];

            foreach ($gets['columns'] as $_columns) {
                if ($_columns['field']) {
                    $field           = $_columns['field'];
                    $columns[$field] = $_columns['name'];

                    // 去掉表前缀
                    $_field = explode('.', $field);
                          
                    if ($_columns['total']) {
                        $selects[$field] = 'sum('.$field.') as '.$_field[1].'_total';
                        $name = $_field[1].'_total';
                    } else {
                        $selects[$field] = $field;
                        $name = $_field[1];
                    }

                    // 子表显示字段
                    if ($_columns['field1']) {
                        $fields[$name]['show'] = $_columns['field1'];
                    }

                    $xls[] = ['label' => $_columns['name'], 'name' => $name];
                }
            }

            // 分组统计
            $query->groupBy($gets['group_by'])
            ->orderBy($gets['order_by'], $gets['sort_by']);

            // 去掉条件模板
            array_shift($gets['conditions']);

            $count = count($gets['conditions']);

            $conditions = $values = [];

            if ($gets['conditions']) {
                foreach ($gets['conditions'] as $key => $condition) {
                    // 数组最后一个去掉逻辑
                    if ($key + 1 == $count) {
                        unset($condition['logic']);
                    }

                    $values[] = $condition['value'];
                    $condition['value'] = '?';

                    $conditions[] = join(' ', $condition);
                }
                $where = join(' ', $conditions);

                $query->whereRaw($where, $values);
            }

            $selects = array_unique($selects);
            $query->selectRaw(join(',', $selects));
            $rows = $query->get();

            $haeds = [];
            foreach ($fields as $key => $field) {
                if ($field['class'] == 'listview') {
                    $haed = [];

                    $show = $field['show'];
                    
                    $tr = explode('`', $field['lv_title']);
                    foreach ($tr as $i => $td) {
                        if ($td && isset($show[$i])) {
                            $haed[] = $td;
                        }
                    }
                    $haeds[$key] = $gets['action'] == 'excel' ? join(',', $haed) : '<tr><th nowrap="nowrap">'.join('</th><th nowrap="nowrap">', $haed).'</th></tr>';
                }
            }

            foreach ($rows as $key => &$row) {
                $row['start_time']    = format_datetime($row['start_time']);
                $row['start_user_id'] = get_user($row['start_user_id'], 'name', 0);
                $row['end_user_id']   = $row['end_user_id'] == 0 ? '执行中' : '已结束';

                foreach ($haeds as $key => $tr) {
                    if (isset($row[$key])) {
                        $table = $gets['action'] == 'excel' ? [] : [$tr];
                        
                        $t = json_decode($row[$key], true);

                        $show = $fields[$key]['show'];

                        if ($t) {
                            foreach ($t as $tr) {
                                $tt = [];
                                foreach ($tr as $i => $td) {
                                    if ($td && isset($show[$i])) {
                                        $tt[] = $td;
                                    }
                                }
                                $table[] = $gets['action'] == 'excel' ? join(',', $tt) : '<tr><td>'.join('</td><td>', $tt).'</td></tr>';
                            }
                        }
                        $row[$key] = $gets['action'] == 'excel' ? join("\n", $table) : '<table class="table table-condensed table-bordered table-hover">'. join('', $table) .'</table>';
                    }
                }
            }

            if ($gets['action'] == 'excel') {
                writeExcel($xls, $rows, $gets['name'].date('-Y-m-d'));
            }

            return $this->display([
                'rows'    => $rows,
                'columns' => $columns,
            ]);
        }
    }

    // 超时统计
    public function timeoutAction()
    {
        $query = [
            'option' => 'step',
            'flag'   => 1,
        ];
        foreach ($query as $k => $v) {
            $query[$k] = Request::get($k, $v);
        }

        // 超时步骤
        if ($query['option'] == 'step') {
            $model = DB::table('work_process as wp')
            ->LeftJoin('work_process_data as wpd', 'wpd.process_id', '=', 'wp.id')
            ->LeftJoin('work as w', 'w.id', '=', 'wp.work_id')
            ->LeftJoin('work_step as ws', 'ws.id', '=', 'wpd.step_id')
            ->where('wp.end_user_id', 0)
            ->where('ws.number', '>', 1)
            ->where('ws.timeout', '>', 0)
            ->where('wpd.flag', $query['flag'])
            ->orderBy('wp.id', 'desc');

            $model->selectRaw('wp.*, wpd.process_id, wpd.add_time as trans_at, wpd.deliver_time as deliver_at, wpd.user_id, wpd.flag, wpd.number as step_number, ws.title as step_name, ws.timeout as step_timeout');

            // 办理中
            if ($query['flag'] == 1) {
                $model->addSelect(DB::raw('UNIX_TIMESTAMP() - ((ws.timeout * 3600) + wpd.add_time) as timeout_diff'))
                ->whereRaw('UNIX_TIMESTAMP() - ((ws.timeout * 3600) + wpd.add_time) > 0');
            }

            if ($query['flag'] == 2) {
                $model->addSelect(DB::raw('wpd.deliver_time - ((ws.timeout * 3600) + wpd.add_time) as timeout_diff'))
                ->whereRaw('wpd.deliver_time - ((ws.timeout * 3600) + wpd.add_time) > 0');
            }

            $rows = $model->paginate();
        }

        // 超时统计
        if ($query['option'] == 'count') {
            $rows_1 = DB::table('work_process_data as wpd')
            ->LeftJoin('work_step as ws', 'ws.id', '=', 'wpd.step_id')
            ->where('wpd.number', '>', 1)
            ->where('wpd.flag', 1)
            ->groupBy('wpd.user_id')
            ->selectRaw('
                count(wpd.id) as count,
                wpd.user_id,
                wpd.flag,
                ws.timeout,
                UNIX_TIMESTAMP() - ((ws.timeout * 3600) + wpd.add_time) as timeout_1
            ')->get();

            $rows_2 = DB::table('work_process_data as wpd')
            ->LeftJoin('work_step as ws', 'ws.id', '=', 'wpd.step_id')
            ->where('wpd.number', '>', 1)
            ->where('wpd.flag', 2)
            ->groupBy('wpd.user_id')
            ->selectRaw('
                count(wpd.id) as count,
                wpd.user_id,
                wpd.flag,
                wpd.deliver_time - ((ws.timeout * 3600) + wpd.add_time) as timeout_2
            ')->get();

            $rows = [];

            // 办理中
            foreach ($rows_1 as $row) {
                $user_id = $row['user_id'];
                $rows[$user_id]['count'] += $row['count'];
                if ($row['timeout']) {
                    $rows[$user_id]['timeout_1'] += $row['count'];
                }
            }

            // 已办理
            foreach ($rows_2 as $row) {
                $user_id = $row['user_id'];
                $rows[$user_id]['count'] += $row['count'];
                if ($row['timeout']) {
                    $rows[$user_id]['timeout_2'] += $row['count'];
                }
            }
        }

        return $this->display([
            'query' => $query,
            'rows'  => $rows,
        ], 'timeout_'. $query['option']);
    }

    // 可办理工作列表
    public function listAction()
    {
        $works = Workflow::permission('sponsor_id')->get();

        $rows = $categorys = [];
        foreach ($works as $rowId => $row) {
            $rows[$row->category_id][] = $row;
        }

        $categorys = WorkflowCategory::orderBy('sort', 'ASC')->get();

        $client = Request::get('client');
        if ($client == 'app') {
            $tpl = 'mobile/list';
        } else {
            $tpl = 'list';
        }

        return $this->display([
            'rows'      => $rows,
            'categorys' => $categorys,
        ], $tpl);
    }

    // 新建工作表单
    public function addAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();

            if (empty($gets['title'])) {
                return $this->json('工作主题必须填写。');
            }

            // 获取第一步骤编号
            $step = DB::table('work_step')->whereRaw('work_id=? and number=1', [$gets['work_id']])->first();

            if (empty($step)) {
                return $this->json('工作第一步骤不存在。');
            }

            // 写入运行实例
            $gets['start_user_id'] = Auth::id();
            $gets['start_time'] = time();

            $process_id = DB::table('work_process')->insertGetid($gets);

            // 写入运行实例第一步
            DB::table('work_process_data')->insert([
                'process_id' => $process_id,
                'step_id' => $step['id'],
                'user_id' => Auth::id(),
                'number' => 1,
            ]);

            // 处理工作表建立和更新
            Workflow::updateTable($gets['work_id']);

            // 建立工作
            DB::table('work_data_'.$gets['work_id'])->insert([
                'process_id' => $process_id,
                'add_user_id' => Auth::id(),
                'add_time' => time(),
            ]);

            // 操作日志
            // action_log('work_process', $process_id, 'workflow/workflow/view', 0, $gets['name']);

            return $this->json(['process_id' => $process_id], true);
        }

        $id = (int)Request::get('id');
        $row = DB::table('work')->where('id', $id)->first();

        return $this->render([
            'row' => $row,
        ]);
    }

    /**
     * 办理工作表单
     */
    public function editAction()
    {
        $process_id = (int)Request::get('process_id');

        // 当前主办数据
        $process = DB::table('work_process as p')
        ->LeftJoin('work_process_data as d', 'p.id', '=', 'd.process_id')
        ->where('p.id', $process_id)
        ->where('d.user_id', Auth::id())
        ->orderBy('d.number', 'desc')
        ->first(['p.*', 'd.option', 'd.step_id', 'd.id as data_id']);

        // 工作流主数据
        $work = DB::table('work as w')
        ->LeftJoin('work_step as s', 'w.id', '=', 's.work_id')
        ->where('s.id', $process['step_id'])
        ->selectRaw('w.template_short,w.title work_title,w.id work_id,w.type work_type,s.id step_id,s.number step_number,s.field field_write,s.field_secret,s.field_auto,s.field_check,s.last,s.print')
        ->first();

        $data = DB::table('work_data_'.$work['work_id'])
        ->where('process_id', $process_id)
        ->first();

        $work['items']   = $data;
        $work['process'] = $process;

        // 主办人可编辑表单
        $work['opflag'] = $process['option'];

        $workFlow = [
            'workId'     => $work['work_id'],
            'workType'   => $work['work_type'],
            'stepNumber' => $work['step_number'],
            'stepId'     => $work['step_id'],
            'dataId'     => $process['data_id'],
        ];

        // 处理工作表建立和更新
        Workflow::updateTable($work['work_id']);

        // 编译表单
        $form = Workflow::parseForm($work['template_short'], $work);

        // 公共附件编辑
        $attach = attachment_edit('work_attachment', $data['attachment'], 'workflow');

        // 公共附件查看权限
        $attach['auth'] = ['view' => 0,'add' => 0];
        if (strpos($work['field_secret'], '[attach@]') === false) {
            $attach['auth']['view'] = 1;
        }

        // 公共附件添加权限
        if (strpos($work['field_write'], '[attach@]') !== false) {
            $attach['auth']['add'] = 1;
        }

        $work['js'] = json_encode($workFlow);

        $client = Request::get('client');
        if ($client == 'app') {
            $tpl = 'mobile/edit';
        } else {
            $tpl = 'edit';
        }

        return $this->display([
            'attach'   => $attach,
            'work'     => $work,
            'process'  => $process,
            'template' => $form['template'],
            'jsonload' => $form['jsonload'],
            'js'       => $form['js'],
        ], $tpl);
    }

    // 查看表单
    public function viewAction()
    {
        $process_id = (int)Request::get('process_id');

        // 当前办理数据
        $model = DB::table('work_process as p')
        ->LeftJoin('work_process_data as d', 'p.id', '=', 'd.process_id')
        ->orderBy('d.number', 'desc')
        ->where('p.id', $process_id);

        // 获取主办数据
        $user = DB::table('work_process_data')
        ->where('user_id', Auth::id())
        ->where('process_id', $process_id)
        ->first();

        // 有主办数据
        if ($user) {
            $model->where('d.user_id', Auth::id());
        }

        $process = $model->first(['p.*', 'd.step_id', 'd.id as data_id']);

        // 工作流主数据
        $work = DB::table('work as w')
        ->LeftJoin('work_step as s', 'w.id', '=', 's.work_id')
        ->where('s.id', $process['step_id'])
        ->selectRaw('w.template_short,w.title work_title,w.id work_id,w.type work_type,s.id step_id,s.number step_number,s.field field_write,s.field_secret,s.field_auto,s.print')
        ->first();

        $data = DB::table('work_data_'.$work['work_id'])
        ->where('process_id', $process_id)
        ->first();

        $work['items'] = $data;

        $workFlow = [
            'workId'     => $work['work_id'],
            'workType'   => $work['work_type'],
            'stepNumber' => $work['step_number'],
            'stepId'     => $work['step_id'],
            'dataId'     => $process['data_id'],
        ];

        // 编译表单
        $form = Workflow::parseForm($work['template_short'], $work);

        $attach = attachment_view('work_attachment', $data['attachment']);
        $attach['queue'] = $attach['view'];
        $attach['auth'] = ['view' => 0,'add' => 0];

        if (strpos($work['field_secret'], '[attach@]') === false) {
            $attach['auth']['view'] = 1;
        }

        $work['js'] = json_encode($workFlow);

        $client = Request::get('client');
        if ($client == 'app') {
            $tpl = 'mobile/view';
        } else {
            $tpl = 'view';
        }

        return $this->display([
            'attach'   => $attach,
            'work'     => $work,
            'process'  => $process,
            'template' => $form['template'],
            'jsonload' => $form['jsonload'],
            'js'       => $form['js'],
        ], $tpl);
    }

    // 打印
    public function printAction()
    {
        $process_id = (int)Request::get('process_id');

        // 当前办理数据
        $model = DB::table('work_process as p')
        ->LeftJoin('work_process_data as d', 'p.id', '=', 'd.process_id')
        ->orderBy('d.number', 'desc')
        ->where('p.id', $process_id);

        // 获取主办数据
        $user = DB::table('work_process_data')
        ->where('user_id', Auth::id())
        ->where('process_id', $process_id)
        ->first(['user_id']);

        if ($user) {
            $model->where('d.user_id', Auth::id());
        }

        $process = $model->first(['p.*', 'd.step_id', 'd.id as data_id']);

        // 工作主数据
        $work = DB::table('work as w')
        ->LeftJoin('work_step as s', 'w.id', '=', 's.work_id')
        ->where('s.id', $process['step_id'])
        ->selectRaw('w.template_short,w.title work_title,w.id work_id,w.type work_type,s.id step_id,s.number step_number,s.field field_write,s.field_secret,s.field_auto,s.print')
        ->first();

        // 工作数据
        $data = DB::table('work_data_'.$work['work_id'])
        ->where('process_id', $process_id)
        ->first();

        $work['items'] = $data;

        // 打印标识
        $work['printflag'] = true;

        $workFlow = [
            'workId'     => $work['work_id'],
            'workType'   => $work['work_type'],
            'stepNumber' => $work['step_number'],
            'stepId'     => $work['step_id'],
            'dataId'     => $process['data_id'],
        ];

        // 编译表单
        $form = Workflow::parseForm($work['template_short'], $work);

        $work['js'] = json_encode($workFlow);

        $this->layout = 'layouts.empty';
        return $this->display([
            'attach'   => $attach,
            'work'     => $work,
            'process'  => $process,
            'template' => $form['template'],
            'jsonload' => $form['jsonload'],
            'js'       => $form['js'],
        ]);
    }

    /**
     * 检查工作表单必填、转入条件、经办人
     */
    public function checkAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();

            // 获取表单缓存
            $work_form_data = Workflow::getFormData($gets['work_id']);

            // 获取步骤信息
            $step = DB::table('work_step')->where('id', $gets['step_id'])->first();

            // 组合表单名称和表名
            foreach ($work_form_data as $key => $row) {
                $form_data[$row['title']] = $gets[$key];
            }

            // 检查必填字段
            if ($step['field_check']) {
                $field_check = explode(',', $step['field_check']);

                $regular = config('default.regular');

                foreach ($field_check as $check) {
                    list($key, $value) = explode('=', $check);

                    if($value == '') {
                        continue;
                    }

                    // 如果校验字段不存在则跳过
                    if (!isset($form_data[$key])) {
                        continue;
                    }

                    // 检查是否为空
                    if ($value == 'SYS_NOT_NULL') {
                        if (empty($form_data[$key])) {
                            return $this->json($key.$regular[$value]['title']);
                        }
                    } else {
                        // 检查为空后的其他正则
                        if (!preg_match($regular[$value]['regex'], $form_data[$key])) {
                            return $this->json($key.$regular[$value]['title']);
                        }
                    }
                }
            }

            // 结束流程标志
            if ($step['type'] == 3) {
                $json['step_id'] = '-1';
            } else {
                // 取得可以转入的步骤
                $steps = Workflow::checkCondition($gets);
                if (is_array($steps)) {
                    foreach ($steps as $row) {
                        $json['step_id'][] = $row['id'];
                    }
                    $json['step_id'] = join(',', (array)$json['step_id']);
                }
            }
            return $this->json($json, true);
        }
    }

    // 保存工作表单草稿
    public function draftAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();

            $data = Workflow::saveForm($gets, true);
            
            $table = 'work_data_'.$gets['work_id'];

            DB::table($table)->where('process_id', $gets['process_id'])->update($data);

            return $this->json('草稿保存成功。', true);
        }
    }

    // 获取工作步骤
    public function stepAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();

            $gets['step_id'] = $gets['next_step_id'];

            if ($gets['step_id'] > 0) {
                // 退回上一步
                if ($gets['step_type'] == 'last') {
                    $data = DB::table('work_process_data')
                    ->where('process_id', $gets['process_id'])
                    ->where('step_id', $gets['next_step_id'])
                    ->orderBy('process_id', 'asc')
                    ->get();

                    $step['select_user_id'] = $data[0]['user_id'];
                    $step['select_user_lock'] = 1;
                }
                // 转交下一步
                elseif ($gets['step_type'] == 'next') {
                    // 取得下一步骤办理人
                    $step = Workflow::getSelectUser($gets);
                }

                $html  = '<div class="form-group"><label class="col-sm-2 control-label">主办人</label><div class="col-sm-10">';
                $html .= Dialog::user('user', 'next_user_id', $step['select_user_id'], 0, $step['select_user_lock']);
                $html .= '</div></div>';

                // 转交下一步才显示会签人
                if ($gets['step_type'] == 'next') {
                    $html .= '<div class="form-group"><label class="col-sm-2 control-label">会签人</label><div class="col-sm-10">';
                    $html .= Dialog::user('user', 'next_user_sign', $step['select_user_sign'], 1, $step['select_user_lock']);
                    $html .= '</div></div>';
                }
                return $this->json($html);
            }
            return $this->json('');
        }
    }

    // 回退工作
    public function lastAction()
    {
        // 写入转交数据到下一步
        if (Request::method() == 'POST') {
            $gets = Request::all();

            $res = Workflow::nextStep($gets);
            if ($res === true) {
                Workflow::notification($gets);
                return $this->json(url_referer('index'), true);
            } else {
                return $this->json($res);
            }
        }

        $work_id = (int)Request::get('work_id');
        $step_id = (int)Request::get('step_id');

        $rows = DB::table('work_step')
        ->whereRaw('FIND_IN_SET(?, `join`) and work_id=?', [$step_id, $work_id])
        ->get();

        return $this->render([
            'step_type' => 'last',
            'rows'      => $rows,
        ], 'next');
    }

    // 转交工作
    public function nextAction()
    {
        // 写入转交数据到下一步
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $res = Workflow::nextStep($gets);
            if ($res === true) {
                Workflow::notification($gets);
                return $this->json(url_referer('index'), true);
            } else {
                return $this->json($res);
            }
        }

        // 显示转交表单
        $step_id = Request::get('step_id');

        // 结束流程
        if ($step_id == '-1') {
            $rows[] = ['id' => '-1', 'title' => '结束流程'];
        }
        // 普通流程
        elseif ($step_id) {
            $_step_id = array_filter(explode(',', $step_id));
            $rows = DB::table('work_step')->whereIn('id', $_step_id)->get();
        }
        return $this->render([
            'step_type' => 'next',
            'rows'      => $rows,
        ]);
    }

    // 结束流程
    public function endAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            $ids = array_filter((array)$id);

            foreach ($ids as $id) {

                // 进入进程结束标志
                DB::table('work_process')->where('id', $id)->update([
                    'end_user_id' => Auth::id(),
                    'end_time'    => time(),
                ]);
            }
            return $this->back('结束操作成功。');
        }
    }

    // 流程记录
    public function logAction()
    {
        $process_id = Request::get('process_id');
        $rows = DB::table('work_process_data')
        ->leftJoin('work_step', 'work_step.id', '=', 'work_process_data.step_id')
        //->orderBy('work_process_data.number', 'desc')
        ->where('process_id', $process_id)->get(['work_process_data.*', 'work_step.title as step_name']);
        return $this->render([
            'rows' => $rows,
        ]);
    }

    // 放入回收站
    public function deleteAction()
    {
        if (Request::method() == 'POST') {
            $id  = Request::get('id');
            $ids = array_filter((array)$id);

            $status = (int)Request::get('status');

            foreach ($ids as $id) {
                $data['state'] = $status;
                DB::table('work_process')->where('id', $id)->update($data);
            }
            return $this->back('回收操作成功。');
        }
    }

    public function dialogAction()
    {
        $gets = Request::all();
        // 返回json
        if (Request::ajax()) {
            $rows = DB::table('work')->where('category_id', $gets['category_id'])->get(['id', 'title']);
            return $this->json($rows);
        }
    }

    // 销毁工作
    public function destroyAction()
    {
        if (Request::method() == 'POST') {
            $id = Request::get('id');
            $id = array_filter((array)$id);

            $rows = DB::table('work_process')->whereIn('id', $id)->get()->toArray();
            if ($rows) {
                foreach ($rows as $row) {

                    // 删除相关数据
                    DB::table('work_process')->where('id', $row['id'])->delete();
                    DB::table('work_process_data')->where('process_id', $row['id'])->delete();
                    DB::table('work_process_log')->where('process_id', $row['id'])->delete();

                    $model = DB::table('work_data_'.$row['work_id'])
                    ->where('process_id', $row['id']);

                    // 工作数据
                    $row = $model->first();

                    // 删除工作附件
                    attachment_delete('work_attachment', $row['attachment']);

                    // 删除工作数据
                    $model->delete();
                }
            }
            
            return $this->success('trash', '销毁操作成功。');
        }
    }
}
