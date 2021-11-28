<div class="panel">

    <div class="wrapper-sm b-b b-light">
        
        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">

            @if(Auth::user()->role->code != 'c001')
                @include('report/select')
            @endif

            <select class="form-control input-sm" id='category_id' name='category_id' data-toggle="redirect" data-url="{{$query}}">
                @foreach($product_categorys as $k => $v)
                    <option value="{{$v['id']}}" @if($select['query']['category_id'] == $v['id']) selected @endif>{{$v['layer_space']}}{{$v['name']}}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i> 筛选</button>
        </form>
        
    </div>

    <table class="table">
    <tr>
    	<td>
    		<div id="container" style="height:360px"></div>
    	</td>
    </tr>
    </table>

</div>

@if(Auth::user()->role->code != 'c001')

<div class="panel">
    <table class="table">
        <tr height="24">
            <th align="center">本年促销计算费比(占比)</th>
            <th align="center">消费促销(金额)</th>
            <th align="center">渠道促销(金额)</th>
            <th align="center">经营促销(金额)</th>
            <th align="center">本年批复促销已兑现金额</th>
         </tr>
             
      	<tr height="24">
      		  <td align="center">{{$assess}}</td>
              <td align="center">{{number_format($promotion['cat'][1],2)}}</td>
              <td align="center">{{number_format($promotion['cat'][2],2)}}</td>
              <td align="center">{{number_format($promotion['cat'][3],2)}}</td>
              <td align="center">{{number_format($promotion_honor, 2)}}</td>
           </tr>
    </table>
</div>

<!--
<div class="panel">
    <table class="table">
        <tr height="24">
            @foreach($product_categorys as $category)
                @if($category['parent_id'] == 0)
                    <th align="center">{{$category['name']}}</th>
                @endif
            @endforeach
        </tr>
      	<tr height="24">
            @foreach($product_categorys as $category)
                @if($category['parent_id']==0)
                    <td align="center">{{$cat_salesdata_ret[$category['id']]}}</td>
                @endif
            @endforeach
      </tr>
    </table>
</div>
-->

@endif

<script src="{{$asset_url}}/vendor/echarts/echarts.min.js" type="text/javascript"></script>
<script type="text/javascript">
var data = {{$json}};
$(function () {
    var myChart = echarts.init(document.getElementById('container'));
    myChart.setOption({
        title: {
            text: '历史年度销售金额',
            subtext: "Historical Annual Sales",
            x:'center',
            y:'top',
            textAlign:'center'
        },
        tooltip: {
            trigger: 'axis',
            transitionDuration: 0,
        },
        legend: {
            formatter: function(key, avb) {
                return key + "年\n" + data.total[key] + '￥';
            },
            left: "center",
            bottom:"bottom",
            align: "left",
            textStyle: {
                lineHeight: 16,
                padding: 5, 
                borderColor: "#999",
                borderWidth: 1,
                borderRadius: 5,
            }
        },
        grid: {
            left: '3%',
            right: '3%',
            bottom: '60',
            containLabel: true
        },
        toolbox: {
            feature: {
                magicType: {type: ['line', 'bar']},
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: data.categories,
        },
        yAxis: {
            type: 'value'
        },
        series: data.series
    });
});
</script>