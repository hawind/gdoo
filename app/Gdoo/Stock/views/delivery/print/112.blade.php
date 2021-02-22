<style type="text/css">
    @page {
        font:12pt 'SimSun', 'STXihei', sans-serif;
        margin: 10mm 5mm 15mm 5mm;
        size: 210mm 270mm;
        prince-pdf-page-colorspace: auto;
        prince-pdf-page-label: auto;
        prince-rotate-body: 0deg;
        prince-shrink-to-fit: none;
        @bottom {
            font-size: 10pt;
            content: "第" counter(page)"页，共"counter(pages)"页"
        }
    }
</style>
<table class="table no-border">
    <tr>
        <td width="70%">客户名称：{{$master['tax_name']}}</td>
        <td width="30%">发货日期：{{$master['invoice_dt']}}</td>
    </tr>
    <tr>
        <td>运费付款方式：{{$master['freight_pay_text']}}</td>
        <td></td>
    </tr>
    <tr>
        <td>联 系 人：{{$master['warehouse_contact']}}</td>
        <td>收货人手机：{{$master['warehouse_phone']}}</td>
    </tr>
    <tr>
        <td>收货地址：{{$master['warehouse_address']}}</td>
        <td>座机电话：{{$master['warehouse_tel']}}</td>
    </tr>
    <tr>
        <td>备注：{{$master['remark']}}</td>
        <td></td>
    </tr>
</table>

<br><br>

<table class="table no-border">
    <tr>
        <td width="34%">产品</td>
        <td width="33%">库管员：</td>
        <td width="33%">发货员：</td>
    </tr>
</table>

<?php $products = $rows->where('product_type', '>', 0); ?>
@if($products->count())
<table class="table">
    <thead>
        <tr>
            <td align="center">产品名称</td>
            <td align="center">规格型号</td>
            <td align="center">单位</td>
            <td align="center">数量</td>
            <td align="center">重量</td>
            <td align="center">备注</td>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $row)
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
            <td align="right">
                @number($row['quantity'], 2)
            </td>
            <td align="right">
                @number($row['total_weight'], 2)
            </td>
            <td>
                {{$row['remark']}}
            </td>
        </tr>
        @endforeach

        <tr>
            <td align="center">合计</td>
            <td></td>
            <td></td>
            <td align="right">@number($products->sum('quantity'), 2)</td>
            <td align="right">@number((intval($products->sum('total_weight') / 100)) * 100, 2)</td>
            <td></td>
        </tr>
    </tbody>

</table>
@endif

<?php $materiels = $rows->where('material_type', '>', 0); ?>
@if($materiels->count())
<table class="table no-border">
    <tr>
        <td width="34%">物料</td>
        <td width="33%">库管员：</td>
        <td width="33%">发货员：</td>
    </tr>
</table>

<table class="table">
    <thead>
        <tr>
            <td align="center">产品名称</td>
            <td align="center">规格型号</td>
            <td align="center">单位</td>
            <td align="center">数量</td>
            <td align="center">重量</td>
            <td align="center">备注</td>
        </tr>
    </thead>
    <tbody>
        @foreach($materiels as $row)
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
            <td align="right">
                {{$row['quantity']}}
            </td>
            <td align="right">
                @number($row['total_weight'], 2)
            </td>
            <td>
                {{$row['remark']}}
            </td>
        </tr>
        @endforeach

        <tr>
            <td align="center">合计</td>
            <td></td>
            <td></td>
            <td align="right">@number($materiels->sum('quantity'), 2)</td>
            <td align="right">@number($materiels->sum('total_weight'), 2)</td>
            <td></td>
        </tr>

    </tbody>

</table>
@endif

<table class="table">
    <tr>
        <td width="10%">特别说明</td>
        <td width="90%">
            收货时请按我司《随货单》点货验收。货物如有缺失或者破损，请与承运方协调，采取现场赔付，如协调无法达成一致意见，请第一时间告知我司并向承运方索取有效货物异常证明（贵司收货人与承运方双方签字认可的货物运单）回单至我司028-38296888并确认收到。
        </td>
    </tr>
    <tr>
        <td>回执单(客户填写)</td>
        <td><p style="padding-top:0;">
            1、&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            年&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            月&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            日收到产品&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            件，配件&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;件；
            </p>
            <p style="padding-top:0;">
            2、货品情况：<label class="checkbox-inline i-checks i-checks-sm"><i></i>完好</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="checkbox-inline i-checks i-checks-sm"><i></i>缺失</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="checkbox-inline i-checks i-checks-sm"><i></i>破损</label>
            </p>
            <p style="padding-top:0;">
            3、赔付与否：<label class="checkbox-inline i-checks i-checks-sm"><i></i>不需赔付</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="checkbox-inline i-checks i-checks-sm"><i></i>需要赔付</label>
            </p>
            <p style="padding-top:0;">
            4、赔付要求：
            </p>
            <p style="padding-top:0;">
            您对我司此次配送服务是否满意：
            <label class="checkbox-inline i-checks i-checks-sm"><i></i>满意</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="checkbox-inline i-checks i-checks-sm"><i></i>不满意</label>
            </p>
            <p style="padding-top:0;">
            贵司经办人签字（加盖贵司印章）：
            </p>
        </td>
    </tr>
    <tr>
        <td colspan="2">注：请您务必完整填写此表，签收时必须填写收到日期，否则视同在规定时间内已送达客户(此单回执，我司结账单以此单为准)，谢谢合作！</td>
    </tr>
</table>