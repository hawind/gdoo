<div class="panel">

    <div class="panel-heading tabs-box">

        <ul class="nav nav-tabs">
            @foreach(Workflow::$_timeout as $_key => $_option)
                <li class="@if($query['option'] == $_key) active @endif">
                    <a class="text-sm" href="{{url('timeout',['option'=>$_key])}}">{{$_option}}</a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="row wrapper">
        <div class="col-sm-12">
            <form id="myform" role="form" class="pull-right form-inline" name="myform" action="{{url()}}" method="get">
                <!--
                办理状态
                <select id='flag' name='flag' data-toggle="redirect" rel="{{url($action, $query)}}">
                    <option value="1" @if($query['flag'] == 1) selected @endif>办理中</option>
                    <option value="2" @if($query['flag'] == 2) selected @endif>已办理</option>
                </select>
                -->
                <button type="submit" class="btn btn-default btn-sm">过滤</button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover b-t">
            <thead>
            <tr>
                <th align="left">人员</th>
                <th align="right" width="160">办理总数</th>
                <th align="right" width="160">超时数量(办理中)</th>
                <th align="right" width="160">超时数量(已办理)</th>
                <th align="right" width="160">超时总计</th>
            </tr>
            </thead>
            <tbody>
            @if($rows)
            @foreach($rows as $user_id => $row)
            <tr>
                <td align="left">{{get_user($user_id, 'name')}}</td>
                <td align="right">{{$row['count']}}</td>
                <td align="right">
                    {{(int)$row['timeout_1']}}
                </td>
                <td align="right">
                    {{(int)$row['timeout_2']}}
                </td>
                <td align="right">
                    {{(int)$row['timeout_1'] + $row['timeout_2']}}
                </td>
            </tr>
             @endforeach
             @endif
            </tbody>
        </table>
    </div>
</div>
