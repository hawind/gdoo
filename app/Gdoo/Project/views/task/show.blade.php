<style>
.modal-body { overflow:hidden; }
</style>

<form method="post" enctype="multipart/form-data" id="mytaskform" name="mytaskform">
<div class="panel m-b-none">

    <table class="table table-form m-b-none">

        <tr>
            <td align="right" width="15%">名称</td>
            <td align="left">
                <input type="text" name="name" id="name" value="{{$task['name']}}" class="form-control input-sm">
            </td>
        </tr>

        <tr>
            <td align="right">列表</td>
            <td align="left">
                <select class="form-control input-inline input-sm" id="item_id" name="item_id">
                    <option value="1">测试日程1_</option>
                    <option value="2">abc123</option>
                    <option value="408">一般事件</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">执行者</td>
            <td align="left">
                {{App\Support\Dialog::user('user','user_id', $task['user_id'], 0, 0)}}
            </td>
        </tr>

        <tr>
            <td align="right">参与者</td>
            <td align="left">
                {{App\Support\Dialog::user('user', 'users', $task['users'], 1, 0)}}
            </td>
        </tr>
    
        <tr>
            <td align="right">时间</td>
            <td align="left">
                <input type="text" name="start_at" autocomplete="off" data-toggle="datetime" value="@datetime($task->start_at,time())" id="start_at" class="form-control input-sm input-inline"> 
                - 
                <input type="text" name="end_at" autocomplete="off" data-toggle="datetime" value="@datetime($task->end_at)" id="end_at" class="form-control input-sm input-inline">
            </td>
        </tr>

        <tr>
            <td align="right">备注</td>
            <td>
                <textarea class="form-control" type="text" name="purchase_plan[remark]" id="remark" placeholder="暂无备注"></textarea>
            </td>
        </tr>

        <tr>
            <td align="right">附件</td>
            <td align="left">
                {{attachment_uploader('attachment', $task['attachment'])}}
            </td>
        </tr>

        <tr>
            <td align="right">子任务</td>
            <td>
                <a href="#" class="option"><i class="fa fa-fw fa-plus"></i>添加子任务</a>
            </div>
            </td>
        </tr>

        <tr>
            <td align="right">评论</td>
            <td>
                <a href="#" class="option"><i class="fa fa-fw fa-plus"></i>添加评论</a>
                <!--
                <textarea class="form-control" rows="2" type="text" name="comment" id="comment" placeholder="内容"></textarea>
                {{attachment_uploader('comment_attachment', '', false)}}
                <a href="#" class="btn btn-sm btn-success"> 提交</a>
                -->
            </td>
        </tr>

        <tr>
            <td align="right">活动</td>
            <td>
                <div class="project-task-log">
                    @if($logs)
                    @foreach($logs as $log)
                        <p><span class="time">@datetime($log->created_at)</span>{{$log->description}}</p>
                    @endforeach
                    @endif
                </div>
            </td>
        </tr>

        </table>
    </div>

</div>

<input type="hidden" name="id" value="{{$task->id}}">
<input type="hidden" name="project_id" value="{{$project_id}}">

</form>