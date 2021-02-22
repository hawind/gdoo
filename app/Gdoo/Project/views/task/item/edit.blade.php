<style>
.modal-body { overflow:hidden; }
</style>

<form method="post" class="project-form" action="{{url()}}" id="item-form-{{$task->id}}" name="item-form-{{$task->id}}">
<div class="panel m-b-none">

    <table class="table table-form m-b-none">

        <tr>
            <td align="right" width="10%">名称</td>
            <td align="left">
                @if($permission['name'])
                    <input type="text" name="name" value="{{$task['name']}}" class="form-control input-sm">
                @else
                    {{$task['name']}}
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

        </table>
    </div>

</div>

<input type="hidden" name="type" value="{{$type}}">
<input type="hidden" name="project_id" value="{{$task->project_id}}">
<input type="hidden" name="id" value="{{$task->id}}">
<input type="hidden" name="is_item" value="1">

</form>