<div class="panel">

    <div class="wrapper-xs b-b b-light">
        <div class='h5'>{{$single['product_name']}} - {{$single['product_spec']}} - {{$year}}年{{$month}}月无数据的客户列表</div>
    </div>

    <table class="table table-bordered">
        <tr>
            <th width="60">序号</th>
            <th width="160">销售组</th>
            <th width="280">客户名称</th>
            <th width="100"></th>
        </tr>
        @if($customers)
        <?php $i = 0; ?>
        @foreach($customers as $key => $value)
        @if(empty($notpurchase[$key]))
        <tr>
            <td align="center">{{$i + 1}}</td>
            <td align="center">{{$value['region_name']}}</td>
            <td align="left">{{$value['customer_id']}}</td>
            <td align="left"></td>
        </tr>
        @endif
        <?php $i++; ?>
        @endforeach
        @endif
    </table>

</div>

<div class="panel">

    <div class="wrapper-xs b-b b-light">
        <div class='h5'>{{$single['product_name']}} - {{$single['product_spec']}} - {{$year}}年客户销售统计</div>
    </div>

    <table class="table table-bordered">
        <tr>
            <th width="60">序号</th>
            <th width="160">销售组</th>
            <th width="280">客户名称</th>
            <th width="100">金额</th>
        </tr>
    <?php $i = 0; ?>
    @if($single['all'])
    @foreach($single['all'] as $key => $value)
    <tr>
      <td align="center">{{$i + 1}}</td>
        <td align="center">{{$customers[$key]['region_name']}}</td>
        <td align="left">{{$customers[$key]['customer_id']}}</td>
    	<td align="right">{{$value}}</td>
    </tr>
    <?php $i++; ?>
    @endforeach 
    @endif
    </table>

</div>