<div class="panel">

    <div class="wrapper-sm">
        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">

            <div class="pull-right">
                <a class="btn btn-default btn-sm" onclick="LocalTableExport('report_export', '地区品类销售');"><i class="fa fa-mail-forward"></i> 导出</a>
            </div>

            @include('report/select')

            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i> 筛选</button>
        </form>
    </div>

<div class="table-responsive">
<table class="table table-bordered" id="report_export">
<tr>

<th style="white-space:nowrap">销售区域</th>

<th style="white-space:nowrap"></th>

@if($categorys)
    @foreach($categorys as $k => $v)
        @if($v['parent_id'] == 226)
            <th nowrap="nowrap">{{$v['name']}}</th>
        @endif
    @endforeach
@endif

<th>总销售额</th>
<th>促销费比</th>
<th>促销金额</th>
<!--
<th>消费促销</th>
<th>渠道促销</th>
<th>经销促销</th>
-->
</tr>

@if($now_year_single['money'])
@foreach($now_year_single['money'] as $key => $value)

<tr>

<td rowspan="3" align="center" style="white-space:nowrap;vertical-align:middle;">
    {{$regions[$key]}}
</td>

<td align="center" style="vertical-align:middle;color:#999;">销售金额(元)</td>

@if($categorys)
@foreach($categorys as $k => $v)
    @if($v['parent_id'] == 226)
	   <td align="right">
	       {{number_format($value[$k], 2)}}
	   </td>
    @endif
@endforeach
@endif

<td style="vertical-align:middle;" align="right">
    {{number_format($now_year_single['totalcost'][$key], 2)}}
</td>

<td rowspan="3" style="vertical-align:middle;" align="center">
 @if($promotions['all'][$key])
	{{:$c = ($promotions['all'][$key]/$now_year_single['totalcost'][$key])}}
	{{number_format($c * 100, 2)}}%
 @else
	0.00%
 @endif
</td>

<td rowspan="3" style="vertical-align:middle;" align="right">
    {{$promotions['all'][$key] > 0 ? $promotions['all'][$key] : 0}}
</td>

<!--
{{:$ps = $promotions[$key]}}
<td rowspan="3" style="vertical-align:middle;" align="right">
    {{$ps[1] > 0 ? $ps[1] : 0}}
</td>
<td rowspan="3" style="vertical-align:middle;" align="right">
    {{$ps[2] > 0 ? $ps[2] : 0}}
</td>
<td rowspan="3" style="vertical-align:middle;" align="right">
    {{$ps[3] > 0 ? $ps[3] : 0}}
</td>
-->

</tr>

<tr>

<td align="center" style="vertical-align:middle;color:#999;">去年同期增长率</td>

@if($categorys)
    @foreach($categorys as $category_id => $category)
        @if($category['parent_id'] == 226)
        <td align="right" title="去年同期增长率" nowrap="nowrap">
            <?php $scale = $oldscale[$key][$category_id]; ?>
            @if($scale == 0)
              去年无
            @elseif($scale > 0)
              <span style="color:green">{{number_format($scale * 100, 2)}}%</span>
            @else
              <span style="color:red">{{number_format($scale * 100, 2)}}%</span>
            @endif
        </td>
        @endif
    @endforeach
@endif

<?php 
    $_year = $now_year_single['totalcost'][$key] - $old_year_single['totalcost'][$key];
    if ($old_year_single['totalcost'][$key]) {
        $_total = number_format($_year / $old_year_single['totalcost'][$key] * 100, 2);
    } else {
        $_total = 0;
    }
?>
<td style="vertical-align:middle;" align="right">
    @if($_total > 0)
        {{$_total}}%
    @else
        <span style="color:red">{{$_total}}%</span>
    @endif
</td>

</tr>

<tr>
    <td align="center" style="vertical-align:middle;color:#999;">占区域该品类百分比</td>
	@if($categorys)
    @foreach($categorys as $k => $v)
        @if($v['parent_id'] == 226)
    		<td align="right" title="占区域该品类百分比">
            @if($value[$v['id']] > 0)
    		  {{number_format(($value[$v['id']] / $now_year_single[cat][$v['id']]) * 100, 2)}}%
            @endif
    		</td>
        @endif
    @endforeach
    @endif

    <td style="vertical-align:middle;" align="right"></td>
</tr>

@endforeach
@endif

</table>
</div>

</div>