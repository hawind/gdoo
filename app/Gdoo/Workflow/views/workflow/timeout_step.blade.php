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
                办理状态
                <select id='flag' name='flag' data-toggle="redirect" rel="{{url($action, $query)}}">
                    <option value="1" @if($query['flag'] == 1) selected @endif>办理中</option>
                    <option value="2" @if($query['flag'] == 2) selected @endif>已办理</option>
                </select>
                <button type="submit" class="btn btn-default btn-sm">过滤</button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover b-t">
            <thead>
            <tr>
                <th align="center" width="60">编号</th>
                <th align="left">主题 / 文号</th>
                <th width="120">主办人</th>
                <th align="left" width="240">步骤</th>
                <th width="80">状态</th>
                <th width="140">超时 / 办理时限</th>
            </tr>
            </thead>
            <tbody>
            @if($rows)
            @foreach($rows as $row)
            <tr>
                <td align="center">{{$row['id']}}</td>
                <td>
                    <a href="{{url('view')}}?process_id={{$row['id']}}">{{$row['title']}}</a>
                    <div class="text-muted">{{$row['name']}}</div>
                </td>
                <td align="center">{{get_user($row['user_id'], 'name')}}</td>
                <td>
                    <span class="badge">{{$row['step_number']}}</span>
                    <a href="javascript:;">{{$row['step_name']}}</a>
                </td>
                <td align="center">
                     @if($row['flag'] == 2)
                        <div class="label label-success">已办理</div>
                     @else
                        <div class="label label-danger">办理中</div>
                     @endif
                </td>
                <td align="center">
                    {{remain_time(time(), $row['timeout_diff'] + time())}}
                    <div class="text-muted">{{$row['step_timeout']}}小时</div>
                </td>
            </tr>
             @endforeach
             @endif
            </tbody>
        </table>
    </div>

    <footer class="panel-footer">
        <div class="row">
            <div class="col-sm-12 text-right text-center-xs">
                {{$rows->render()}}
            </div>
        </div>
    </footer>
</div>
