<?php namespace Gdoo\Project\Services;

use Arr;
use DB;
use Auth;
use Gdoo\Project\Models\Task;

class TaskService
{
    /**
     * 获取待办任务
     */
    public static function getBadge()
    {
        $rows = DB::table('project_task')
        ->where('user_id', Auth::id())
        ->whereRaw('isnull(progress, 0) < 1')
        ->get();
        $ret['total'] = sizeof($rows);
        $ret['data'] = $rows;
        return $ret;
    }

    // 读取数据
    public static function data($search)
    {
        $query = $search['query'];
        $user_id = auth()->id();

        $_items = Task::where('project_id', $query['project_id'])
        ->leftJoin('project', 'project.id', '=', 'project_task.project_id')
        ->where('parent_id', 0)
        ->orderBy('project_task.sort', 'asc')
        ->orderBy('project_task.id', 'asc')
        ->get(['project_task.*', 'project.user_id as project_user_id'])->toArray();

        $model = Task::with(['users' => function ($q) {
            $q->select(['user.id','user.name as user_name']);
        }]);

        $model->where('project_task.project_id', $query['project_id'])
        ->leftJoin('project', 'project.id', '=', 'project_task.project_id')
        ->leftJoin('user', 'user.id', '=', 'project_task.user_id')
        ->where('parent_id', '>', 0);
        
        foreach ($search['where'] as $where) {
            if ($where['active']) {
                $model->search($where);
            }
        }
        $_tasks = $model->select(['project_task.*','user.name as user_name', 'project.user_id as project_user_id'])
        ->orderBy('project_task.sort', 'asc')
        ->orderBy('project_task.id', 'asc')
        ->get()->toArray();

        foreach ($_items as $_item) {
            $project_user_id = 0;
            if ($_item['project_user_id'] == $user_id) {
                $project_user_id = 1;
            }
            
            $tasks[] = [
                'start_date' => '',
                'parent_id' => 0,
                'parent' => 0,
                'duration' => '',
                'loaded' => true,
                'expanded' => true,
                'id' => $_item['id'],
                'name' => $_item['name'],
                'type' => $_item['type'],
                'created_at' => '',
                'user_id' => 0,
                'user_name' => '',
                'open' => true,
                'option_edit' => $project_user_id,
                'option_delete' => $project_user_id,
                'dhm' => '',
            ];
        }

        foreach ($_tasks as $_task) {
            $project_user_id = $task_user_id = 0;

            if ($_task['user_id'] == $user_id) {
                $task_user_id = 1;
            }

            // 显示保存按钮
            if ($_task['project_user_id'] == $user_id) {
                $task_user_id = $project_user_id = 1;
            }

            $_task['option_edit'] = $task_user_id;
            $_task['option_delete'] = $project_user_id;

            $_task['start_date'] = date('Y-m-d', $_task['start_at']);
            $_task['name'] = $_task['name'];
            $_task['parent'] = $_task['parent_id'];
            $_task['users'] = join(',', Arr::pluck($_task['users'], 'user_name'));
            $_task['open'] = true;
            $_task['loaded'] = true;
            $_task['expanded'] = true;
            $_task['created_dt'] = format_datetime($_task['created_at']);
            $_task['start_dt'] = format_datetime($_task['start_at']);
            $_task['end_dt'] = format_datetime($_task['end_at']);

            if ($_task['start_at'] && $_task['end_at']) {
                $remain = remain_time($_task['start_at'], $_task['end_at'], '');
                $str = '';
                if ($remain->d) {
                    $str .= $remain->d.'天';
                }
                if ($remain->h) {
                    $str .= $remain->h.'小时';
                }
                if ($remain->i) {
                    $str .= $remain->i.'分钟';
                }
                $_task['duration_date'] = $str;
            }

            $_task['duration'] = ($_task['end_at'] - $_task['start_at']) / 86400;
            $_task['duration'] = $_task['duration'] > 0 ? $_task['duration'] : 1;

            $tasks[] = $_task;
        }
        return $tasks;
    }
}