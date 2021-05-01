<div id="div1">
    <table border=0 cellspacing=0 cellpadding=0 width="100%" style="font-size:11pt;">
        <tbody>
        <tr>
          <td width="43%"><font>单据编号：<span>{{$master['sn']}}</span></font></td>
          <td width="33%"><font>单据日期：<span>{{$master['invoice_dt']}}</span></font></td>
          <td><font>发货日期：{{$master['delivery_dt']}}</font></td>
        </tr>
        <tr>
          <td><font>转入仓库：<span>{{$master['in_warehouse_name']}}</span></font></td> 
          <td><font>转出仓库：<span>{{$master['out_warehouse_name']}}</span></font></td>
          <td></td>
        </tr>
        <tr>
            <td colspan="3"><font>备注：<span>{{$master['remark']}}</span></font></td> 
          </tr>
        </tbody>
    </table>
      </div>
</div>

<div id="div2">
<style>
td { padding: 5px; }
</style>
<table style="font-size:11pt;border-width:1px;border-style:solid;border-collapse:collapse;" border="1" width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td align="center">产品编码</td>
    <td align="center">产品名称</td>
    <td align="center">规格型号</td>
    <td align="center">单位</td>
    <td align="center">数量</td>
    <td align="center">批号</td>
    <td align="center">货位</td>
</tr>
</thead>

@foreach($rows as $row)
<tr>
<td align="center">{{$row['product_code']}}</td>        
<td align="center">{{$row['product_name']}}</td>        
<td align="center">{{$row['product_spec']}}</td>
<td align="center">{{$row['product_unit']}}</td>
<td align="right">@number($row['quantity'], 2)</td>
<td align="center">{{$row['batch_sn']}}</td>
<td align="center">{{$row['posname']}}</td>
</tr>
@endforeach

<tfoot>
    <td align="center">合计</td>
    <td></td>
    <td></td>
    <td></td>
    <td tdata="Sum" format="#,##0.00" align="right"><font id="id01">@number($rows->sum('quantity'), 2)</font></td>
    <td></td>
    <td></td>
</tfoot>

</table>

</div>

<div id="div4">
    <table width="100%" border="0" style="border:0;" cellspacing="0" cellpadding="0">
        <tr>
            <td width="25%">制单人：{{$master['created_by']}}</td>
            <td width="25%">会计：</td>
            <td width="25%">发货：</td>
            <td width="25%">仓管：</td>
        </tr>
    </table>
</div>