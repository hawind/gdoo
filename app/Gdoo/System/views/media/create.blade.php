<script id="uploader-item-tpl" type="text/html">
    <div id="<%=fileId%>" class="uploadify-queue-item">
        <span class="file-name"><span class="text-danger" title="草稿状态">!</span> <a href="javascript:uploader.file('<%=fileId%>');"><%=name%></a></span>
        <span class="file-size">(<%=size%>)</span>
        <span class="insert"><a class="option" href="javascript:uploader.insert('<%=fileId%>');">插入编辑器</a></span>
        <span class="cancel"><a class="option" href="javascript:uploader.cancel('<%=fileId%>');">删除</a></span>
        <input type="hidden" class="id" name="attachment[]" value="<%=id%>" />
    </div>
    <div class="clear"></div>
</script>

<div class="uploadify-queue">

    <a class="btn btn-sm btn-info" href="javascript:viewBox('reader', '文件上传', '{{url('index/attachment/uploader', ['path' => Request::module()])}}');"><i class="fa fa-cloud-upload"></i> 文件上传</a>
    <span class="uploader-size">&nbsp;文件大小限制 {{$setting['upload_max']}}MB</span>
    <div class="clear"></div>

    <div id="fileQueue" class="uploadify-queue">
    
    @if($attach['main'])
        @foreach($attach['main'] as $n => $file)
        <div id="file_queue_{{$n}}" class="uploadify-queue-item">
            <span class="file-name"><span class="icon icon-paperclip"></span> <a href="javascript:uploader.file('file_queue_{{$n}}');">{{$file['name']}}</a></span>
            <span class="file-size">({{human_filesize($file['size'])}})</span>
            <span class="insert"><a class="option" href="javascript:uploader.insert('file_queue_{{$n}}');">插入编辑器</a></span>
            <span class="cancel"><a class="option" href="javascript:uploader.cancel('file_queue_{{$n}}');">删除</a></span>
            <input type="hidden" class="id" name="attachment[]" value="{{$file['id']}}">
        </div>
        <div class="clear"></div>
    @endforeach

    @elseif($attachList['queue'])

        @foreach($attachList['queue'] as $n => $v)
        <div id="file_attach_{{$n}}" class="uploadify-queue-item">
            <span class="file-name"><span class="icon icon-paperclip"></span> <a href="javascript:uploader.file('file_attach_{{$n}}');">{{$v['title']}}</a></span>
            <span class="file-size"> ({{human_filesize($v['size'])}})</span>
            <span class="insert"><a class="option" href="javascript:uploader.insert('file_attach_{{$n}}');">插入编辑器</a></span>
            <span class="cancel"><a class="option" href="javascript:uploader.cancel('file_attach_{{$n}}');">删除</a></span>
            <input type="hidden" class="id" name="attachment[]" value="{{$v['id']}}" />
        </div>
        <div class="clear"></div>
        @endforeach

    @endif
    </div>

    <div id="fileQueueDraft">
    @if($attach['draft'])
        @foreach($attach['draft'] as $n => $file)
        <div id="queue_draft_{{$n}}" class="uploadify-queue-item">
            <span class="file-name"><span class="text-danger" title="草稿附件">!</span> <a href="javascript:uploader.file('queue_draft_{{$n}}');">{{$file['name']}}</a></span>
            <span class="file-size">({{human_filesize($file['size'])}})</span>
            <span class="insert"><a class="option" href="javascript:uploader.insert('queue_draft_{{$n}}');">插入编辑器</a></span>
            <span class="cancel"><a class="option" href="javascript:uploader.cancel('queue_draft_{{$n}}');">删除</a></span>
            <input type="hidden" class="id" name="attachment[]" value="{{$file['id']}}">
        </div>
        <div class="clear"></div>
        @endforeach
    @endif     
    </div>
</div>
