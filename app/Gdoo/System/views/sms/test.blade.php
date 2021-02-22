<form method="post" action="{{url('test')}}" id="sms_test" name="sms_test">
<table class="table table-form m-b-none">
    <tr>
        <td align="left">
            <input class="form-control input-sm" placeholder="发送手机" name="data[sms_to]" type="text">
        </td>
    </tr>
    <tr>
        <td align="left">
            <textarea class="form-control input-sm" placeholder="发送内容" name="data[sms_text]" rows="3"></textarea>
        </td>
    </tr>
</table>
</form>
