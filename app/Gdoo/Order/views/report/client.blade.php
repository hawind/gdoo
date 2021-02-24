<div class="panel">

    <div class="wrapper-sm b-b b-light">
        <div class="text-md">{{$year_id}}年度发生交易客户数[{{count((array)$single['customer'])}}]</div>
    </div>

    <div class="wrapper-sm b-b b-light">

        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">

            <div class="pull-right">
                <a class="btn btn-default btn-sm" onclick="LocalTableExport('report_export', '客户单品交易');"><i class="fa fa-mail-forward"></i> 导出</a>
            </div>
            
            @if(Auth::user()->role->code != 'c001')
                @include('report/select')
                &nbsp;
            @endif
            <select class="form-control input-sm" id='year' name='year' data-toggle="redirect" data-url="{{$query}}">
                    @foreach($years as $v)
                    <option value="{{$v}}" @if($select['query']['year']==$v) selected @endif>{{$v}}年</option>
                    @endforeach
            </select>
            &nbsp;
            <select class="form-control input-sm" id='category_id' name='category_id' data-toggle="redirect" data-url="{{$query}}">
                @foreach($categorys as $k => $v)
                    <option value="{{$v['id']}}" @if($select['query']['category_id'] == $v['id']) selected @endif>{{$v['layer_space']}}{{$v['name']}}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i> 搜索</button>
        </form>
    </div>

    <table class="table table-bordered" id="report_export">
    <tr>
        <th>品类</th>
        <th>单品</th>

        <th>总销售家数</th>

        @foreach($months as $k => $v)
            <th>{{$v}}月</th>
        @endforeach

        @foreach($single['sum'] as $k => $v)
        <tr>
            <td align="center">{{$single['category'][$k]}}</td>
            <td align="left"><a href="{{url('clientdata')}}?aspect_id={{$select['select']['aspect_id']}}&region_id={{$select['select']['region_id']}}&circle_id={{$select['select']['circle_id']}}&client_id={{$select['select']['client_id']}}&product_id={{$k}}&year={{$year}}">[查]</a> {{$single['product'][$k]['product_name']}} - {{$single['product'][$k]['product_spec']}}</td>
            <td align="right">{{sizeof($single['all'][$k])}}</td>
            @foreach($months as $v2)
            <td align="right">
                {{:$sum = count((array)$v[$v2])}}
                @if($sum > 0) {{$sum}} @else <span style="color:#ccc;">0</span> @endif
            </td>
           @endforeach
        </tr>
        @endforeach
    </table>
</div>