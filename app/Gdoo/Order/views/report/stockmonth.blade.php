<div class="panel">

    <div class="wrapper-sm b-b b-light">
        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">

            <div class="pull-right">
                <a class="btn btn-default btn-sm" onclick="LocalTableExport('report_export', '三个月未进货客户');"><i class="fa fa-mail-forward"></i> 导出</a>
            </div>

            @if(Auth::user()->role->code != 'c001')
                @include('report/select')
            @endif
            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i> 搜索</button>
        </form>
    </div>

    <table class="table" id="report_export">
    <tr>
        <th align="center" width="60">序号</th>
        <th align="left" width="100">客户编码</th>
        <th align="left">客户名称</th>
        <th align="center" width="100">客户类型</th>
        <th align="right" width="200">去年金额</th>
        <th align="right" width="200">今年金额</th>
    </tr>

    <?php $n = 1; ?>
    @if(count($rows))
    @foreach($rows as $row)
        <tr>
            <td align="center">{{$n}}</td>
            <td align="center">{{$row['code']}}</td>
            <td align="left">{{$row['name']}}</td>
            <td align="center">{{$customer_type[$row['type_id']]['name']}}</td>
            <td align="right">@number($data[$year1][$row['id']], 2)</td>
            <td align="right">@number($data[$year2][$row['id']], 2)</td>
        </tr>
        <?php $n++; ?>
    @endforeach
    @endif
    </table>
</div>