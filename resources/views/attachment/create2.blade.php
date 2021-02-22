<script id="uploader-item-tpl" type="text/html">
    <div id="<%=fileId%>" class="uploadify-queue-item">
        <span class="file-name"><span class="text-danger" title="草稿状态">!</span> <a href="javascript:uploader.file('<%=fileId%>');"><%=name%></a></span>
        <span class="file-size">(<%=size%>)</span>
        <span class="cancel"><a class="option" href="javascript:uploader.cancel('<%=fileId%>');"><i class="fa fa-fw fa-remove"></i></a></span>
        <input type="hidden" class="id" name="{{$field}}[]" value="<%=id%>" />
    </div>
    <div class="clear"></div>
</script>

<div class="uploadify-queue">

    <a class="btn btn-sm btn-info" href="javascript:viewBox('reader', '文件上传', '{{url('index/attachment/uploader', ['path' => Request::module(), 'key' => $key.'.'.$field])}}');"><i class="fa fa-cloud-upload"></i> 附件</a>
    <div class="clear"></div>

    <div id="fileQueue_{{$key}}_{{$field}}" class="uploadify-queue">
    
        @if($attachments['rows'])
        @foreach($attachments['rows'] as $n => $file)
        <div id="file_queue_{{$n}}" class="uploadify-queue-item">
            <span class="file-name"><span class="icon icon-paperclip"></span> <a href="javascript:uploader.file('file_queue_{{$n}}');">{{$file['name']}}</a></span>
            <span class="file-size">({{human_filesize($file['size'])}})</span>
            <span class="cancel"><a class="option" href="javascript:uploader.cancel('file_queue_{{$n}}');"><i class="fa fa-fw fa-remove"></i></a></span>
            <input type="hidden" class="id" name="{{$field}}[]" value="{{$file['id']}}">
        </div>
        <div class="clear"></div>
        @endforeach
        @endif

    </div>

    <div id="fileDraft_{{$key}}_{{$field}}">
    @if($attachments['draft'])
        @foreach($attachments['draft'] as $n => $file)
        <div id="queue_draft_{{$n}}" class="uploadify-queue-item">
            <span class="file-name"><span class="text-danger" title="草稿附件">!</span> <a href="javascript:uploader.file('queue_draft_{{$n}}');">{{$file['name']}}</a></span>
            <span class="file-size">({{human_filesize($file['size'])}})</span>
            <span class="cancel"><a class="option" href="javascript:uploader.cancel('queue_draft_{{$n}}');"><i class="fa fa-remove"></i></a></span>
            <input type="hidden" class="id" name="{{$field}}[]" value="{{$file['id']}}">
        </div>
        <div class="clear"></div>
        @endforeach
    @endif     
    </div>
</div>
