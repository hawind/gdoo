<form class="form-horizontal" name="myreturn" id="myreturn" method="post">
    <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="20%">回退进程</td>
            <td align="left" width="80%">
                <input type="text" readonly="readonly" class="form-control input-sm" value="{{$step['name']}}">
            </td>
        </tr>
        <tr>
            <td align="right">回退原因</td>
            <td align="left">
                <textarea class="form-control" id="remark" name="remark"></textarea>
            </td>
        </tr>
    </table>
    <input type="hidden" name="run_id" id="run_id" value="{{$step['run_id']}}">
    <input type="hidden" name="step_id" id="step_id" value="{{$step['step_id']}}">
    <input type="hidden" name="data_id" id="data_id" value="{{$gets['data_id']}}">
</form>