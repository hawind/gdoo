<table id="condition" class="bg-white table table-condensed table-bordered table-hover">
<thead>
<tr>
@foreach($columns as $column)
    @if($column)
        <th nowrap>{{$column}}</th>
    @endif
@endforeach
</tr>
</thead>

@foreach($rows as $row)
<tr>
    @foreach($row as $key => $row)

    @if($key == 'id')
        <td align="center">{{$row}}</td>
    @elseif($key == 'start_time')
        <td align="center">{{$row}}</td>
    @elseif($key == 'end_user_id')
        <td align="center">{{$row}}</td>
    @else
        <td>{{$row}}</td>
    @endif

    @endforeach
</tr>
@endforeach
</table>
