<div id="div1">
    <div style="line-height:40px;font-size:14pt;" align=center><strong><font>{{$setting['print_title']}}{{$form['template']['name']}}</font></strong></div>
    <table width="100%">
        <tbody>
        <tr>
            <td width="70%">客户名称：{{$master['tax_name']}}</td>
            <td width="30%">退货日期：{{$master['invoice_dt']}}</td>
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
                <td align="center">单价</td>
                <td align="center">金额</td>
                <td align="center">重量(kg)</td>
                <td align="center">批次</td>
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
                    {{floatval($row['quantity'])}}
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
                <td align="center">
                    {{$row['batch_sn']}}
                </td>
            </tr>
            @endforeach
    
            <tr>
                <td align="center">合计</td>
                <td></td>
                <td></td>
                <td align="center">{{floatval($rows->sum('quantity'))}}</td>
                <td></td>
                <td align="center">@number($rows->sum('money'), 2)</td>
                <td align="center">@number($rows->sum('total_weight'), 2)</td>
                <td></td>
            </tr>
        </tbody>
        
    </table>
    </div>
    
    <div id="div3">
        <table width="100%">
            <tr>
                <td width="25%">制单：{{$master['created_by']}}</td>
                <td width="25%">财务：李彩</td>
                <td width="25%">发货：</td>
                <td width="25%">仓管：</td>
            </tr>
        </table>
    </div>
    
    <div id="div4">
        <div align="center">第<font tdata="PageNO">##</font>页，<font tdata="PageCount">##</font></span>页</div>
     </div>
    
    <script language="javascript" src="{{$asset_url}}/vendor/LodopFuncs.js"></script>
    <script language="javascript" type="text/javascript"> 
        var LODOP;
        function print280() {
            LODOP = getLodop();
            LODOP.PRINT_INIT("{{$form['template']['name']}}");
            LODOP.SET_PRINT_PAGESIZE(0, 2100, 2700, "CreateCustomPage");
    
            var strStyle = "<style> table,td,th {border-width:1px;border-style:solid;border-collapse:collapse}</style>";
    
            LODOP.ADD_PRINT_TABLE(125, "4%", "92%", 420, strStyle + document.getElementById("div2").innerHTML);
            LODOP.SET_PRINT_STYLEA(0,"Vorient", 3);
    
            LODOP.ADD_PRINT_HTM(10, "4%", "92%", 120, document.getElementById("div1").innerHTML);
            LODOP.SET_PRINT_STYLEA(0, "ItemType", 1);
            LODOP.SET_PRINT_STYLEA(0, "LinkedItem", 1);
    
            LODOP.ADD_PRINT_HTM(10, 0, "92%", 54, document.getElementById("div3").innerHTML);
            LODOP.SET_PRINT_STYLEA(0,"LinkedItem", -2);
    
            LODOP.ADD_PRINT_HTM('96%', "4%","92%", 22, document.getElementById("div4").innerHTML);
            LODOP.SET_PRINT_STYLEA(0, "ItemType", 1);
            LODOP.PREVIEW();			
        };
    
        function print140() {
            LODOP = getLodop();
            LODOP.PRINT_INIT("{{$form['template']['name']}}");
            LODOP.SET_PRINT_PAGESIZE(0, 2100, 1400, "CreateCustomPage");
    
            var strStyle = "<style> table,td,th {border-width:1px;border-style:solid;border-collapse:collapse}</style>";
    
            LODOP.ADD_PRINT_TABLE(125, "4%", "92%", 420, strStyle + document.getElementById("div2").innerHTML);
            LODOP.SET_PRINT_STYLEA(0,"Vorient", 3);
    
            LODOP.ADD_PRINT_HTM(10, "4%", "92%", 120, document.getElementById("div1").innerHTML);
            LODOP.SET_PRINT_STYLEA(0, "ItemType", 1);
            LODOP.SET_PRINT_STYLEA(0, "LinkedItem", 1);
    
            LODOP.ADD_PRINT_HTM(10, 0, "92%", 54, document.getElementById("div3").innerHTML);
            LODOP.SET_PRINT_STYLEA(0,"LinkedItem", -2);
    
            LODOP.ADD_PRINT_HTM('93%', "4%","92%", 22, document.getElementById("div4").innerHTML);
            LODOP.SET_PRINT_STYLEA(0, "ItemType", 1);
            LODOP.PREVIEW();
        };
    
    </script>