<form class="form-horizontal" method="post" action="{{url()}}" id="user-sms" name="user-sms">
<table class="table table-form m-b-none">
    <tbody>
        <tr>
            <td width="10%" align="right">接收者</td>
            <td>{{$user['name']}}</td>
        </tr>
        <tr>
            <td align="right">内容</td>
            <td colspan="3">
                <textarea rows="2" class="form-control" id="content" name="content"></textarea>
                <input type="hidden" name="user_id" value="{{$user['id']}}">
            </td>
        </tr>
    </tbody>
</table>
</form>