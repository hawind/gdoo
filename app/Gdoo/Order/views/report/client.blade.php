<div class="panel">

    <div class="wrapper-sm b-b b-light">
        <div class="text-md">{{$year}}年单品客户数({{count((array)$single['customer'])}})</div>
    </div>

    <div class="wrapper-sm b-b b-light">

        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">

            <div class="pull-right">
                <a class="btn btn-default btn-sm" onclick="LocalTableExport('report_export', '销售单品客户表');"><i class="fa fa-mail-forward"></i> 导出</a>
            </div>
            
            @include('report/select')
            &nbsp;

            <select class="form-control input-sm" id='year' name='year' data-toggle="redirect" data-url="{{$query}}">
                @if($years)
                    @foreach($years as $v)
                    <option value="{{$v}}" @if($select['query']['year']==$v) selected @endif>{{$v}}年</option>
                    @endforeach
                @endif
            </select>
            &nbsp;
            <select class="form-control input-sm" id='category_id' name='category_id' data-toggle="redirect" data-url="{{$query}}">
                <option value="0">全部品类</option>
                @foreach($categorys as $k => $v)
                    @if($v['layer_level'] == 2)
                    <option value="{{$v['id']}}" @if($select['query']['category_id'] == $v['id']) selected @endif>{{$v['name']}}</option>
                    @endif
                @endforeach
            </select>

            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i> 搜索</button>
        </form>
    </div>

    <table class="table table-bordered" id="report_export">
    <tr>
        <th>产品编码</th>
        <th>产品名称</th>

        <th></th>

        <th>合计</th>

        @if($months)
        @foreach($months as $k => $v)
            <th>{{$v}}月</th>
        @endforeach
        @endif

        @if($single['sum'])
        @foreach($single['sum'] as $k => $v)
        <tr>
            <td align="center" rowspan="2">{{$single['product'][$k]['product_code']}}</td>
            <td align="left" rowspan="2"><a href="{{url('clientdata')}}?aspect_id={{$select['select']['aspect_id']}}&region_id={{$select['select']['region_id']}}&circle_id={{$select['select']['circle_id']}}&client_id={{$select['select']['client_id']}}&product_id={{$k}}&year={{$year}}">[查]</a> {{$single['product'][$k]['product_name']}} - {{$single['product'][$k]['product_spec']}}</td>
            
            <td align="right" style="vertical-align:middle;color:#999;">销售客户数</td>

            <td align="right">{{count((array)$single['all'][$k])}}</td>
            @if($months)
            @foreach($months as $v2)
            <td align="right">
                {{:$sum = count((array)$v[$v2])}}
                @if($sum>0) {{$sum}} @else @endif
            </td>
           @endforeach
           @endif
        </tr>

        <tr>
            <td align="right" style="vertical-align:middle;color:#999;">销售金额</td>
            <td align="right" colspan="1">@number(array_sum((array)$single['sum_money'][$k]), 2)</td>
            @if($months)
            @foreach($months as $v2)
            <td align="right">
                @if($single['sum_money'][$k][$v2] > 0) @number($single['sum_money'][$k][$v2], 2) @else @endif
            </td>
           @endforeach
           @endif
        </tr>

        @endforeach
        @endif
    </table>
</div>