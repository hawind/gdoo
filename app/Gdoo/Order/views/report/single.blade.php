<div class="panel">

    <div class="wrapper-sm b-b">
        <div class="text-md">{{$select['select']['year']}}年单品销售排名(占比)</div>
    </div>

    <div class="wrapper-sm b-b">

        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">

            <div class="pull-right">
                <a class="btn btn-default btn-sm" onclick="LocalTableExport('report_single', '单品销售排名');"><i class="fa fa-mail-forward"></i> 导出</a>
            </div>

            @if(Auth::user()->role->code != 'c001')
                @include('report/select')
                &nbsp;
            @endif

            <select class="form-control input-sm" id='category_id' name='category_id' data-toggle="redirect" data-url="{{$query}}">
                @foreach($categorys as $k => $v)
                    <option value="{{$v['id']}}" @if($select['query']['category_id'] == $v['id']) selected @endif>{{$v['layer_space']}}{{$v['name']}}</option>
                @endforeach
            </select>

            &nbsp;年份
            <input type="text" id="year" name="year" onclick="datePicker({dateFmt:'yyyy'});" value="{{$select['select']['year']}}" class="form-control input-sm">

            <button type="submit" class="btn btn-default btn-sm">筛选</button>
        </form>
        
    </div>

{{:$months = range(1,12)}}
@foreach($single['year'] as $year => $total)
	
    {{:arsort($total['money'])}}
    
	<table class="table table-bordered" id="report_single">
		
		<tr>
		<th align="center">产品名称</th>
		@if(count($months))
        @foreach($months as $month)
			<th align="center">
				{{$month}}月
			</th>
		@endforeach
        @endif
		<th align="center">合计</th>
		</tr>

        @foreach($total['money'] as $key => $product)

			{{:$total = array_sum((array)$product)}}
			<tr>
			<td>
				{{$single['name'][$key]}} - {{$single['spec'][$key]}}
			</td>

            @foreach($months as $month)
				<td align="right">
					<div title="金额">{{(int)$single['money'][$year][$key][$month]}}</div>
					<div title="件数">{{(int)$single['amount'][$year][$key][$month]}}</div>
					<div style="color:green;" title="该单品本月/本年的占比">
						 @if($single['money'][$year][$key][$month] > 0)
							{{number_format(($single['money'][$year][$key][$month]/$total)*100,2)}}%
						 @endif
					</div>
				</td>
			@endforeach 

			<th align="right">
				<div title="金额">{{$total}}</div>
				<div title="件数">{{(int)array_sum($single['amount'][$year][$key])}}</div>
				<div style="color:red;" title="本年单品/品类的占比">
				 @if($total > 0)
					{{number_format(($total/$single['money2'][$year])*100,2)}}%
				 @endif
				</div>
			</th>
		</tr>
		@endforeach
	</table>

@endforeach

</div>