<style>
body {
    color: #badbe6;
    text-align: center;
    background: -webkit-linear-gradient(right, #198fb4, #0b6fab);
  background: -o-linear-gradient(right, #198fb4, #0b6fab);
  background: linear-gradient(to left, #198fb4, #0b6fab);
  background-color: #3586b7;
}
.text-muted {
    color: #badbe6;
}
.text-muted a { 
    color: #fff; 
}
.list-group-item {
    border-color: #fff;
}
.login-box { 
    margin: 0 auto;
    text-align: left;
}
.login-box .h5 {
    text-align: center;
    line-height: 20px;
}

</style>

<div class="login-box">

    <div class="container w-xxl w-auto-xs" style="padding-top:50px;">

        <div class="wrapper text-center">
            <h2 class="text-white">{{$setting['title']}}</h2>
            <small class="text-muted">Different Office</small>
        </div>

        @if(Session::has('error'))
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                {{Session::pull('error')}}
            </div>
        @endif

        <div class="text-center">
        <img src="{{url('index/api/qrcode', ['size' => 5, 'data' => 'qrlogin:'.time().''])}}">
        </div>

        <div class="line line-dashed"></div>

        <div class="text-center text-muted">
            <a class="text-base" href="{{url('login')}}"><i class="fa fa-mail-reply"></i> 账号登录</a>
        </div>

        <div class="line line-dashed"></div>

        <div class="text-center">
            <small class="text-muted">© {{date('Y')}} {{$version}}</small>
        </div>

        <div class="line line-dashed"></div>

    </div>

    <div class="h5 m-t-lg text-white help">
        <p>合作经销商的账号和密码请与客户经理联络。</p>
        <p>公司员工、供应商的账号和密码请与总经理助理联络。</p>
        <p>系统异常请与上述岗位联络。</p>
    </div>

</div>