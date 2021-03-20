<style>
.modal-body { overflow:hidden; }
</style>

<form method="post" action="{{url()}}" id="task-form" name="task-form">
<input type="hidden" name="type" value="{{$type}}">

@if($type == 'subtask')
<input type="hidden" name="parent_id" value="{{$parent_id}}">
@endif

<input type="hidden" name="project_id" value="{{$project_id}}">
<input type="hidden" name="is_item" value="0">
        
<div class="panel m-b-none">

    <table class="table table-form m-b-none">

        <tr>
            <td align="right" width="10%">名称</td>
            <td align="left">
                <input type="text" name="name" value="{{$task['name']}}" class="form-control input-sm">
            </td>
        </tr>

        @if($type == 'task')
        <tr>
            <td align="right">任务列表</td>
            <td align="left">
                <select class="form-control input-sm" name="parent_id">
                    <option value="0"> - </option>
                    @if($items)
                    @foreach($items as $item)
                    <option value="{{$item['id']}}" @if($item['id'] == $task['item_id']) selected="selected" @endif>{{$item['name']}}</option>
                    @endforeach
                    @endif
                </select>
            </td>
        </tr>
        @endif

        <tr>
            <td align="right">执行者</td>
            <td align="left">
                {{App\Support\Dialog::user('user', $type.'_user_id', '', 0, 0)}}
            </td>
        </tr>

        <tr>
            <td align="right">参与者</td>
            <td align="left">
                {{App\Support\Dialog::user('user', $type.'_users', '', 1, 0)}}
            </td>
        </tr>
    
        <tr>
            <td align="right">时间</td>
            <td align="left">
                <input type="text" name="start_at" autocomplete="off" data-toggle="datetime" value="@datetime($task->start_at,time())" class="form-control input-sm input-inline"> 
                - 
                <input type="text" name="end_at" autocomplete="off" data-toggle="datetime" value="" class="form-control input-sm input-inline">
            </td>
        </tr>

        <tr>
            <td align="right">备注</td>
            <td>
                <textarea class="form-control" type="text" name="remark"></textarea>
            </td>
        </tr>

        <tr>
            <td align="right">附件</td>
            <td align="left">
                @include('attachment/create')
            </td>
        </tr>

        </table>
    </div>

</div>

</form>