<div class="table-responsive">
<table class="table m-b-none">
<thead>
    <tr>
        <th align="center" nowrap="nowrap">序号</th>
        <th align="left" nowrap="nowrap">步骤</th>
        <th align="center" nowrap="nowrap">类型</th>
        <th align="center" nowrap="nowrap">收到时间</th>
        <th align="center" nowrap="nowrap">办理人</th>
        <th align="center" nowrap="nowrap">办理时间</th>
        <th align="center" nowrap="nowrap">办理效率</th>
        <th align="center" nowrap="nowrap">办理意见</th>
    </tr>
</thead>
    @foreach($rows as $i => $row)
    <tr>
        <td align="center" nowrap="nowrap">
            {{$i + 1}}
        </td>
        <td align="left" nowrap="nowrap">
            <a class="option hinted" title="收到人: {{$row['user_name']}}">{{$row['run_name']}}</a>
        </td>
        <td align="center" nowrap="nowrap">
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
                    <span class="label label-info">已阅</span>
                @else
                    <span class="label label-success">已审</span>
                @endif
            @endif
        </td>
        <td align="center" nowrap="nowrap">
            @datetime($row['created_at'])
        </td>
        <td align="center" nowrap="nowrap">
        {{get_user($row['updated_id'],'name')}}
        </td>
        <td align="center" nowrap="nowrap">
            @datetime($row['updated_at'])
        </td>
        <td align="center" nowrap="nowrap">
        <?php 
            $start = $row['created_at'];
            $end = $row['updated_at'];
            $s = Carbon\Carbon::createFromTimeStamp($start);
            $e = Carbon\Carbon::createFromTimeStamp($end);
            if ($row['updated_id']) {
                echo $s->diffInHours($e).'小时';
            }
        ?>
        </td>
        <td align="left" width="30%">
            {{$row['remark']}}
        </td>
    </tr>
@endforeach
</table>
</div>
