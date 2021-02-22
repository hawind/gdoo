<div class="panel">
    <div class="panel-heading b-b b-light">

        <div class="pull-right">
            <form id="myform" class="form-inline" name="myform" action="{{url()}}" method="get">
                回复 <span class="badge bg-info">{{sizeof($rows)}}</span>
                &nbsp;
                查看 <span class="badge">{{$post->hit}}</span>
            </form>
        </div>
        <div class="h5 text-md">{{$post->title}}</div>
    </div>

    <div class="wrapper-sx">

        <div class="wrapper-xs padder">
            @if(Auth::id() == $post->add_user_id || is_admin())
            <div class="pull-right">
                <a class="btn btn-xs btn-default" href="{{url('add',['id'=>$post->id])}}">编辑</a>
                <a class="btn btn-xs btn-default" onclick="app.confirm('{{url('delete',['id'=>$post->id])}}','确定要删除吗？');">删除</a>
            </div>
            @endif
            <small class="text-muted">
                由 {{get_user($post->add_user_id, 'name')}}
                &nbsp;
                @datetime($post->add_time)
            </small>
        </div>

        <div class="wrapper-xs padder">
            {{$post->content}}

            @if($attachList['view'])
                <div class="b-a b-light wrapper-sm">
                    @include('attachment/view')
                </div>
            @endif

        </div>
    </div>
</div>

@if($rows)
<div class="panel">

    <div class="panel-heading">
        <div class="h5">回复列表</div>
    </div>

    <div class="wrapper-sx">

        @foreach($rows as $row)

        <div class="wrapper-xs padder b-t">
            @if(Auth::id() == $row->add_user_id || is_admin())
                <div class="pull-right">
                    <a class="btn btn-xs btn-default" href="{{url('comment',['id'=>$row->id])}}">编辑</a>
                    <a class="btn btn-xs btn-default" onclick="app.confirm('{{url('delete',['id'=>$row->id])}}','确定要删除吗？');" href="javascript:;">删除</a>
                </div>
            @endif
            <small class="text-muted">
                由 {{get_user($row->add_user_id, 'name')}}
                &nbsp;
                @datetime($row->add_time)
            </small>
        </div>

        <div class="wrapper-xs padder">

            {{$row->content}}

            @if($row['attach'])
            <div class="b-a b-light wrapper-sm">
                {{'';$attachList['view'] = $row['attach']}}
                @include('attachment/view')
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="panel panel-info">

    <div class="panel-heading">
        <div class="h5">发表回复</div>
    </div>

    <div class="padder m-t">
        <form method="post" action="{{url('comment')}}" id="myform" name="myform">

            <div class="form-group">
                {{'';$attachList = $attachment}}
                @include('attachment/add')
            </div>

            <div class="form-group">
                {{ueditor('content')}}
            </div>

            <div class="form-group">
                <input type="hidden" name="parent_id" value="{{$post->id}}">
                <a href="{{url('forum',['id'=>$post->forum_id])}}" class="btn btn-default">返回</a>
                <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> 发表回复</button>
            </div>
        </form>
    </div>
</div>
