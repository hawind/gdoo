<div class="panel">

    <div class="panel-body">
        <form method="post" action="{{url('comment')}}" id="myform" name="myform">

            <div class="form-group">
                @include('attachment/add')
            </div>

            <div class="form-group">
                {{ueditor('content', $row['content'])}}
            </div>

            <div class="form-group">
                <input type="hidden" name="id" value="{{$row['id']}}">
                <input type="hidden" name="parent_id" value="{{$row['parent_id']}}">
                <button onclick="history.back();" class="btn btn-default">返回</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> 发表回复</button>
            </div>
        </form>
    </div>
</div>
