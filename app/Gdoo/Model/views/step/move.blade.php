<div class="panel">
    <form class="form-horizontal" method="post" action="{{url()}}" id="moveform" name="moveform">
        <div class="table-responsive">
            <table class="table table-form">
                <tr>
                    <td align="right">工作步骤</td>
                    <td align="left">
                        <select id="batch_status" class="form-control input-sm">
                            <option value=""> - </option>
                            <option value="0">未发放</option>
                            <option value="1">已发放</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right" width="15%">主办人</td>
                    <td align="left">
                        {{App\Support\Dialog::user('user','user_id', '', 0, 0)}}
                    </td>
                </tr>
                <tr>
                    <td align="right">工作状态</td>
                    <td align="left">
                        <select id="batch_status" class="form-control input-sm">
                            <option value="0">未办理</option>
                            <option value="1">已办理</option>
                            <option value="2">全部</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right">描述</td>
                    <td align="left">
                        <textarea class="form-control" id="batch_description"></textarea>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="id" value="{{$row->id}}">
        </form>
    </div>
</div>
