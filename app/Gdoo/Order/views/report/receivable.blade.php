<div class="panel">

    <div class="wrapper b-b b-light">
        @if(Auth::user()->role->name != 'client')
        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">
            
            @include('data/select')

            <div class="form-group">
                &nbsp;年份<input type="text" id="year" name="year" onclick="datePicker({dateFmt:'yyyy'});" value="{{$select['select']['year']}}" class="form-control input-sm">
            </div>

            <button type="submit" class="btn btn-default btn-sm">过滤</button>
        </form>
        @endif
    </div>

    <table class="table">
        <tr>
            <th align="left">客户名称</th>
            <th align="center">客户代码</th>
            @for($i=1; $i <= 12; $i++)
            <th align="right">{{$i}}月(￥)</th>
            @endfor
            <th align="right">合计(￥)</th>
        </tr>

        @foreach($rows as $row)
        <tr>
            <td align="left">{{$row['customer_name']}}</td>
            <td align="center">{{$row['customer_code']}}</td>
            @for($i=1; $i <= 12; $i++)
                <td align="right">
                    <div style="color:green;" title="月任务">@number($row['task'][$i] * 10000)</div>
                    <div title="客户回款金额">@number($row['money'][$i])</div>
                </td>
            @endfor
            <td align="right">
                <div style="color:green;" title="月任务">@number(array_sum($row['task']) * 10000)</div>
                <div>@number(array_sum($row['money']))</div>
            </td>
        </tr>
        @endforeach
    </table>
</div>