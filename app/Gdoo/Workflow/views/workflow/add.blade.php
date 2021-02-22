<div class="wrapper">
<form class="form-horizontal" name="myform" id="myform" method="post">

<div class="form-group">
    <label class="col-sm-2 control-label">工作文号</label>
    <div class="col-sm-10">
        <input class="form-control input-sm" name="name" id="process_name" placeholder="编号规则" value="{{$row['title']}}({{date('Y-m-d H:i')}})" type="text" readonly>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-2 control-label">工作主题</label>
    <div class="col-sm-10">
        <input class="form-control input-sm" name="title" id="process_title" type="text">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-2 control-label">工作描述</label>
    <div class="col-sm-10">
        <textarea class="form-control input-sm" name="description" id="process_description"></textarea>
    </div>
</div>

<div class="form-group m-b-none">
    <label class="col-sm-2 control-label">重要等级</label>
    <div class="col-sm-10">
        <select class="form-control input-sm" name="level" id="process_level">
            <option value="1">普通</option>
            <option value="2">重要</option>
            <option value="3">紧急</option>
        </select>
    </div>
</div>
<input type="hidden" name="work_id" value="{{$row['id']}}">
</form>
</div>
