<form class="form-horizontal form-controller" action="{{url()}}" method="post" id="myfolder" name="myfolder">
    <div class="form-group">
        <label class="col-sm-2 control-label" style="border-top:0;"><span class="red">*</span> 名称</label>
        <div class="col-sm-10 control-text">
            <input type="text" value="{{$folder['name']}}" required="required" placeholder="请输入文件夹名称" class="form-control input-sm" autocomplete="off" name="name">
            <input type="hidden" value="{{$folder['id']}}" name="id">
        </div>
    </div>
</form>