<form method="post" class="form-horizontal" action="{{url('store')}}" id="sms" name="sms">
    
    <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="10%">名称</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="name" value="{{$row['name']}}">
            </td>
        </tr>

        <tr>
            <td align="right">apikey</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="apikey" value="{{$row['apikey']}}">
            </td>
        </tr>
        <tr>
            <td align="right">服务商</td>
            <td align="left">
                <select class="form-control input-sm" name="driver" id="driver">
                    <option value="gdoo" @if($row['driver'] == 'gdoo') selected @endif>Gdoo短信</option>
                    <option value="alidayu" @if($row['driver'] == 'alidayu') selected @endif>阿里短信</option>
                    <option value="yunpian" @if($row['driver'] == 'yunpian') selected @endif>云片网</option>
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