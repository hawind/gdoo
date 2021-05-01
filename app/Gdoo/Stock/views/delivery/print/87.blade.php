<div id="div1">
<table width="100%">
    <tbody>
    <tr>
        <td width="70%">客户名称：{{$master['tax_name']}}</td>
        <td width="30%">发货日期：{{$master['invoice_dt']}}</td>
    </tr>
    <tr>
        <td>单据编号：{{$master['sn']}}</td>
        <td>销售类型：{{$master['type_name']}}</td>
    </tr>
    <tr>
        <td colspan="2">备注：{{$master['remark']}}</td>
    </tr>
    </tbody>
</table>
</div>

<div id="div2">
<style type="text/css">
    td { padding: 5px; }
</style>
<table width="100%" border="1" style="font-size:12pt;">
    <thead>
        <tr>
            <td align="center">产品名称</td>
            <td align="center">规格型号</td>
            <td align="center">单位</td>
            <td align="center">数量</td>
            <td align="center">备注</td>
            <td align="center">B</td>
            <td align="center">单价</td>
            <td align="center">金额</td>
            <td align="center">重量(kg)</td>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td align="center">
                {{$row['product_name']}}
            </td>
            <td align="center">
                {{$row['product_spec']}}
            </td>
            <td align="center">
                {{$row['product_unit']}}
            </td>
            <td align="center">
                <strong style="font-size:18px;">{{floatval($row['quantity'])}}</strong>
            </td>
            <td align="center">
                <strong style="font-size:18px;">{{$row['batch_sn']}}</strong>
            </td>
            <td align="center">
                {{$row['warehouse_type']}}
            </td>
            <td align="center">
                @number($row['price'], 2)
            </td>
            <td align="center">
                @number($row['money'], 2)
            </td>
            <td align="center">
                @number($row['total_weight'], 2)
            </td>
        </tr>
        @endforeach

        @if($money < 0)
        <tr>
            <td align="center">折扣额</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td align="center">@number($money, 2)</td>
            <td></td>
        </tr>
        @endif

        <tr>
            <td align="center">合计</td>
            <td></td>
            <td></td>
            <td align="center">{{floatval($rows->sum('quantity'))}}</td>
            <td></td>
            <td></td>
            <td align="center"></td>
            <td align="center">@number($rows->sum('money') + $money, 2)</td>
            <td align="center">@number($rows->sum('total_weight'), 2)</td>
        </tr>
    </tbody>
    
</table>
</div>

<div id="div3">
<table width="100%">
    <tr>
        <td width="25%">制单：{{$master['created_by']}}</td>
        <td width="25%">财务：</td>
        <td width="25%">发货：</td>
        <td width="25%">仓管：</td>
    </tr>
</table>
</div>