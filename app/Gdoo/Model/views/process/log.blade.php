<table class="table">
<thead>
    <tr>
        <th align="center">序号</th>
        <th align="left">步骤</th>
        <th align="left">办理内容</th>
        <th align="center">办理人</th>
        <th align="center">办理类型</th>
        <th align="center">办理效率</th>
        <th align="center">办理时间</th>
    </tr>
</thead>
    @foreach($rows as $i => $row)
    <tr>
        <td align="center">
            {{$i + 1}}
        </td>
        <td align="left">
            {{$steps[$row['step_number']]['name']}}
        </td>
        <td align="left">
            {{$row[description]}}
        </td>
        <td align="center">
            {{$row[created_by]}}
        </td>
        <td align="center">
            @if($row[step_status] == 'back') 
                退回 
            @else
                审批
            @endif
        </td>
        <td align="center">
        <?php 
            $start = $row['created_at'];
            if ($i) {
                $s = Carbon\Carbon::createFromTimeStamp($start);
                $e = Carbon\Carbon::createFromTimeStamp($end);
                echo $s->diffInHours($e).'小时';
            } else {
                echo '无';
            }
            $end = $start;
        ?>
        </td>
        <td align="center">
            @datetime($row[created_at])
        </td>
    </tr>
@endforeach
</table>