<style>
body {
    text-align: center;
    background-image: url({{$asset_url}}/images/login-bg2.jpg);
    background-repeat: no-repeat;
    height: 100vh;
    background-attachment: scroll;
    background-position: center center;
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
}
.content-body {
    margin: 0;
}
.overlay {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    z-index: 0;
    background: rgba(0, 0, 0, 0.50);
    background: linear-gradient(to bottom, rgba(25, 137, 250, 0.65), rgba(25, 137, 250, 0.35));
}

.text-muted {
    color: #fff;
}
.text-muted a {
    color: #fff;
}
.login-box {
    position: relative;
    margin: 0 auto;
    text-align: left;
    max-width: 750px;
}
.login-box .h5 {
    text-align: center;
    line-height: 20px;
}
.toast {
    text-align: left;
}

.login-box {
    padding-top: 220px;
}

.logo-text {
    padding: 20px;
    padding-top: 60px;
    padding-left: 0;
}

.text-title {
    font-weight: 500;
    font-size: 36px;
}
.footer {
    margin-top: 10px;
    padding-top: 10px;
    border-top: solid 1px rgba(255, 255, 255, 0.3);
    text-align: right;
}
@media (max-width: 767px) {
    .login-box {
        padding-top: 60px;
    }
    .logo-text {
        text-align: center;
    }
    .row {
        margin-left: 15px;
        margin-right: 15px;
    }
    .footer {
        border-top: 0;
        text-align: center;
    }
    .panel-heading {
        display: none;
    }
}

.fa-lock {
    color: #666;
    font-size: 18px;
}
.text-tips {
    color: #999;
    padding-top: 5px;
}
.panel {
    border-radius: 5px !important;
}

.show_captcha {
    display: flex;
}
#refresh_captcha {
    margin-left: 5px;
    margin-top: 2px;
}
</style>

<div class="overlay"></div>

<div class="login-box">

        @if(Session::has('error'))
        <div class="alert alert-warning alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            {{Session::pull('error')}}
        </div>
        @endif

        <div class="row">

        <div class="col-sm-7 col-xs-12" id="aaa">
            <div class="logo-text layer" data-depth="0.60">
                <h2 class="text-white text-title">{{$setting['title']}}</h2>
                <small class="text-muted">Good Online Office</small>
            </div>
        </div>

        <div class="col-sm-5 col-xs-12">

        <form class="form-horizontal" url="{{url('login')}}" method="post" id="myform" name="myform">

            <div class="panel">

                <div class="panel-heading wrapper b-b b-light">
                    <h4 class="font-thin m-t-none m-b-none"><i class="fa fa-lock"></i> 登录</h4>
                    <div class="text-tips"> 
                        请使用您的帐号密码进行登录
                    </div>      
                  </div>

                <div class="panel-body" style="padding:20px 30px 0 30px;">
                    <div class="form-group">
                        <input type="text" placeholder="账号" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" placeholder="密码" class="form-control" name="password" required>
                    </div>

                    <div id="show_captcha" style="display:@if($show_captcha) '' @else none @endif">
                        <div class="form-group">
                            <div class="show_captcha">
                                <input type="text" maxlength="4" name="captcha" class="form-control" id="input-code" autocomplete="off" placeholder="验证码">
                                <a id="refresh_captcha" title="点击刷新">
                                    <img id="captcha_image" src="{{url('user/auth/captcha')}}" style="vertical-align:middle;height:30px;">
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox m-b-md m-t-none">
                            <label class="i-checks i-checks-sm">
                                <input type="checkbox" name="remember">
                                <i></i> 下次自动登录
                            </label>
                        </div>
                        <div class="line line-dashed"></div>
                        <button type="submit" class="btn btn-lg btn-info btn-block"> 登录 </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    </div>
    
    <!--
    <div class="line line-dashed"></div>
    <div class="text-center text-muted">
    <a class="text-base" href="{{url('qrcode')}}">
    <i class="fa fa-qrcode"></i> 扫码登录</a>
    </div>
    -->

    <div class="footer">
        <small class="text-muted">© {{date('Y')}} {{$version}}</small>
    </div>
</div>
</div>

<script>
(function($) {

    // 初始化获取的用户名
    let username = localStorage.getItem('remember_username');
    if (username) {
        $('#username').val(username);
    }
    // 记住用户名
    $("#username").on("input propertychange", function() {
        localStorage.setItem('remember_username', this.value);
    });

    // ajax 登录
    $('#myform').on('submit', function () {
        var url = $(this).attr('action');
        var data = $(this).serialize();
        $.post(url, data, function (res) {
            if (res.show_captcha) {
                $('#show_captcha').show();
            }
            if (res.success) {
                toastrSuccess(res.msg);
                app.redirect('/');
            } else {
                // 登录错误显示验证码
                if (res.show_captcha) {
                    refresh_captcha();
                }
                toastrError(res.msg);
            }
        }, 'json');
        return false;
    });

    // 刷新验证码
    $(document).on('click', '#refresh_captcha', function () {
        refresh_captcha();
    });

    // 刷新验证码方法
    function refresh_captcha() {
        $('#captcha_image').attr('src', settings.public_url + '/user/auth/captcha?_=' + Math.random());
    }
})(jQuery);
</script>