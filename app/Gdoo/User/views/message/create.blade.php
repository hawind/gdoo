<table class="table table-form m-b-none">
    <tbody>
        <tr>
            <td width="10%" align="right">姓名</td>
            <td width="40%">{{$user->name}}</td>
            <td width="10%" align="right">性别</td>
            <td width="40%">{{option('user.gender', $user['gender'])}}</td>
        </tr>
        <tr>
            <td align="right">部门</td>
            <td>{{$user->department->name}}</td>
            <td align="right">岗位</td>
            <td>{{$user->role->name}}</td>
        </tr>
        <tr>
            <td align="right">手机</td>
            <td>{{$user->phone}}</td>
            <td align="right">邮箱</td>
            <td>{{$user->email}}</td>
        </tr>
        <tr>
            <th colspan="4" align="left">站内私信</th>
        </tr>
        <tr>
            <td align="right">内容</td>
            <td colspan="3">
                <form class="form-horizontal" method="post" action="{{url()}}" id="user_message" name="user_message">
                    <textarea class="form-control" name="content"></textarea>
                    <input type="hidden" name="read_by" value="{{$user->id}}" />
                </form>
            </td>
        </tr>
    </tbody>
</table>