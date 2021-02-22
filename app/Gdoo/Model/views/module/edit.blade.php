<form method="post" class="form-horizontal" action="{{url('store')}}" id="mail" name="mail">
    
    <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="10%">名称</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="name" value="{{$row['name']}}">
            </td>
        </tr>
        <tr>
            <td align="right">邮箱帐号</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="user" value="{{$row['user']}}">
            </td>
        </tr>
        <tr>
            <td align="right">邮箱密码</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="password" value="{{$row['password']}}">
            </td>
        </tr>
        <tr>
            <td align="right">SMTP服务器</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="smtp" value="{{$row['smtp']}}">
            </td>
        </tr>
        <tr>
            <td align="right">服务器端口</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="port" value="{{$row['port']}}">
            </td>
        </tr>
        <tr>
            <td align="right">连接方式</td>
            <td align="left">
                <select class="form-control input-sm" name="secure" id="secure">
                    <option value="" @if($row['secure'] == '') selected @endif>默认</option>
                    <option value="ssl" @if($row['secure'] == 'ssl') selected @endif>ssl</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="right">状态</td>
            <td align="left">
                <select class="form-control input-sm" name="status" id="status">
                    <option value="1" @if($row['status'] == '1') selected @endif>启用</option>
                    <option value="0" @if($row['status'] == '0') selected @endif>停用</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="right">排序</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="sort" value="{{$row['sort']}}">
            </td>
        </tr>
    </table>
    <input type="hidden" name="id" value="{{$row['id']}}">
</form>