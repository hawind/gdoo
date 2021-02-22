<div class="panel">

    @include('tabs', ['tabKey' => 'mp'])

    <div class="panel-body">

        <form class="form-horizontal" id="myform" method="POST" action="{{url()}}">

            <div class="form-group">
                <label class="control-label col-sm-2">开始ID</label>
                <div class="col-sm-6">
                    <input type="text" name="start_id" value="{{(int)$qrcode['scene_id'] + 1}}" required lay-verify="required" placeholder="请输入开始ID"
                        autocomplete="off" class="form-control input-sm">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">结束ID</label>
                <div class="col-sm-6">
                    <input type="text" name="end_id" value="{{(int)$qrcode['scene_id'] + $max}}" required lay-verify="required" placeholder="请输入结束ID"
                        autocomplete="off" class="form-control input-sm">
                </div>
            </div>

            <div class="form-group m-b-none">
                <label class="control-label col-sm-2"></label>
                <div class="col-sm-6">
                    <button class="btn btn-success" lay-submit lay-filter="formDemo">保存</button>
                    <button type="reset" class="btn btn-default">重置</button>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
$(function () {
    layui.use('form', function () {
        var form = layui.form;
        // 监听提交
        form.on('submit(formDemo)', function () {
            var data = $('#myform').serialize();
            var load = layer.load(2);
            $.post('{{url()}}', data, function (res) {
                layer.close(load);
                if (res.status == '0') {
                    layer.msg(res.msg);
                }
                if (res.status == '1') {
                    layer.msg(res.msg);
                    window.location.href = res.url;
                }
            })
            return false;
        });
    });
});
</script>