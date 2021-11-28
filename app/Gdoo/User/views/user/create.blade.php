<div class="form-panel">
    <div class="form-panel-header">
        <div class="pull-right"></div>
        {{$form['btn']}}
    </div>
    <div class="form-panel-body panel-form-{{$form['action']}}">
        <form class="form-horizontal form-controller" method="post" id="user" name="user">
            {{$form['tpl']}}
        </form>
    </div>
</div>

<script>
function getAuthSecret(userId) {
    $.messager.confirm('操作警告', '确定要更新安全密钥。', function(btn) {
        if (btn == true) {
            $.post('{{url("user/profile/secret")}}',{id:userId}, function(res) {
                $("#secret").html(res.data);
                $('#user_auth_secret_value').val(res.data);
            }, 'json');
        }
    });
}
function showSecretQrcode() {
    $.messager.alert('二次验证二维码','<div align="center" id="secret-qrcode"></div>');
    $('#secret-qrcode').qrcode({
        render: "canvas",
        text: "{{$secret_qrcode}}",
        width: 200,
        height: 200
    });
}
(function($) {
    var table = '{{$form["table"]}}';
    var action = '{{$form["action"]}}';
    if (action == 'show') {
        var text = $('#user_auth_secret').text();
        if (text) {
            $('#user_auth_secret').html('<a href="javascript:;" id="secretQrcode" onclick="showSecretQrcode();"><code id="secret">'+ text +'</code> <i class="fa fa-qrcode"></i></a>');
        }
    } else {
        var text = $('#user_auth_secret').data('content');
        $('#user_auth_secret').html('<input type="hidden" id="user_auth_secret_value" name="user[auth_secret]" value="'+ text +'"><code id="secret">' + text + '</code> <a class="btn btn-info btn-xs" onclick="getAuthSecret();" href="javascript:;">更新</a>');
    }

})(jQuery);
</script>