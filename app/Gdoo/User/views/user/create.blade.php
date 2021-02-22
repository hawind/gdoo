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
var action = '{{$form["action"]}}';
var table = '{{$form["table"]}}';

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

$(function() {
    if (action == 'show') {
        var text = $('#user_auth_secret').text();
    } else {
        var text = $('#user_auth_secret').data('content');
    }
    $('#user_auth_secret').html('<input type="hidden" id="user_auth_secret_value" name="user[auth_secret]" value="'+ text +'"><code id="secret">' + text + '</code><a class="btn btn-info btn-xs" onclick="getAuthSecret();" href="javascript:;">更新</a>');
});
</script>