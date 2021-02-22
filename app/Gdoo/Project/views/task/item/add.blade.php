<style>
.modal-body { overflow:hidden; }
</style>

<form method="post" action="{{url()}}" id="item-form" name="item-form">
<div class="panel m-b-none">

    <table class="table table-form m-b-none">

        <tr>
            <td align="right" width="10%">名称</td>
            <td align="left">
                <input type="text" name="name" value="{{$task['name']}}" class="form-control input-sm">
            </td>
        </tr>

        <tr>
            <td align="right">备注</td>
            <td>
                <textarea class="form-control" type="text" name="remark"></textarea>
            </td>
        </tr>

        </table>
    </div>

</div>

<input type="hidden" name="type" value="{{$type}}">
<input type="hidden" name="parent_id" value="{{$parent_id}}">
<input type="hidden" name="project_id" value="{{$project_id}}">
<input type="hidden" name="is_item" value="1">

</form>