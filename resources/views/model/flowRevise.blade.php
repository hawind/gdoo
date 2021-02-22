<div class="table-responsive">
    <form class="form-horizontal" action="{{url()}}" name="revise-form" id="revise-form" method="post">
    <table class="table table-form b-b">
        <tr>
            <td align="right" width="15%">
                流程状态
            </td>
            <td align="left">
                <select name="master[status]" class="form-control input-sm">
                    @foreach($flows as $flow)
                    <option value="{{$flow['id']}}" @if($flow['id'] == $row['status']) selected="selected" @endif>{{$flow['name']}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td align="right">
                单据创建人
            </td>
            <td align="left">
                {{App\Support\Dialog::user('user','master[created_id]', '', 0, 0)}}
            </td>
        </tr>
        <tr>
            <td align="right">
                审批记录
            </td>
            <td align="left">
                <select name="log[step_id]" class="form-control input-sm">
                    <option value=""> - </option>
                    @foreach($steps as $step)
                    <option value="{{$step['step_id']}}">{{$step['name']}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td align="right">
                审批主办人
            </td>
            <td align="left">
                {{App\Support\Dialog::user('user','log[user_id]', '', 0, 0)}}
            </td>
        </tr>
        <tr>
            <td align="right">
                审批状态
            </td>
            <td align="left">
                <select name="log[run_status]" class="form-control input-sm">
                    <option value=""> - </option>
                    <option value="draft">待审</option>
                    <option value="next">已审</option>
                </select>
            </td>
        </tr>
    </table>
    <input type="hidden" name="key" value="{{$key}}">
    </form>

    <table class="table table-form m-b-none b-t">
        <thead>
            <tr>
                <th align="center">序号</th>
                <th align="left">步骤</th>
                <th align="center">办理人</th>
                <th align="center">办理类型</th>
                <th align="center">办理效率</th>
                <th align="center">办理时间</th>
                <th align="center">办理意见</th>
            </tr>
        </thead>
        @foreach($rows as $i => $row)
        <tr>
            <td align="center">
                {{$i + 1}}
            </td>
            <td align="left">
                {{$row['run_name']}}
            </td>
            <td align="center">
                {{get_user($row[user_id], 'name')}}
            </td>
            <td align="center">
                @if($row['updated_id'] == 0)
                @if($row['option'] == 0)
                    <span class="label label-info">未阅</span>
                @else
                    <span class="label label-danger">待审</span>
                @endif
                @elseif($row['run_status'] == 'back')
                    <span class="label label-warning">退回</span>
                @elseif($row['run_status'] == 'recall')
                    <span class="label label-warning">撤回</span>
                @elseif($row['run_status'] == 'abort')
                    <span class="label label-warning">弃审</span>
                @else
                    @if($row['option'] == 0)
                        <span class="label label-success">已阅</span>
                    @else
                        <span class="label label-success">已审</span>
                    @endif
                @endif
            </td>
            <td align="center">
            <?php
                $start = $row['created_at'];
                if($i) {
                    $s = Carbon\Carbon::createFromTimeStamp($start);
                    $e = Carbon\Carbon::createFromTimeStamp($end);
                    if($row[updated_at]) {
                        echo $s->diffInHours($e).'小时';
                    }
                }
                $end = $start;
            ?>
            </td>
            <td align="center" title="创建时间：@datetime($row['created_at'])">
                @datetime($row[updated_at])
            </td>
            <td align="left">
                {{$row[remark]}}
            </td>
        </tr>
        @endforeach

    </table>

</div>