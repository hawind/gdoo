<script id="upload-item-tpl" type="text/html">
    <div id="<%=fileId%>" class="uploadify-queue-item">
        <span class="file-name"><span class="text-danger" title="草稿状态">!</span> <a href="javascript:uploader.file('<%=fileId%>');"><%=name%></a></span>
        <span class="file-size">(<%=size%>)</span>
        <span class="cancel"><a class="option" href="javascript:uploader.cancel('<%=fileId%>');">删除</a></span>
        <input type="hidden" class="id" name="attachment[]" value="<%=id%>" />
    </div>
    <div class="clear"></div>
</script>

<div class="uploadify-queue">

    <a class="btn btn-sm btn-info" href="javascript:viewBox('reader', '文件上传', '{{url('index/attachment/upload', ['path' => Request::module(), 'table' => $attachment['table'], 'field' => $attachment['field']])}}');"><i class="fa fa-cloud-upload"></i> 文件上传</a>
    <span class="uploader-size">&nbsp;文件大小限制 {{$setting['upload_max']}}MB</span>
    <div class="clear"></div>

    <div id="fileQueue" class="uploadify-queue">
    @if($attachment['rows'])
        @foreach($attachment['rows'] as $n => $v)
        <div id="file_queue_{{$n}}" class="uploadify-queue-item">
            <span class="file-name"><span class="icon icon-paperclip"></span> <a class="option" href="javascript:uploader.file('file_queue_{{$n}}');">{{$v['name']}}</a></span>
            <span class="file-size"> ({{human_filesize($v['size'])}})</span>
            
            @if(in_array($v['type'], ['pdf']))
                <a href="{{$upload_url}}/{{$v['path']}}" class="btn btn-xs btn-default" target="_blank">[预览]</a>
            @endif

            @if(in_array($v['type'], ['jpg','png','gif','bmp']))
                <img data-original="{{$upload_url}}/{{$v['path']}}" /><a data-toggle="image-show" class="option">[预览]</a>
            @endif
            
            <span class="cancel"><a class="option" href="javascript:uploader.cancel('file_queue_{{$n}}');">删除</a></span>
            <input type="hidden" class="id" name="attachment[]" value="{{$v['id']}}" />
        </div>
        <div class="clear"></div>
        @endforeach
    @endif
    </div>

    <div id="fileDraft_{{$attachment['table']}}_{{$attachment['field']}}">
    @if($attachment['draft'])
        @foreach($attachment['draft'] as $n => $file)
        <div id="queue_draft_{{$n}}" class="uploadify-queue-item">
            <span class="file-name"><span class="text-danger" title="草稿附件">!</span> <a class="option" href="javascript:uploader.file('queue_draft_{{$n}}');">{{$file['name']}}</a></span>
            <span class="file-size">({{human_filesize($file['size'])}})</span>
            <span class="cancel"><a class="option" href="javascript:uploader.cancel('queue_draft_{{$n}}');">删除</a></span>
            <input type="hidden" class="id" name="attachment[]" value="{{$file['id']}}">
        </div>
        <div class="clear"></div>
        @endforeach
    @endif     
    </div>
</div>
<script>
(function($) {
    var galley_id = "fileQueue";
    var galley = document.getElementById(galley_id);
    var viewer = new Viewer(galley, {
        navbar: false,
        url: "data-original",
    });
    $("#" + galley_id).on("click", '[data-toggle="image-show"]', function() {
        $(this).prev().click();
    });
})(jQuery);
</script>
