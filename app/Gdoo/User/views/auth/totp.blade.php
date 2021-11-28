<style>
body {
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
</style>
<div class="container w-xxl w-auto-xs" style="padding-top:100px;">

    @if(Session::has('error'))
        <div class="alert alert-warning alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            {{Session::pull('error')}}
        </div>
    @endif

    <div class="panel">
        <div class="panel-body">

            <form class="form-horizontal padder-md" url="{{url()}}" method="post" id="myform" name="myform" role="form">
                <div class="form-group">
                    <label class="control-label">两步验证</label>
                    <input type="text" class="form-control" maxlength="6" id="code" name="code" value="{{Request::old('code')}}">
                </div>
                <div class="form-group">
                    <!--
                    <span id="sms-info"><a onclick="totp.smsCode();" href="javascript:;" class="btn btn-info btn-xs" id="sms-code">点击短信获取验证码</a></span>
                    -->
                    <div class="pull-right">
                        <a href="{{url('logout')}}" class="btn btn-default"> 注销 </a>
                        <button type="submit" class="btn btn-primary"> 验证 </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="line line-dashed"></div>

<div class="text-center">
    <small class="text-muted">© {{date('Y')}} {{$version}}</small>
</div>

<script type="text/javascript">
$('#myform').submit(function() {
    var url  = $(this).attr('action');
    var data = $(this).serialize();
    $.post(url, data, function(res) {
        if(res.status) {
            toastrSuccess(res.data);
            app.redirect('/');
        } else {
            toastrError(res.data);
        }
    }, 'json');
    return false;
});

var totp = {
    second: 0,
    timeId: undefined,
    init: function(second)
    {
        clearTimeout(this.timeId);
        this.second = second;
        this.start();
    },
    start: function()
    {
        var self = this;
        if(this.second > 0) {
            $("#second").html(this.second);
            this.timeId = setTimeout(function()
            {
                self.start();
            }, 1000);
            this.second--;

        } else {
            clearTimeout(this.timeId);
            $("#sms-info").html('<a class="btn btn-info btn-xs" onclick="getSMSCode();" href="javascript:;" id="sms-code">重新获取验证码</a>');
        }
    },
    smsCode:function()
    {
        var self = this;
        $.get('{{url()}}',{sms:true}, function(res) {
            if(res.status == true) {
                self.init(res.data);
                $("#sms-info").html('<span class="btn btn-info btn-xs disabled">剩余<span id="second">' + res.data + '</span>秒重新获取</span>');
            } else {
                toastrError(res.data);
            }
        },'json');
    }
}
</script>
