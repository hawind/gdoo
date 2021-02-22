<span class="btn btn-xs btn-info">
    附件 <span class="badge">{{count($attachment['rows'])}}</span>
</span>

<div class="uploadify-queue">
    @foreach($attachment['rows'] as $row)
    <div class="uploadify-queue-item">
        <span class="file-name">
            <span class="text-muted icon icon-paperclip"></span> <a download="aaaa" href="{{url('index/attachment/download',['id'=>$row['id']])}}">{{$row['name']}}</a>
        </span> 
        <span class="file-size">&nbsp;({{human_filesize($row['size'])}})</span>
        &nbsp;
        @if(in_array(strtolower($row['type']), ['pdf']))
            <a href="{{URL::to('uploads').'/'.$row['path']}}" class="btn btn-xs btn-default" target="_blank">预览</a>
        @elseif(in_array(strtolower($row['type']), ['jpg','png','gif','bmp']))
            <a class="btn btn-xs btn-default" onclick="imageBox('preview', '附件预览', '{{URL::to('uploads').'/'.$row['path']}}');">预览</a>
        @else
            <a class="btn btn-xs btn-default" href="{{url('index/attachment/download',['id'=>$row['id']])}}">下载</a>
        @endif
        <div class="clear"></div>
    </div>
    @endforeach
</div