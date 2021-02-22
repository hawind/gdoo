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

    <script src="{{$asset_url}}/vendor/highcharts/highcharts.min.js" type="text/javascript"></script>
    <script type="text/javascript">
    var data = {{$json}};
    $(function() {

        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function(color) {
            return {
                radialGradient: {cx:0.9,cy:0.9,r: 0.9},
                stops: [
                    [0, color],
                    [1, Highcharts.Color(color).brighten(-0.1).get('rgb')] // darken
                ]
            };
        });

        $.each(data.pie,function(year)
        {
            var piediv = "<div id='container_pie_"+year+"' style='height:300px;'></div>";
            var columndiv = "<div id='container_column_"+year+"' style='height:300px;'></div>";
            $('#container_pie').append(piediv);
            $('#container_column').append(columndiv);
            pie(year);
            column(year);
        });
    });

    function pie(year)
    {
        $('#container_pie_'+year).highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: year+'年品类构成分析(金额)'
            },
            tooltip: {
              pointFormat: '{series.name}: <b>{point.y}￥</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '金额',
                data: data.pie[year]
            }]
        });
    }

    function column(year)
    {
        $('#container_column_'+year).highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: year+'年品类销售额柱体分析(金额)'
            },
            xAxis: {
                categories: data.column.categories
            },
            yAxis: {
                allowDecimals: false,
                min: 0,
                title: {
                    text: '类别'
                }
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.x +'</b><br/>'+
                        this.series.name +': '+ this.y +'<br/>'+
                        '合计: '+ this.point.stackTotal;
                }
            },
            plotOptions: {
                column: {
                    stacking: 'normal'
                }
            },
            series:data.column.series[year]
        });
    }
    </script>

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
    <table class="table">
        <tr>
            <td id="container_pie"></td>
        </tr>
    </table>
</div>

<div class="panel">
    <table class="table">
        <tr>
            <td id="container_column"></td>
        </tr>
    </table>
</div>