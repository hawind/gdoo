<div class="panel">
<table class="table table-form">
<form method="post" action="{{url()}}" id="myform" name="myform">

<tr>
    <td align="right" width="10%">板块名称 <span style="color:red">*</span></td>
    <td align="left">
        <input type="text" class="form-control input-sm" name="name" value="{{$row['name']}}" />
    </td>
</tr>

<tr>
    <td align="right">访问权限</td>
    <td>
        {{App\Support\Dialog::search($row,'id=scope_id&name=scope_name&multi=1')}}
    </td>
</tr>

<tr>
    <td align="right">类别状态</td>
    <td>
        <label class="radio-inline"><input type="radio" name="state" value="1"  @if($row['id']>0&&$row['state']==1) checked="true" @endif > 启用</label>
        &nbsp;
        <label class="radio-inline"><input type="radio" name="state" value="0"  @if($row['id']>0&&$row['state']==0) checked="true" @endif > 停用</label>
    </td>
</tr>

<tr>
    <td align="right">排序</td>
    <td align="left">
    <input type="text" class="form-control input-sm" name="sort" value="{{$row['sort']}}" />
    </td>
</tr>

<tr>
    <td align="right">备注</td>
    <td align="left">
    <textarea class="form-control input-sm" rows="3" cols="20" type="text" name="remark" id="remark" />{{$row['remark']}}</textarea>
    </td>
</tr>

<tr>
    <td align="right"></td>
    <td align="left">
        <input type="hidden" name="id" value="{{$row['id']}}" />
        <input type="hidden" id="past_parent_id" name="past_parent_id" value="{{$row['parent_id']}}" />
        <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> 提交</button>
        <button type="button" onclick="history.back();" class="btn btn-default">返回</button>
    </td>
</tr>

</table>
</form>
</div>
