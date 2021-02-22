<div class="panel">

    <div class="wrapper">
        <form id="search-form" class="form-inline" name="mysearch" action="{{url()}}" method="get">
            
            <div class="input-group">
                <button type="button" class="btn btn-sm btn-default" data-toggle="dropdown">
                    批量操作
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu text-xs">
                    @if(isset($access['delete']))
                    <li><a href="javascript:optionDelete('#myform','{{url('delete',['status'=>'1'])}}','确定要恢复流程吗？');"><i class="icon icon-play"></i> 恢复</a></li>
                    @endif
                    @if(isset($access['destroy']))
                    <li class="divider"></li>
                    <li><a href="javascript:optionDelete('#myform','{{url('destroy')}}', '流程将被永久删除？');"><i class="icon icon-remove"></i> 销毁</a></li>
                    @endif
                </ul>
            </div>

            @include('workflow/select')
        </form>
    </div>

    <form method="post" id="myform" name="myform">
    <div class="table-responsive">
        <table class="table m-b-none b-t table-hover">
            <thead>
            <tr>
                <th align="center">
                    <input class="select-all" type="checkbox">
                </th>
                <th align="left">主题 / 文号</th>
                <th>发起人</th>
                <th>当前主办人</th>
                <th align="left">步骤</th>
                <th>状态</th>
                <th align="center">ID</th>
            </tr>
            </thead>
            <tbody>
            @if($rows)
            @foreach($rows as $row)
            <tr>
                <td align="center">
                    @if(isset($access['delete']) && ($row['start_user_id'] == Auth::id() || $access['trash'] == 4))
                        <input class="select-row" type="checkbox" name="id[]" value="{{$row['id']}}">
                    @else
                        <input type="checkbox" disabled>
                    @endif
                </td>
                <td>
                    <a data-toggle="layer-frame-url" href="#{{$row['id']}}" data-skin="gdoo" data-url="{{url('view')}}?process_id={{$row['id']}}">{{$row['title']}}</a>
                    <div class="text-muted">{{$row['name']}}</div>
                </td>
                <td align="center">{{get_user($row['start_user_id'], 'name')}}</td>
                <td align="center">{{get_user($row['step']['user_id'], 'name')}}</td>
                <td>
                    <span class="badge">{{$row['step']['number']}}</span>
                    <a href="javascript:viewBox('process-log','流程记录','{{url('log', ['process_id' => $row['id']])}}');">{{$row['step']['name']}}</a>

                    <div class="">
                    <?php /*
                        if ($row['step']['timeout'])
                        {
                            $timeout = 6 * 60;
                            $start = Carbon\Carbon::createFromTimeStamp($row['step']['add_time']);
                            echo Carbon\Carbon::now()->diffInHours($start);
                        } */
                    ?>
                    </div>
                </td>
                <td align="center">
                     @if($row['end_time'])
                        <span class="label label-info">已结束</span>
                     @else
                        <span class="label label-success">执行中</span>
                     @endif
                </td>
                <td align="center">{{$row['id']}}</td>
            </tr>
             @endforeach
             @endif
            </tbody>
        </table>
    </div>
    </form>

    <footer class="panel-footer">
      <div class="row">
        <div class="col-sm-1 hidden-xs">
        </div>
        <div class="col-sm-11 text-right text-center-xs">
            {{$rows->render()}}
        </div>
      </div>
    </footer>
</div>