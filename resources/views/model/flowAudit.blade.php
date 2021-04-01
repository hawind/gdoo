<form class="form-horizontal" name="myturn" id="myturn" method="post">

    <table class="table table-form">
        <tr>
            <td align="right">办理类型</td>
            <td align="left">
                <label class="i-checks i-checks-sm"><input type="radio" class="step_next_type" name="step_next_type" value="next"><i></i>审批</label>
                <!-- $run_step->back == 1 -->
                @if($run_step->type != 'start')
                &nbsp;
                &nbsp;
                <label class="i-checks i-checks-sm"><input type="radio" class="step_next_type" name="step_next_type" value="back"><i></i>退回</label>
                @endif
                <!--
                &nbsp;
                &nbsp;
                <label class="i-checks i-checks-sm"><input type="radio" class="step_next_type" name="step_next_type" value="reject"><i></i>拒绝</label>
                -->
            </td>
        </tr>

        <tr>
            <td align="right" width="20%">审核进程</td>
            <td align="left"  width="80%">
                <div id="step_next"></div>
            </td>
        </tr>

        <tr>
            <td align="right">审核人</td>
            <td align="left">
                <div id="step_next_user_html"></div>
            </td>
        </tr>

        <tr>
            <td align="right">抄送人</td>
            <td align="left">
                <div id="step_inform_user"></div>
            </td>
        </tr>

        <tr>
            <td align="right">审批意见</td>
            <td align="left">
                <textarea class="form-control" rows="3" id="step_remark" name="remark"></textarea>
            </td>
        </tr>

        <tr>
            <td align="right">提醒类型</td>
            <td align="left">
                <label class="i-checks i-checks-sm"><input type="checkbox" name="step_inform_sms" id="step_inform_sms" value="1"><i></i>短信</label>
            </td>
        </tr>

        <tr>
            <td align="right">提醒内容</td>
            <td align="left">
                <input type="text" class="form-control input-sm" name="step_inform_text" id="step_inform_text" value="" readonly="readonly">
            </td>
        </tr>

    </table>
</form>

<script type="text/javascript">
(function($) {
    var $me = $('#myturn');
    var informs = {};
    var inform_sms = {};
    var users = {};
    var remarks = {};
    $me.on('click', '.step_next_type', function() {
        
        $('#step_next', $me).empty();
        $('#step_next_user_html', $me).empty();
        $('#step_inform_user', $me).empty();

        if (this.value == 'reject') {
            $('#step_next', $me).html('无');
        } else {
            var table = '{{$table}}';
            var query = $('#{{$table}}, #myturn').serialize();
            // 获取子表数据
            var gets = gridListData(table);
            if (gets === false) {
                return;
            }

            $.post('{{url("flowStep")}}', query + '&' + $.param(gets), function(res) {
                if (res.status) {
                    var data = res.data;
                    informs = data.informs;
                    inform_sms = data.inform_sms;
                    users = data.users;
                    // 审核节点
                    $('#step_next', $me).html(data.tpl);
                    // 通知内容
                    $('#step_inform_text', $me).val(data.inform_text);
                    // 审核内容
                    $('#step_remark', $me).val(data.remark);

                    // 获取已选中的进程值
                    var step_id = $(".step_next_id", $me).val();
                    step_next_id(step_id);
                    
                } else {
                    toastrError(res.data);
                }
            }, 'json');
        }
    });

    function step_next_id(step_id) {
        if (step_id > 0) {
            // 审核人
            $('#step_next_user_html', $me).html(users[step_id]);
            // 知会人
            $('#step_inform_user', $me).html(informs[step_id]);
            // 短信提醒
            $('#step_inform_sms', $me).prop('checked', inform_sms[step_id]);
        }
    }

    $me.on('click', '.step_next_id', function() {
        var me = $(this);
        var step_id = me.val();
        step_next_id(step_id);
    });

})(jQuery);
</script>