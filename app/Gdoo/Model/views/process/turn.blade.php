<form class="form-horizontal" name="myturn" id="myturn" method="post">

    <table class="table table-form">

        <tr>
            <td align="right">办理类型</td>
            <td align="left">
                <label class="i-checks i-checks-sm"><input type="radio" class="process-step" name="step_status" value="next"><i></i>审批</label>
                @if($step->back == 1 && $step->number > 1)
                &nbsp;
                &nbsp;
                <label class="i-checks i-checks-sm"><input type="radio" class="process-step" name="step_status" value="back"><i></i>退回</label>
                @endif
                &nbsp;
                &nbsp;
                <label class="i-checks i-checks-sm"><input type="radio" class="process-step" name="step_status" value="reject"><i></i>拒绝</label>
            </td>
        </tr>

        <tr>
            <td align="right" width="20%">选择进程</td>
            <td align="left"  width="80%">
                <div id="process-step"></div>
            </td>
        </tr>
        <tr>
            <td align="right">审批备注</td>
            <td align="left">
                <textarea class="form-control" rows="3" id="description" name="description"></textarea>
            </td>
        </tr>

        <tr>
            <td align="right">提醒类型</td>
            <td align="left">
                <label class="i-checks i-checks-sm"><input type="checkbox" name="notify_sms" value="{{$notify['sms']}}" @if($notify['sms'])checked="checked" @endif><i></i>短信</label>
            </td>
        </tr>

        <tr>
            <td align="right">提醒内容</td>
            <td align="left">
                <input type="text" class="form-control input-sm" name="notify_text" value="{{$notify['text']}}" readonly="readonly">
            </td>
        </tr>

    </table>
    <input type="hidden" name="key" value="{{$key}}">
</form>

<script type="text/javascript">
$('.process-step').on('click', function() {
    if(this.value == 'reject') {
        $('#process-step').html('无');
    } else {
        var myform = $('#myform, #myturn').serialize();
        var query = $.param({type: this.value, model_id:'{{$step->model_id}}', step_sn:'{{$step->sn}}'});
        $.post('{{url("step")}}?' + query, myform, function(res) {
            $('#process-step').html(res);
        });
    }
});

function get_step_user()
{
    var myform = $('#myform,#myturn').serialize();
    $.get('{{url("user")}}', myform, function(res) {
    	$('#process-user').html(res.data);
    });
}

</script>