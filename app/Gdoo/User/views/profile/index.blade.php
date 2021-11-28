<form method="post" action="{{url()}}" id="profile" name="profile">

<div class="panel">

    <div class="panel-heading tabs-box">
        <ul class="nav nav-tabs">
            <li class="@if(Request::action() == 'index') active @endif">
                <a class="text-sm" href="{{url('index')}}">我的资料</a>
            </li>
            <li class="@if(Request::action() == 'password') active @endif">
                <a class="text-sm" href="{{url('password')}}">修改密码</a>
            </li>
        </ul>
    </div>

    <div class="panel-body">

        <div class="row">

            <div class="col-sm-2">
                <div class="text-center">
                    <span class="thumb-lg w-auto-folded avatar m-t-sm">
                        <a href="javascript:avatar();" class="hinted" title="修改头像"><img src="{{avatar(Auth::user()->avatar)}}" id="user-avatar" class="img-full" alt="{{Auth::user()->name}}"></a>
                        <div class="h4 font-thin m-t-sm">{{Auth::user()->login}}</div>
                    </span>
                </div>
            </div>
            <div class="col-sm-4">

                <div class="form-group">
                    <label class="control-label">主题</label>
                    <select id="change-theme" class="form-control input-sm" name="theme" id="theme">
                        <?php $themes = ['primary' => '原色', 'blue2' => '科技蓝', 'blue' => '经典蓝', 'wood' => '木质纸', 'purple' => '个性紫', 'green' => '绿色', 'lilac' => '淡紫色']; ?>
                        @if($themes)
                        @foreach($themes as $theme_key => $theme)
                            <option value="{{$theme_key}}" @if($user->theme == $theme_key) selected @endif>{{$theme}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label class="control-label">
                    头像显示
                    </label>
                    <select id="avatar_show" class="form-control input-sm" name="avatar_show">
                        <?php $avatar_shows = ['1' => '显示', '0' => '隐藏']; ?>
                        @if($avatar_shows)
                        @foreach($avatar_shows as $k => $v)
                            <option value="{{$k}}" @if($user->avatar_show == $k) selected @endif>{{$v}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label class="control-label">安全密钥</label>
                    <code>{{$user->auth_secret}}</code>
                    <a href="javascript:;" onclick="qrcodeTotp();">
                        <i class="fa fa-qrcode"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
    <div class="panel-footer">
        <div class="col-sm-offset-2">
            <button type="button" onclick="history.back();" class="btn btn-default">返回</button>
            <button type="button" id="profile-form-submit" class="btn btn-success"><i class="fa fa-check-circle"></i> 提交</button>
        </div>
    </div>
</div>

</form>

<script type="text/javascript" src="{{$asset_url}}/vendor/jquery.qrcode.min.js"></script>
<script>
function qrcodeTotp() {
    $.messager.alert("二次验证二维码", '<div align="center" id="secret-qrcode"></div>');
    $("#secret-qrcode").qrcode({
        render: "canvas",
        text: "{{$secretURL}}",
        correctLevel: 1,
        width: 200,
        height: 200
    });
}

$(function() {

    // 主题修改
    $('#change-theme').on('change', function() {
        var e = $(this).val();
        $('body').attr('class', 'theme-' + e);
        top.$('body').attr('class', 'theme-' + e);
    });

    ajaxSubmit('profile', function(res) {
        if (res.status) {
            toastrSuccess(res.data);
        } else {
            toastrError(res.data);
        }
    });
    
});
function avatar() {
    viewDialog({
        title: '修改头像',
        url: '{{url("avatar")}}',
        dialogClass: 'modal-lg',
        id: 'avatar-dialog',
        buttons: [{
            text: "确定",
            'class': "btn-default",
            click: function() {
                $(this).dialog("close");
            }
        }]
    });
}
</script>