<div class="panel">
<form method="post" action="{{url()}}" id="myform" name="myform">
<table class="table table-form m-b-none">
<tr>
    <td align="right" width="10%">流程名称 <span style="color:red;">*</span></td>
    <td><input type="text" id="title" name="title" value="{{$row['title']}}" class="form-control input-sm"></td>
</tr>

<tr>
    <td align="right">发起权限 <span style="color:red;">*</span></td>
    <td>
        {{App\Support\Dialog::search($row, 'id=auth_id&name=auth_name&multi=1')}}
    </td>
</tr>

<tr>
    <td align="right">查询权限 <span style="color:red;">*</span></td>
    <td>
        {{App\Support\Dialog::search($row, 'id=query_id&name=query_name&multi=1')}}
    </td>
</tr>

<tr>
    <td align="right">流程类型 <span style="color:red;">*</span></td>
    <td>
        <label class="radio-inline"><input type="radio" name="type" value="1" @if($row['id'] > 0) disabled @endif @if($row['type']==1) checked @endif>固定流程</label>
        <label class="radio-inline"><input type="radio" name="type" value="2" @if($row['id'] > 0) disabled @endif @if($row['type']==2) checked @endif>自由流程</label>
    </td>
</tr>

<tr>
    <td align="right">使用状态 <span style="color:red;">*</span></td>
    <td>
        <label class="radio-inline" title="所有用户都可以在前台新建工作里看到该流程，但无权限用户不能点击">
            <input name="state" value="1" type="radio" checked="checked">
            可见
        </label>
        <label class="radio-inline" title="只有拥有权限的用户才能在前台新建工作中看到，并可点击">
            <input name="state" value="2" type="radio">
            不可见
        </label>
        <label class="radio-inline" title="无论用户有无权限，都不会在前台新建工作中显示">
            <input name="state" value="3" type="radio">
            锁定
        </label>
    </td>
</tr>

<tr>
    <td align="right">所属类别 <span style="color:red;">*</span></td>
    <td align="left">
        <select class="form-control input-sm" name="category_id" id="category_id">
            <option value=""> - </option>
             @if($category)
             @foreach($category as $k => $v)
                <option value="{{$v['id']}}"  @if($row['category_id']==$v['id']) selected @endif >{{$v['title']}}</option>
             @endforeach
             @endif
        </select>
    </td>
</tr>

<tr>
    <td align="right">流程备注</td>
    <td><textarea id="remark" name="remark" class="form-control input-sm">{{$row['remark']}}</textarea></td>
</tr>

<tr>
    <td align="right"></td>
    <td>
        <input type="hidden" name="id" value="{{$row['id']}}">
        <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> 提交</button>
        <button type="button" onclick="history.back();" class="btn btn-default">返回</button>
        
    </td>
</tr>

</table>

</form>

</div>
