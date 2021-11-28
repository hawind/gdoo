<div class="panel">

    <div class="wrapper-sm b-b b-light">
        
        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">

            <div class="pull-right">
                <a class="btn btn-default btn-sm" onclick="LocalTableExport('report_category', '品类销售');"><i class="fa fa-mail-forward"></i> 导出</a>
            </div>

            @if(Auth::user()->role->code != 'c001')
                @include('report/select')
            @endif
            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i> 搜索</button>
        </form>
        
    </div>


    <table class="table table-bordered" id="report_category">
        <tr>
            <th align="center" style="width:100px;">品类编码</th>
            <th align="left">品类名称</th>
            <th align="right">本年度销售累计金额</th>
            <th align="right">去年同期销售累计金额</th>
            <th align="right">去年同期占比(%)</th>
        </tr>
        @foreach($product_categorys as $k => $category)

        @if($category['level'] > 1 && $category['level'] < 10)
        <?php $v = $percentData[$now_year][$k]; ?> 
        @if($product_categorys[$k]['code'])
        <tr>
            <td align="center">
                {{$product_categorys[$k]['code']}}
            </td>
            <td align="left">
                @if($product_categorys[$k]['level'] > 2)
                    &nbsp;&nbsp;&nbsp;&nbsp;
                @endif
                {{$product_categorys[$k]['name']}}
            </td>
            <td align="right">
            @if($select['role'] == 'client')
                {{number_format($v / $percentData['sum'][$now_year] * 100, 2)}}%
            @else
                {{number_format($v, 2)}}
            @endif
            </td>
            <td align="right">
                @if($select['role'] == 'client')
                    {{number_format($percentData[$last_year][$k] / $percentData['sum'][$last_year] * 100, 2)}}%
                @else
                    {{number_format($percentData[$last_year][$k], 2)}}
                @endif
            </td>
            <td align="right">
                @if($percentage[$k] > 0)
                    {{$percentage[$k]}}%
                @else 
                    0% 
                @endif
            </td>
        </tr>
        @endif
        @endif

        @endforeach

        <tr>
            <td align="left"></td>
            <td align="left">同期年度累计金额</td>
            <td align="right">{{number_format($percentData['sum'][$now_year],2)}}</td>
            <td align="right">{{number_format($percentData['sum'][$last_year],2)}}</td>
            <td align="right">{{number_format($percentage['total'],2)}}%</td>
        </tr>
    </table>
</div>

<div class="panel">
    <div class="panel-heading b-b">品类销售额(占比)</div>
    <div class="panel-body">
        <div id="container_pie"></div>
    </div>
</div>

<div class="panel">
<div class="panel-heading b-b">品类月销售额</div>
    <div class="panel-body">
        <div id="container_column"></div>
    </div>
</div>

<script src="{{$asset_url}}/vendor/echarts/echarts.min.js" type="text/javascript"></script>
<script type="text/javascript">
var data = {{$json}};
$(function() {
    $.each(data.pie,function(year) {
        var piediv = "<div class='col-sm-3 col-xs-6' id='container_pie_"+year+"' style='height:240px;'></div>";
        var columndiv = "<div class='col-sm-6 col-xs-12' id='container_column_"+year+"' style='height:280px;'></div>";
        $('#container_pie').append(piediv);
        $('#container_column').append(columndiv);
        pie(year);
        column(year);
    });
});

function pie(year)
{
    var myChart = echarts.init(document.getElementById('container_pie_' + year));
    myChart.setOption({
        legend: {show:false},
        tooltip: {
            trigger: 'item',
            formatter: '{c} ({d}%)'},
        title: [{
            text: year + '年',
            left: '50%',
            textAlign: 'center'
        }],
        dataset: {
            source: data.pie[year]
        },
        series: [{
            type: 'pie',
            radius: '70%',
            itemStyle: {
                borderColor: '#fff',
                borderWidth: 1
            }
        }]
    });
}

function column(year)
{
    var myChart = echarts.init(document.getElementById('container_column_' + year));
    myChart.setOption({
        title: {
            text: year + '年',
            x:'center',
            left:'50%',
            textAlign:'center'
        },
        tooltip: {
            trigger: 'axis',
            transitionDuration: 0,
        },
        legend: {
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
            data: data.column.categories,
        },
        yAxis: {
            type: 'value'
        },
        series: data.column.series[year]
    });
}
</script>