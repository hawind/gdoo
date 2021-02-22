<div class="panel">

<div class="panel-heading tabs-box">
        <ul class="nav nav-tabs">
            <li class="@if($search['query']['done'] == 0) active @endif">
                <a class="text-sm" href="{{url('',['done'=>0])}}">执行中</a>
            </li>
            <li class="@if($search['query']['done'] == 1) active @endif">
                <a class="text-sm" href="{{url('',['done'=>1])}}">已结束</a>
            </li>
        </ul>
    </div>

    <div class="wrapper">
        @include('workflow/select_monitor')
    </div>

    <form method="post" id="myform" name="myform">
    <div class="table-responsive">
        <table class="table m-b-none table-striped b-t table-hover">
            <thead>
            <tr>
                <th align="center">
                    <input class="select-all" type="checkbox">
                </th>
                <th align="left">主题 / 文号</th>
                <th>发起人</th>
                <th>发起时间</th>
                <th>当前主办人</th>
                <th align="left">步骤(点击可查看各岗位处理时间)</th>
                <th>状态</th>
                <th>发起到现在时间</th>
                <th align="center">ID</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @if($rows)
            @foreach($rows as $row)
            <tr>
                <td align="center">
                    <input class="select-row" type="checkbox" name="id[]" value="{{$row['id']}}">
                </td>
                <td>
                    <a href="{{url('view')}}?process_id={{$row['id']}}">{{$row['title']}}</a>
                    <div class="text-muted">{{$row['name']}}</div>
                </td>
                <td align="center">{{get_user($row['start_user_id'], 'name')}}</td>
                <td align="center">@datetime($row['start_time'])</td>

                <td align="center">
                    {{get_user($row['step']['user_id'], 'name')}}
                </td>

                <td>
                    <span class="badge">{{$row['step']['number']}}</span>
                    <a href="javascript:viewBox('process-log','流程记录','{{url('log', ['process_id' => $row['id']])}}');">{{$row['step']['name']}}</a>
                </td>

                <td align="center">
                     @if($row['end_time'])
                        <span class="label label-info">已结束</span>
                     @else
                        <span class="label label-success">执行中</span>
                     @endif
                </td>
                
                <td align="center"><?php echo time_day_hour($row['start_time']); ?></td>

                <td align="center">{{$row['id']}}</td>
                <td align="center">
                    <a class="option" href="javascript:correct('{{$row['step']['id']}}');">纠正</a>
                </td>
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

<script type="text/javascript">
function correct(id)
{
    $.dialog({
        title: '工作纠正',
        dialogClass:'modal-md',
        onShow:function() {
            var self = this;
            $.get('{{url("correct")}}', {id:id}, function(html) {
                if (html) {
                    self.html(html);
                }
            });
        },
        buttons:[{
            text: '确定',
            'class': 'btn-primary',
            click: function() {

                var myform = $('#myform,#nextform').serialize();
                $.post('{{url("next")}}', myform, function(res1) {
                    if (res1.status) {
                        location.href = res1.data;
                    } else {
                        toastrError(res1.data);
                    }
                },'json');
            }
        },{
            text: '取消',
            'class': 'btn-default',
            click: function() {
                $(this).dialog('close');
            }
        }]
    });

}
</script>