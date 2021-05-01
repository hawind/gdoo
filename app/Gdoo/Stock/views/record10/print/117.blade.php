<div id="div1">
    <table border=0 cellspacing=0 cellpadding=0 width="100%" style="font-size:11pt;">
        <tbody>
        <tr>
          <td width="43%"><font>单据编号：<span>{{$master['sn']}}</span></font></td>
          <td width="33%"><font>入库日期：<span>{{$master['invoice_dt']}}</span></font></td>
          <td><font>仓库：{{$master['warehouse_name']}}</font></td></tr>
        <tr>
          <td><font>入库类别：<span>{{$master['type_name']}}</span></font></td> 
          <td><font>部门：<span>{{$master['department_name']}}</span></font><font></font></td>
          <td><font>备注：{{$master['remark']}}</font></td></tr>
        </tbody></table>
      </div>
</div>

<div id="div2">
<style>
td { padding: 2px; }
</style>
<table style="font-size:11pt;" border="1" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse" bordercolor="#000000">
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
    <td tdata="Sum" format="#,##0.00" align="right"><font id="id01">###</font></td>
    <td></td>
    <td></td>
    </tfoot>
</table>
</div>

<div id="div3">
    <table width="100%" style="LINE-HEIGHT:30px;font-size:11pt;" cellspacing="0" cellpadding="0" style="border-collapse:collapse">
        <tr>
            <td width="40%">制单人：{{$master['created_by']}}</td>
            <td width="40%">交货人：</td>
        </tr>
    </table>
 </div>