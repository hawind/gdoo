<div class="panel">

<table class="table">
  <tr>
    <td><div class='title'>{{$year}}年 ({{$circle['name']}}) 客户圈月份销售分析</div></td>
  </tr>
</table>

</div>

<div class="panel">

<table class="table table-bordered">
<tr>
<th>月份</th>

 @if(count($categorys)) @foreach($categorys as $k => $v)
	<th>{{$k}}</th>
 @endforeach @endif

<th>总销售额</th>
<th>促销费比</th>

<th>消费促销</th>
<th>渠道促销</th>
<th>经销促销</th>

 @if(count($single['money'])) @foreach($single['money'] as $key => $value)
<tr>
<td rowspan="2" width="40" align="center" style="vertical-align:middle;color:#66CC00;font-weight:bold;background:#FFFFE9">{{$key}}月</td>

 @if(count($categorys)) @foreach($categorys as $k => $v)
	<td align="right">
		{{$value[$v] > 0 ? $value[$v] : 0}}
	</td>
 @endforeach @endif


<td rowspan="2" align="right" style="vertical-align:middle;color:#666;background:#FFFFE9">{{$single['cat'][$key]}}</td>

<td rowspan="2" style="vertical-align:middle;" align="right">
 @if($single['cat'][$key])
	{{:$c = ($promotions['month1'][$key]/$single['cat'][$key])}}
	{{number_format($c*100, 2)}}%
 @else
	0.00%
 @endif
</td>


{{:$promotion = $promotions['month'][$key]}}
<td rowspan="2" style="vertical-align:middle;" align="right">
	{{$promotion[1] > 0 ? $promotion[1] : 0}}
</td>
<td rowspan="2" style="vertical-align:middle;" align="right">
	{{$promotion[2] > 0 ? $promotion[2] : 0}}
</td>
<td rowspan="2" style="vertical-align:middle;" align="right">
	{{$promotion[3] > 0 ? $promotion[3] : 0}}
</td>

</tr>

<tr>
	 @if(count($categorys)) @foreach($categorys as $k => $v)
	<td align="right">
		{{:$pl  = $value[$v]/$single['cat'][$key]}}
		{{$_pl = number_format($pl*100, 2)}}%
	</td>
	 @endforeach @endif
</tr>

 @endforeach @endif

</table>

</div>
