<form method="post" class="form-horizontal" action="{{url('store')}}" id="setting" name="setting">
    
    <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="10%">配置名称</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="name" value="{{$row['name']}}">
            </td>
        </tr>
        <tr>
            <td align="right">Key</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="key" value="{{$row['key']}}">
            </td>
        </tr>
        <tr>
            <td align="right">值</td>
            <td align="left">
                <textarea class="form-control input-sm" type="text" name="value">{{$row['value']}}</textarea>
            </td>
        </tr>
        <tr>
            <td align="right">类型</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="type" value="{{$row['type']}}">
            </td>
        </tr>
        <tr>
            <td align="right">备注</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="remark" value="{{$row['remark']}}">
            </td>
        </tr>
    </table>
    <input type="hidden" name="id" value="{{$row['id']}}">
</form>