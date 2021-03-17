<?php namespace Gdoo\Project\Controllers;

use Illuminate\Http\Request;

use DB;
use Validator;
use Auth;

use Gdoo\User\Models\User;
use Gdoo\Project\Models\Project;
use Gdoo\Project\Models\Task;
use Gdoo\Project\Models\Log;
use Gdoo\Index\Models\Attachment;
use Gdoo\Index\Models\Access;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\AttachmentService;

class ProjectController extends DefaultController
{
    public $permission = [];
    
    public function index()
    {
        $search = search_form([
            'referer' => 1,
            'status' => 0
        ], [
            ['text', 'project.title', '名称'],
            ['text', 'project.user_id', '拥有者'],
        ]);

        $query = $search['query'];

        $model = Project::with(['tasks' => function ($q) {
            $q->where('user_id', auth()->id())->whereRaw('isnull(progress, 0) < 1');
        }])->where('status', $query['status'])
        ->orderBy('id', 'desc')
        ->select(['*']);

        foreach ($search['where'] as $where) {
            if ($where['active']) {
                $model->search($where);
            }
        }

        $auth_id = auth()->id();

        // 不是全部权限
        if ($this->access['index'] < 4) {
            $sql = "(permission = 0
            or (permission = 1 
            and (
                exists (
                select 1 from project_task
                left join project_task_user on project_task.id = project_task_user.task_id 
                where project_task.project_id = project.id 
                and (project.user_id = ".$auth_id." or project_task.user_id = ".$auth_id." or project_task_user.user_id = ".$auth_id."))))
            )";
            $model->whereRaw($sql);
        }

        $rows = $model->paginate()->appends($query);

        $tabs = [
            'name' => 'status',
            'items' => Project::$tabs
        ];

        return $this->display([
            'auth_id' => $auth_id,
            'rows' => $rows,
            'search' => $search,
            'tabs' => $tabs,
        ]);
    }

    // 项目详情
    public function show(Request $request)
    {
        return $this->display([]);
    }

    // 添加项目
    public function add(Request $request)
    {
        if ($request->method() == 'POST') {
            $gets = $request->input();

            if ($gets['name'] == '') {
                return $this->error('项目名称必须填写。');
            }

            if ($gets['user_id'] == '') {
                return $this->error('项目拥有者填写。');
            }

            $task = new Project();
            $task->fill($gets);
            $task->save();

            return $this->success('index', '恭喜你，添加项目成功。');
        }
        return $this->display([]);
    }

    // 编辑项目
    public function edit(Request $request)
    {
        if ($request->method() == 'POST') {
            $gets = $request->input();

            if ($gets['name'] == '') {
                return $this->error('项目名称必须填写。');
            }

            if ($gets['user_id'] == '') {
                return $this->error('项目拥有者填写。');
            }

            $task = Project::find($gets['id']);
            $task->fill($gets);
            $task->save();

            return $this->success('index', '恭喜你，编辑项目成功。');
        }

        $id = $request->input('id');
        $project = Project::find($id);

        return $this->display([
            'project' => $project,
        ]);
    }

    // 删除项目
    public function delete(Request $request)
    {
        $id = $request->input('id');
        $id = array_filter((array)$id);

        if (empty($id)) {
            return $this->error('请先选择数据。');
        }

        $tasks = Task::whereIn('project_id', $id)->get();
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

        // 删除任务
        Project::whereIn('id', $id)->delete();

        return $this->success('index', '恭喜你，操作成功。');
    }
}
