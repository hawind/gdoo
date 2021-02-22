<div class="panel">
    <div class="panel-heading b-b b-light">

        <form id="search-form" class="form-inline" name="mysearch" action="{{url()}}" method="get">

            <div class="pull-right">
                主题 <span class="badge">{{$forum['count']}}</span>
                &nbsp;
                今日 <span class="badge bg-info">{{$forum['today']}}</span>
            </div>

            <a href="{{url('add',['forum_id'=>$forum['id']])}}" class="btn btn-sm btn-info"><i class="icon icon-plus"></i> 发帖</a>

            @include('searchForm')

        </form>
        <script type="text/javascript">
        $(function() {
            $('#search-form').searchForm({
                data:{{json_encode($search['forms'])}},
                init:function(e) {
                    var self = this;
                }
            });
        });
        </script>
    </div>

    <table class="table table-hover">
        <thead>
        <tr>
            <th align="left">主题 / 最后回复</th>
            <th align="left" width="160">作者</th>
            <th align="center" width="160">回复 / 查看</th>
            <th align="left" width="160">最后回复</th>
        </tr>
        </thead>

        @if($rows)
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td align="left" class="wrapper-xs">
                    <a class="h5" href="{{url('view',['id' => $row->id])}}">{{$row->title}}</a>
                    <small class="block text-muted"></small>
                </td>
                <td align="left">
                    <div>{{get_user($row->add_user_id, 'name')}}</div>
                    <small class="block text-muted">@datetime($row->add_time)</small>
                </td>
                <td align="center">
                    <div>{{$row->count}}</div>
                    <small class="block text-muted">{{$row['hit']}}</small>
                </td>
                <td align="left">
                    <div>{{get_user($row->post->add_user_id, 'name')}}</div>
                    <small class="block text-muted">@datetime($row->post->add_time)</small>
                </td>
            </tr>
            @endforeach
        </tbody>
        @endif
    </table>

    <div class="panel-footer text-right">
        {{$rows->render()}}
    </div>
</div>
