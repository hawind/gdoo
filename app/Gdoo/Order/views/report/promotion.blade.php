<div class="panel">

    <table class="table">
    <tr>
    <th>步骤</th>
    <th>开始时间</th>
    <th>结束时间</th>
    <th>促销对象</th>
    <th>促销目标</th>
    <th>促销类别</th>
    <th>促销单品</th>
    <th>促销方法</th>
    <th>我司支持方式</th>
    <th>兑现凭据</th>
    <th>预估费用</th>
    <th>实际兑现费用</th>

    @foreach($promotions as $key => $value)
    <tr>
        @foreach($value as $k => $v)
            <td align="left">{{$v}}</td>
        @endforeach
    </tr>
    @endforeach

    </table>

</div>