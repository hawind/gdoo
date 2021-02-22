<div class="panel">

<div class="panel-body">

        <form method="post" action="{{url('add')}}" id="myform" name="myform">

            <div class="form-group">
                <input type="text" id="title" name="title" value="{{$row['title']}}" class="form-control">
            </div>

            <div class="form-group">
                @include('attachment/add')
            </div>

            <div class="form-group">
                {{ueditor('content', $row['content'])}}
            </div>

            <div class="form-group">
                <input type="hidden" name="id" value="{{$row['id']}}">
                <input type="hidden" name="forum_id" value="{{$row['forum_id']}}">
                <button onclick="history.back();" class="btn btn-default">返回</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> 发表</button>
            </div>
        </form>
    </div>

</div>
