<div class="panel">

    <div class="wrapper-sm b-b b-light">

        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">

            <div class="pull-right">
                <a class="btn btn-default btn-sm" onclick="LocalTableExport('report_newclient', '本年新客户');"><i class="fa fa-mail-forward"></i> 导出</a>
            </div>

            @if(Auth::user()->role->code != 'c001')
                @include('report/select')
            @endif
            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i> 筛选</button>
        </form>

    </div>

    <table class="table table-bordered" id="report_newclient">
        <tr>
            <th align="center" width="100">序号</th>
            <th align="center">客户编码</th>
            <th align="left">客户名称</th>
            <th align="center" width="120">客户类型</th>
            <th align="center" width="120">发货金额</th>
        </tr>
        <?php $total_sum_money = 0; $i = 1; ?>
        @foreach($list[$nowYear] as $k => $v)
        <?php
            $customer = $customers[$k];
            $money_sum = $list[$nowYear][$k];
        ?>
        @if(empty($list[$lastYear][$k]))
            <tr>
                <td align="center">{{$i}}</td>
                <td align="center">{{$customer['customer_code']}}</td>
                <td align="left">{{$customer['customer_name']}}</td>
                <td align="center">{{$customer_type[$customer['grade_id']]['name']}}</td>
                <td align="right">@number($money_sum, 2)</td>
            </tr>
            <?php $total_sum_money += $money_sum; $i++; ?>
        @endif
        @endforeach
        <tr>
            <th align="center">合计</th>
            <th align="left"></th>
            <th align="left"></th>
            <th align="center"></th>
            <th align="right">@number($total_sum_money, 2)</th>
        </tr>
    </table>
</div>