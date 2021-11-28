<?php namespace Gdoo\Project\Controllers;

use Illuminate\Http\Request;

use DB;
use Validator;
use Auth;
use Session;

use Gdoo\User\Models\User;
use Gdoo\Project\Models\Project;
use Gdoo\Project\Models\Task;
use Gdoo\Project\Models\Item;
use Gdoo\Project\Models\Log;
use Gdoo\Index\Models\Attachment;

use Gdoo\Project\Services\TaskService;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\AttachmentService;
use Illuminate\Support\Arr;

class TaskController extends DefaultController
{
    public $permission = ['drag', 'sort'];
    
    public function index(Request $request)
    {
        $search = search_form([
            'advanced' => '',
            'project_id' => '',
            'tpl' => 'index',
        ], [
            ['form_type' => 'text', 'name' => '任务名称', 'field' => 'project_task.name', 'value' => '', 'options' => []],
            ['form_type' => 'text', 'name' => '执行者', 'field' => 'project_task.user_id', 'value' => '', 'options' => []],
        ], 'model');

        $header = [
            'master_name' => '项目任务',
            'simple_search_form' => 1,
            'table' => 'project_task',
            'master_table' => 'project_task',
        ];

        $header['buttons'] = [
            //['name' => '删除', 'icon' => 'fa-remove', 'action' => 'delete', 'display' => $this->access['delete']],
            //['name' => '导出', 'icon' => 'fa-share', 'action' => 'export', 'display' => 1],
        ];

        $header['search_form'] = $search;
        $query = $search['query'];

        if ($request->ajax() && $request->wantsJson()) {
            $tasks = TaskService::data($search);
            $_tasks = array_nest($tasks, 'name');
            $rows = [];
            foreach($_tasks as $_task) {
                $rows[] = $_task;
            }
            $json['data'] = $rows;
            return $json;
        }

        if ($request->ajax()) {
            $tasks = TaskService::data($search);
            return ['data' => $tasks];
        }
        
        $project = Project::find($query['project_id']);

        // 生成权限
        $user_id = auth()->id();
        $permission = [
            'add_item' => $project['user_id'] == $user_id,
            'add_task' => $project['user_id'] == $user_id,
        ];

        return $this->display([
            'header' => $header,
            'project' => $project,
            'permission' => $permission,

        ], 'index/'.$query['tpl']);
    }

    // 移动任务
    public function drag(Request $request)
    {
        $gets = $request->input();

        $task = Task::find($gets['id']);

        $task->start_at = strtotime($gets['start_date']);
        $task->end_at = strtotime($gets['end_date']);
        $task->progress = $gets['progress'];
        $task->save();
        
        return $this->json('恭喜您，任务移动成功。', true);
    }

    // 移动任务
    public function sort(Request $request)
    {
        $gets = $request->input();

        $task = Task::find($gets['id']);
        $task->parent_id = $gets['parent_id'];
        $task->save();

        $i = 0;
        foreach ($gets['sort'] as $id) {
            $task = Task::find($id);
            $task->sort = $i;
            $task->save();
            $i++;
        }
        return $this->json('恭喜您，任务移动成功。', true);
    }

    // 添加任务
    public function add(Request $request)
    {
        if ($request->method() == 'POST') {
            $gets = $request->input();

            if ($gets['name'] == '') {
                return $this->json('名称必须填写。');
            }

            if ($gets['start_at'] == '') {
                $gets['start_at'] = time();
            }

            $attachment = $gets['attachment'];
            $gets['attachment'] = join(',', (array)$attachment);

            $gets['start_at'] = strtotime($gets['start_at']);
            $gets['end_at'] = strtotime($gets['end_at']);

            $gets['user_id'] = $gets[$gets['type'].'_user_id'];

            $task = new Task();
            $task->fill($gets);
            $task->save();

            // 更新关系表
            $task->syncUsers($gets);

            // 附件发布
            AttachmentService::publish($attachment);

            if ($gets['is_item'] == '0') {
                $task = Task::find($task->id);
                $task->created_at = format_datetime($task->created_at);
                $task->user_name  = get_user($task->user_id, 'name', false);
                return $this->json($task, true);
            } else {
                return $this->json('恭喜你，添加任务成功。', true);
            }
        }

        $project_id = $request->input('project_id');
        $parent_id = $request->input('parent_id');
        $type = $request->input('type');

        $items = Task::where('project_id', $project_id)
        ->where('parent_id', 0)
        ->orderBy('id', 'desc')
        ->get();

        $tpl = $type == 'item' ? 'item/add' : 'add';

        $attachment = AttachmentService::edit('', 'project_task', 'attachment', 'project');
        return $this->render([
            'items' => $items,
            'attachment' => $attachment,
            'project_id' => $project_id,
            'parent_id' => $parent_id,
            'type' => $type,
        ], $tpl);
    }

