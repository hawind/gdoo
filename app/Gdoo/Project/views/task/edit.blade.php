<style>
.modal-body {
    overflow: hidden;
}
.wrapper-sm {
    background-color: #f0f3f4;
}
</style>
<div class="wrapper-sm">

    <form method="post" class="project-form" action="{{url()}}" id="task-form-{{$task['id']}}" name="task-form-{{$task['id']}}">

        <input type="hidden" name="type" value="{{$type}}">
        <input type="hidden" name="project_id" value="{{$task->project_id}}">
        <input type="hidden" name="id" value="{{$task->id}}">
        <input type="hidden" name="is_item" value="0">

        <div class="panel b-a">
            <table class="table table-form m-b-none">
                <tr>
                    <td align="right" width="10%">名称</td>
                    <td align="left">

                        @if($permission['name'])
                        <div class="input-group">
                            <div class="input-group-check">
                                <label class="i-checks i-checks-lg m-b-none hinted" title="点击完成任务">
                                    <input class="select-row" name="progress" type="checkbox" @if($task['progress']==1) checked="checked" @endif value="1"><i></i>
                                </label>
                            </div>
                            <input type="text" name="name" value="{{$task['name']}}" class="form-control input-sm">
                        </div>
                        @else

                        @if($task['progress'] == 1)
                        <span class="label label-success">完成</span>
                        @else
                        <span class="label label-info">执行中</span>
                        @endif

                        <input type="hidden" name="progress" value="{{$task['progress']}}">
                        <input type="hidden" name="name" value="{{$task['name']}}">

                        {{$task['name']}}

                        @endif
                    </td>
                </tr>

                @if($type == 'task')
                <tr>
                    <td align="right">任务列表</td>
                    <td align="left">
                        @if($items)
                        @if($permission['parent_id'])
                        <select class="form-control input-sm" name="parent_id">
                            @foreach($items as $item)
                            <option value="{{$item['id']}}" @if($item['id']==$task['parent_id']) selected="selected" @endif>{{$item['name']}}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="hidden" name="parent_id" value="{{$task['parent_id']}}">
                        @foreach($items as $item)
                        @if($item['id'] == $task['parent_id']) {{$item['name']}} @endif
                        @endforeach
                        @endif
                        @endif
                    </td>
                </tr>
                @endif

                <tr>
                    <td align="right">执行者</td>
                    <td align="left">
                        @if($permission['user_id'])
                        {{App\Support\Dialog::user('user', $type.'_user_id', $task['user_id'], 0, 0)}}
                        @else
                        <input type="hidden" name="{{$type}}_user_id" value="{{$task['user_id']}}">
                        {{App\Support\Dialog::text('user', $task['user_id'])}}
                        @endif
                    </td>
                </tr>

                <tr>
                    <td align="right">参与者</td>
                    <td align="left">
                        @if($permission['users'])
                        {{App\Support\Dialog::user('user', $type.'_users', $task['users'], 1, 0)}}
                        @else
                        <input type="hidden" name="{{$type}}_users" value="{{$task['users']}}">
                        {{App\Support\Dialog::text('user', $task['users'])}}
                        @endif
                    </td>
                </tr>

                <tr>
                    <td align="right">时间</td>
                    <td align="left">
                        @if($permission['date'])
                        <input type="text" name="start_at" data-toggle="datetime" value="@datetime($task->start_at,time())" class="form-control input-sm input-inline">
                        -
                        <input type="text" name="end_at" data-toggle="datetime" value="@datetime($task->end_at)" class="form-control input-sm input-inline">
                        @else
                        <input type="hidden" name="start_at" value="@datetime($task->start_at)">
                        <input type="hidden" name="end_at" value="@datetime($task->end_at)">
                        @datetime($task->start_at)
                        -
                        @datetime($task->end_at)
                        @endif
                    </td>
                </tr>

                <tr>
                    <td align="right">备注</td>
                    <td>
                        @if($permission['remark'])
                        <textarea class="form-control" type="text" name="remark">{{$task->remark}}</textarea>
                        @else
                        {{$task->remark}}
                        @endif
                    </td>
                </tr>

                <tr>
                    <td align="right">附件</td>
                    <td align="left">
                        @if($permission['attachment'])
                            @include('attachment/create')
                        @else
                            @include('attachment/show')
                        @endif
                    </td>
                </tr>

            </table>
        </div>

        <div class="task-subtask" id="task-subtask-{{$task->id}}">

            <div class="panel b-a">
                <div class="panel-heading b-b b-light">

                    <span class="font-bold">子任务 <span class="label bg-light">{{count($tasks)}}</span></span>
                    @if($permission['add-subtask'] == 1)
                    <a href="javascript:addSubTask({{$task->id}});" class="option option-add"><i class="fa fa-fw fa-plus"></i>添加子任务</a>
                    @endif
                </div>
                <ul class="list-group list-group-lg no-bg auto">

                    @if($tasks)
                    @foreach($tasks as $v)

                    <li class="list-group-item clearfix">
                        <span class="pull-left thumb-sm avatar m-r">
                            <img src="{{avatar($v['avatar'])}}">
                        </span>
                        <span class="clear">
                            <span>
                                <span class="pull-right text-muted">@datetime($v->created_at)</span>
                                {{$v['created_by']}}
                            </span>
                            <small class="text-muted clear text-ellipsis">
                                @if($v->progress == 1)
                                <span class="label label-success">完成</span>
                                @else
                                @if(auth()->id() == $v->user_id)
                                <span class="label label-danger">执行中</span>
                                @else
                                <span class="label label-info">执行中</span>
                                @endif
                                @endif
                                <a href="javascript:editSubTask({{$v->id}});">{{$v->name}}</a>
                            </small>
                        </span>
                    </li>
                    @endforeach
                    @endif

                </ul>
            </div>
        </div>

        <div class="task-log" id="task-log-{{$task->id}}">

            <div class="panel b-a m-b-none">
                <div class="panel-heading b-b b-light">
                    <span class="font-bold">评论列表 <span class="label bg-light">{{count($tasks)}}</span></span>
                    @if($permission['add-comment'] == 1)
                    <a href="javascript:addComment({{$task->id}});" class="option option-add"><i class="fa fa-fw fa-plus"></i>添加回复</a>
                    @endif
                </div>
                <div class="panel-body">

                    @if($logs)
                    @foreach($logs as $log)
                    @if($log->type == 'comment')

                    <div class="m-l-lg">
                        <a class="pull-left thumb-sm avatar m-l-n-md">
                            <img src="{{avatar($log->avatar)}}" alt="{{$log->created_by}}">
                        </a>
                        <div class="m-l-lg panel b-a">
                            <div class="panel-heading pos-rlt b-b b-light">
                                <span class="arrow left"></span>
                                <span>{{$log->created_by}}</span>
                                <span class="text-muted m-l-sm pull-right">
                                    <i class="fa fa-clock-o"></i> @datetime($log->created_at)
                                </span>
                            </div>
                            <div class="panel-body">
                                <div>{{$log->content}}</div>
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="task-log-content"><span class="time">@datetime($log->created_at)</span>{{$log->user}} {{$log->content}}</p>
                    @endif
                    @endforeach
                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

</form>