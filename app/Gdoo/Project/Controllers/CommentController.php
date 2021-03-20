<?php namespace Gdoo\Project\Controllers;

use Illuminate\Http\Request;

use DB;
use Validator;
use Auth;
use Session;

use Gdoo\User\Models\User;
use Gdoo\Project\Models\Project;
use Gdoo\Project\Models\Task;
use Gdoo\Project\Models\Log;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\AttachmentService;

class CommentController extends DefaultController
{
    public $permission = [];
    
    // 添加评论
    public function add(Request $request)
    {
        if ($request->method() == 'POST') {
            $gets = $request->input();

            if ($gets['content'] == '') {
                return $this->json('评论内容必须填写。');
            }

            $gets['user'] = auth()->user()->name;
            $gets['type'] = 'comment';

            $log = new Log();
            $log->fill($gets);
            $log->save();

            $log = Log::find($log->id);
            $log->created_at = format_datetime($log->created_at);

            return $this->json($log, true);
        }

        $task_id = $request->input('task_id');

        $attachment = AttachmentService::edit('', 'project_task_log', 'attachment', 'project');
        return $this->render([
            'attachment' => $attachment,
            'task_id' => $task_id,
        ]);
    }

    // 编辑评论
    public function edit(Request $request)
    {
        if ($request->method() == 'POST') {
            $gets = $request->input();

            if ($gets['content'] == '') {
                return $this->json('评论内容必须填写。');
            }

            $item = Log::find($gets['id']);
            $item->fill($gets);
            $item->save();

            return $this->json('恭喜你，编辑评论成功。', true);
        }

        $id  = $request->input('id');
        $log = Log::find($id);

        $attachment = AttachmentService::edit($log['attachment'], 'project_task_log', 'attachment', 'project');
        return $this->render([
            'attachment' => $attachment,
            'log' => $log,
        ]);
    }

    // 删除评论
    public function delete(Request $request)
    {
        if ($request->method() == 'POST') {
            $id = $request->input('id');
            $id = array_filter((array)$id);

            if (empty($id)) {
                return $this->json('请先选择数据。');
            }

            $rows = Log::whereIn('id', $id)->get();
            Log::whereIn('id', $id)->delete();

            foreach($rows as $row) {
                AttachmentService::remove($row['attachment']);
            }

            return $this->json('恭喜你，删除评论成功。', true);
        }
    }
}