    // 编辑任务
    public function edit(Request $request)
    {
        if ($request->method() == 'POST') {
            $gets = $request->input();

            if ($gets['name'] == '') {
                return $this->json('名称必须填写。');
            }

            $gets['progress'] = (int)$gets['progress'];

            $attachment = $gets['attachment'];
            $gets['attachment'] = join(',', (array)$attachment);
            
            $gets['start_at'] = strtotime($gets['start_at']);
            $gets['end_at'] = strtotime($gets['end_at']);

            $gets['user_id'] = $gets[$gets['type'].'_user_id'];
            
            $task = Task::find($gets['id']);
            $task->fill($gets);
            $task->save();

            // 更新关系表
            $task->syncUsers($gets);

            // 附件发布
            AttachmentService::publish($attachment);

            return $this->json('恭喜你，编辑任务成功。', true);
        }

        $id = $request->input('id');
        $type = $request->input('type');

        $task = Task::find($id);
        $task->users = $task->users()->pluck('user_id')->implode(',');

        $project = Project::find($task->project_id);

        $tasks = Task::where('project_task.parent_id', $task->id)
        ->leftJoin('user', 'user.id', '=', 'project_task.user_id')
        ->orderBy('project_task.id', 'desc')
        ->get(['project_task.*', 'user.avatar']);

        $items = Task::where('project_id', $task->project_id)
        ->where('parent_id', 0)
        ->orderBy('id', 'desc')
        ->get();
        
        $logs = Log::where('project_task_log.task_id', $id)
        ->leftJoin('user', 'user.id', '=', 'project_task_log.created_id')
        ->orderBy('project_task_log.id', 'desc')
        ->get(['project_task_log.*', 'user.avatar']);

        $auth_id = auth()->id();

        $permission = [
            'name' => 0,
            'status' => 0,
            'parent_id' => 0,
            'date' => 0,
            'user_id' => 0,
            'users' => 0,
            'remark' => 0,
            'attachment' => 0,
            'add-subtask' => 0,
            'add-comment' => 0,
        ];

        if ($project['user_id'] == $auth_id) {
            $permission = [
                'name' => 1,
                'status' => 1,
                'parent_id' => 1,
                'date' => 1,
                'user_id' => 1,
                'users' => 1,
                'remark' => 1,
                'attachment' => 1,
                'add-subtask' => 1,
                'add-comment' => 1,
            ];
        } elseif ($task['user_id'] == $auth_id) {
            $permission = [
                'name' => 1,
                'status' => 1,
                'parent_id' => 0,
                'date' => 0,
                'user_id' => 0,
                'users' => 1,
                'remark' => 1,
                'attachment' => 1,
                'add-subtask' => 0,
                'add-comment' => 1,
            ];
        } elseif (in_array($auth_id, (array)$task->users)) {
            $permission = [
                'name' => 0,
                'status' => 0,
                'parent_id' => 0,
                'date' => 0,
                'user_id' => 0,
                'users' => 0,
                'remark' => 0,
                'attachment' => 0,
                'add-subtask' => 0,
                'add-comment' => 1,
            ];
        }
        $tpl = $type == 'item' ? 'item/edit' : 'edit';

        $attachment = AttachmentService::edit($task['attachment'], 'project_task', 'attachment', 'project');
        return $this->render([
            'task' => $task,
            'logs' => $logs,
            'items' => $items,
            'tasks' => $tasks,
            'type' => $type,
            'attachment' => $attachment,
            'permission' => $permission,
        ], $tpl);
    }

    // 删除任务
    public function delete(Request $request)
    {
        if ($request->method() == 'POST') {
            $id = $request->input('id');
            $id = array_filter((array)$id);

            if (empty($id)) {
                return $this->json('请先选择数据。');
            }

            $tasks = Task::whereIn('id', $id)->get();

            foreach ($tasks as $task) {
                $logs = Log::where('task_id', $task->id)->get();
                foreach ($logs as $log) {
                    AttachmentService::remove($log->attachment);
                    $log->delete();
                }
                
                AttachmentService::remove($task->attachment);
                $task->users()->sync([]);
                $task->delete();
            }
            return $this->json('恭喜你，删除任务成功。', true);
        }
    }
}
