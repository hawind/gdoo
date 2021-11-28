<form method="post" id="cron" name="cron">
    
    <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="10%">名称</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="name" value="{{$row['name']}}">
            </td>
        </tr>

        <tr>
            <td align="right">命令</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="command" value="{{$row['command']}}">
            </td>
        </tr>

        <tr>
            <td align="right">表达式</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="expression" value="{{$row['expression']}}">
            </td>
        </tr>

        <tr>
            <td align="right">类型</td>
            <td align="left">
                <select class="form-control input-sm" name="type" id="type">
                    <option value="system" @if($row['type'] == 'system') selected @endif>系统</option>
                    <option value="user" @if($row['type'] == 'user') selected @endif>用户</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">状态</td>
            <td align="left">
                <select class="form-control input-sm" name="status" id="status">
                    <option value="1" @if($row['status'] == 1) selected @endif>正常</option>
                    <option value="0" @if($row['status'] == 0) selected @endif>禁用</option>
                </select>
            </td>
        </tr>

    </table>
    <input type="hidden" name="id" value="{{$row['id']}}">
</form>