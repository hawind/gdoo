<div class="panel">

    <div class="wrapper b-b b-light">
        <div class='h5'>{{$single['cat']}} * ({{$single['name']}} - {{$single['spec']}}) - {{$year}}年度({{$month}}月)未进货经销商列表</div>
    </div>

    <table class="table">
        <tr>
            <th width="40">序号</th>
            <th width="100">区域</th>
            <th width="100">客户圈</th>
            <th width="280">客户名称</th>
            <th align="left">单品</th>
        </tr>
        @if(count($clients))
        <?php $i = 0; ?>
        @foreach($clients as $key => $value)
        @if(empty($notpurchase[$key]))
        <tr>
            <td align="center">{{$i + 1}}</td>
            <td align="center">{{$value['area']}}</td>
            <td align="center">{{$value['circle_name']}}</td>
            <td align="left">{{$value['client_id']}}</td>
            <td align="left">{{$single['name']}} - {{$single['spec']}}</td>
        </tr>
        @endif
        <?php $i++; ?>
        @endforeach
        @endif
    </table>

</div>

<div class="panel">

    <div class="wrapper b-b b-light">
        <div class='h5'>{{$single['cat']}} * ({{$single['name']}} - {{$single['spec']}}) - {{$year}}度年经销商销售分析</div>
    </div>

    <table class="table">
        <tr>
            <th width="40">序号</th>
            <th width="100">区域</th>
            <th width="100">客户圈</th>
            <th width="280">客户名称</th>
            <th align="left">单品</th>
            <th width="100">金额</th>
        </tr>
    <?php $i = 0; ?>
    @if(count($single['all'])) 
    @foreach($single['all'] as $key => $value)
    <tr>
      <td align="center">{{$i + 1}}</td>
        <td align="center">{{$clients[$key]['area']}}</td>
        <td align="center">{{$clients[$key]['circle_name']}}</td>
        <td align="left">{{$clients[$key]['client_id']}}</td>
    	<td align="left">{{$single['name']}} - {{$single['spec']}}</td>
    	<td align="right">{{$value}}</td>
    </tr>
    <?php $i++; ?>
    @endforeach 
    @endif
    </table>

</div>