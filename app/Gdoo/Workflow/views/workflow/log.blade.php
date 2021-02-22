<table class="table m-b-none">
<thead>
    <tr>
        <th align="center">序号</th>
        <th align="left">步骤</th>
        <th align="left">办理内容</th>
        <th align="center">办理人</th>
        <th align="center">办理类型</th>
        <th align="center">办理时间</th>
        <th align="center">办理效率</th>
    </tr>
</thead>
    @foreach($rows as $i => $row)
    <tr>
        <td align="center">
            {{$row['number']}}
        </td>
        <td align="left">
            @if($row['flag'] == 1)
            <span class="red">{{$row['step_name']}}</span>
            @else
            <span>{{$row['step_name']}}</span>
            @endif
        </td>
        <td align="left">
            {{$row[description]}}
        </td>
        <td align="center">
            {{get_user($row['user_id'], 'name')}}
        </td>
        <td align="center">
            @if($row[step_status] == 'back') 
                退回 
            @else
                审批
            @endif
        </td>
        <td align="center">
            @datetime($row[deliver_time])
        </td>
        <td align="center">
        <?php 
            $start = $row['deliver_time'];
            if ($start) {
                if ($end) {
                    $s = Carbon\Carbon::createFromTimeStamp($start);
                    $e = Carbon\Carbon::createFromTimeStamp($end);
                    echo $s->diffInHours($e).'小时';
                } else {
                    echo '无';
                }
                $end = $start;
            }
            
        ?>
        </td>
    </tr>
@endforeach
</table>