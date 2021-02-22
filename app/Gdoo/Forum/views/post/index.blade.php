<div class="panel">
    <h4 class="font-thin padder">板块列表</h4>
    <ul class="list-group alt">
        @if($rows)
        @foreach($rows as $row)
        <li class="list-group-item">
            <div class="media">
                <span class="pull-left">
                    <i class="{{$row->today > 0 ? '': 'text-muted'}} fa fa-2x fa-comments m-r-xs"></i>
                </span>
                <div class="pull-right m-t-sm">
                    <span class="badge bg-info">{{$row->count}}</span>
                </div>
                <div class="media-heading">
                    <a class="h5" href="{{url('forum',['id'=>$row->id])}}">{{$row->name}}</a>
                    <span class="badge">{{$row->today}}</span>
                </div>
                <div class="media-body">
                    <small class="block text-muted">
                        最后由 {{get_user($row['post']['add_user_id'], 'name')}}
                        <span> • <span>
                        {{human_time($row['post']['add_time'])}}
                    </small>
                </div>
            </span>
        </li>
        @endforeach
        @endif
    </ul>
</div>
