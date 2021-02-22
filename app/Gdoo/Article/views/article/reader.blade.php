<table class="table m-b-none">
    <thead>
        <tr>
            <th align="left" colspan="4">
                已读 <span class="badge bg-info">{{(int)$rows['total'][1]}}</span>
                &nbsp;&nbsp;
                未读 <span class="badge">{{(int)$rows['total'][0]}}</span>
            </th>
        </tr>
        <tr>
            <th align="center">
                序号
            </th>
            <th align="left">
                阅读人
            </th>
            <th align="center">
                部门
            </th>
            <th align="center">
                阅读时间
            </th>
        </tr>
    </thead>
    @if($rows['data'])
    @foreach($rows['data'] as $row)
    <tr>
        <td align="center">
            {{$loop->index + 1}}
        </td>
        <td align="left">
            {{$row['name']}}
        </td>
        <td align="center">
        {{$row['department']}}
        </td>
        <td align="center">
            @datetime($row['created_at'])
        </td>
    </tr>
    @endforeach 
    @endif
</table>