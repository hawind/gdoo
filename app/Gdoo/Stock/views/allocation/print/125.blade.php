<div id="div1">
    <div style="line-height:40px;font-size:14pt;" align=center><strong><font>{{$setting['print_title']}}产成品调拨单</font></strong></div>   
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
<td align="right"><strong style="font-size:18px;">@number($row['quantity'], 2)</strong></td>
<td align="center"><strong style="font-size:18px;">{{$row['batch_sn']}}</strong></td>
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

<div id="div4">
    <table width="100%" border="0" style="border:0;" cellspacing="0" cellpadding="0">
        <tr>
            <td width="25%">制单人：{{$master['created_by']}}</td>
            <td width="25%">会计：李彩</td>
            <td width="25%">发货：</td>
            <td width="25%">仓管：</td>
        </tr>
    </table>
</div>

<div id="div3">
    <table width="100%" style="LINE-HEIGHT:30px;font-size:11pt;" cellspacing="0" cellpadding="0" style="border-collapse:collapse">
        <tr>
            <td width="100%" align="center">第<font tdata="PageNO">##</font>页，<font tdata="PageCount">##</font></span>页</td>
        </tr>
    </table>
 </div>

<script type="text/javascript" src="{{$asset_url}}/vendor/LodopFuncs.js"></script>
<script type="text/javascript"> 
	var LODOP;
	function print280() {
		LODOP = getLodop();
		LODOP.PRINT_INIT("{{$form['template']['name']}}");
        LODOP.SET_PRINT_PAGESIZE(0, 2100, 2700, "CreateCustomPage");
		var strStyle = "<style> table,td,th {border-width:1px;border-style:solid;border-collapse:collapse}</style>"
		LODOP.ADD_PRINT_TABLE(105, "4%", "92%", 430, document.getElementById("div2").innerHTML);
		LODOP.SET_PRINT_STYLEA(0,"Vorient", 3);

		LODOP.ADD_PRINT_HTM(10, "4%", "92%", 115, document.getElementById("div1").innerHTML);
		LODOP.SET_PRINT_STYLEA(0, "ItemType", 1);
		LODOP.SET_PRINT_STYLEA(0, "LinkedItem", 1);

        LODOP.ADD_PRINT_HTM(10, "4%","92%", 54, document.getElementById("div4").innerHTML);
		LODOP.SET_PRINT_STYLEA(0,"LinkedItem", 1);

	    LODOP.ADD_PRINT_HTM('96%', "4%", "92%", 54, document.getElementById("div3").innerHTML);
		LODOP.SET_PRINT_STYLEA(0,"ItemType", 1);

		LODOP.PREVIEW();			
	};
    function print140() {
		LODOP = getLodop();
		LODOP.PRINT_INIT("{{$form['template']['name']}}");
        LODOP.SET_PRINT_PAGESIZE(0, 2100, 1400, "CreateCustomPage");
		LODOP.ADD_PRINT_TABLE(105, "4%", "92%", 440, document.getElementById("div2").innerHTML);
		LODOP.SET_PRINT_STYLEA(0,"Vorient", 3);

		LODOP.ADD_PRINT_HTM(10, "4%", "92%", 115, document.getElementById("div1").innerHTML);
		LODOP.SET_PRINT_STYLEA(0, "ItemType", 1);
		LODOP.SET_PRINT_STYLEA(0, "LinkedItem", 1);

        LODOP.ADD_PRINT_HTM(10, "4%","92%", 54, document.getElementById("div4").innerHTML);
		LODOP.SET_PRINT_STYLEA(0,"LinkedItem", 1);

	    LODOP.ADD_PRINT_HTM('92%', "4%","92%", 54, document.getElementById("div3").innerHTML);
		LODOP.SET_PRINT_STYLEA(0,"ItemType", 1);

		LODOP.PREVIEW();			
	};

</script>