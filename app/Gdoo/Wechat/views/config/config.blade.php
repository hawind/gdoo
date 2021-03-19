<div class="panel">

    @include('tabs2')

    <form class="form-horizontal" method="post" id="myform" action="{{url()}}">

    <div class="panel-body">
            <div class="form-group">
                <label class="control-label col-sm-2">AppId</label>
                <div class="col-sm-6">
                    <input type="text" name="wechat_appid" value="{{$app['wechat_appid']}}" required lay-verify="required" placeholder="请输入AppId" autocomplete="off"
                        class="form-control input-sm">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-2">AppSecret</label>
                <div class="col-sm-6">
                    <input type="text" name="wechat_secret" value="{{$app['wechat_secret']}}" required lay-verify="required" placeholder="请输入AppSecret"
                        autocomplete="off" class="form-control input-sm">
                </div>
            </div>

            <!--
            <div class="form-group">
                <label class="control-label col-sm-2">关闭回复</label>
                <div class="col-sm-6">
                    <textarea name="desc" placeholder="请输入内容" class="form-control input-sm">{{$mp['desc']}}</textarea>
                </div>
            </div>
            -->

            <div class="form-group">
                <label class="control-label col-sm-2">URL(服务器地址)</label>
                <div class="col-sm-6">
                    <code>{{url('wechat/echo/index')}}</code>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">Token(令牌)</label>
                <div class="col-sm-6">
                    <input type="text" name="token" value="{{$app['wechat_token']}}" required lay-verify="required" placeholder="请输入令牌" autocomplete="off" class="form-control input-sm">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">EncodingAESKey(消息加解密密钥)</label>
                <div class="col-sm-6">
                    <input type="text" name="wechat_aeskey" value="{{$app['wechat_aeskey']}}" required lay-verify="required" placeholder="请输入消息加解密密钥" autocomplete="off" class="form-control input-sm">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">开关状态</label>
                <div class="col-sm-6">
                    <div class="radio">
                        <label class="i-checks i-checks-sm">
                            <input type="radio" @if($app['wechat_status']==1) checked @endif value="1" name="wechat_status">
                            <i></i>开启</label>
                        &nbsp;&nbsp;
                        <label class="i-checks i-checks-sm">
                            <input type="radio" @if($app['wechat_status']==0) checked @endif value="0" name="wechat_status">
                            <i></i>关闭</label>
                    </div>
                </div>
            </div>

            <div class="form-group m-b-none">
                <label class="control-label col-sm-2"></label>
                <div class="col-sm-6">
                    <button class="btn btn-success" lay-submit lay-filter="sbm">立即提交</button>
                    <button type="reset" class="btn btn-default">重置</button>
                </div>
            </div>

        </div>

    </form>

</div>

<script>
    layui.use('form', function () {
        var form = layui.form;
        form.on('submit(sbm)', function () {
            var data = $('#myform').serialize();
            var loading = showLoading();
            $.post('{{url()}}', data, function (res) {
                layer.close(loading);
                if (res.status == '1') {
                    toastrSuccess(res.data);
                }
            })
            return false;
        });
    });
</script>