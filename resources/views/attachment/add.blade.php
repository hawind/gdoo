<script type="text/javascript">
var uploader = {
    file:function(fileId)
    {
        var id = $('#'+fileId).find(".id").val();
        location.href = '{{url("file/attachment/file")}}?model={{$attachList["model"]}}&id='+id;
    },
    cancel:function(fileId)
    {
        var id = $('#'+fileId).find(".id").val();
        if (id > 0) {
            var name = $('#'+fileId).find(".fileName a").text();
            $.messager.confirm('操作警告', '确定要删除 <strong>'+name+'</strong> 此文件吗', function(btn) {
                if (btn == true) {
                    var url = '{{url("file/attachment/delete")}}';
                    $.get(url,{model:'{{$attachList["model"]}}',id:id},function(res) {
                        if (res == 1) {
                            $('#'+fileId).remove();
                        }
                    });
                }
            });
        } else {
            $('#'+fileId).remove();
        }
    },
    insert:function(fileId)
    {
        var id = $('#'+fileId).find(".id").val();
        var name = $('#'+fileId).find(".file-name a").text();
        // 检查图片类型
        if (/\.(gif|jpg|jpeg|png|GIF|JPG|PNG)$/.test(name)) 
        {
            var html = '<img src="{{url("file/attachment/view")}}?model={{$attachList["model"]}}&id='+id+'" title="'+name+'">';
        } 
        else
        {
            var html = '<a href="{{url("file/attachment/file")}}?model={{$attachList["model"]}}&id='+id+'" title="'+name+'">'+name+'</a>';
        }
        UE.getEditor('content').execCommand('insertHtml', html);
    }
}
</script>

<script id="uploader-item-tpl" type="text/html">
    <div id="file_draft_<%=id%>" class="uploadify-queue-item">
        <span class="file-name"><span class="text-danger" title="草稿状态">!</span> <a href="javascript:uploader.file('file_draft_<%=id%>');"><%=title%></a></span>
        <span class="file-size">(<%=size%>)</span>
        <span class="insert"><a class="option" href="javascript:uploader.insert('file_draft_<%=id%>');">插入编辑器</a></span>
        <span class="cancel"><a class="option" href="javascript:uploader.cancel('file_draft_<%=id%>');">删除</a></span>
        <input type="hidden" class="id" name="attachment[]" value="<%=id%>" />
    </div>
    <div class="clear"></div>
</script>

<a class="btn btn-sm btn-info" href="javascript:viewBox('reader', '文件上传', '{{url('file/attachment/uploader', ['model' => $attachList['model'], 'path' => $attachList['path']])}}');"><i class="fa fa-cloud-upload"></i> 文件上传</a>
<span class="uploader-size">&nbsp;文件大小限制{{$setting['upload_max']}} MB</span>

<div class="clear"></div>

<div id="fileQueue">
@if($attachList['queue'])
    @foreach($attachList['queue'] as $n => $v)
    <div id="file_queue_{{$n}}" class="uploadify-queue-item">
        <span class="file-name"><span class="icon icon-paperclip"></span> <a href="javascript:uploader.file('file_queue_{{$n}}');">{{$v['title']}}</a></span>
        <span class="file-size"> ({{human_filesize($v['size'])}})</span>
        <span class="insert"><a class="option" href="javascript:uploader.insert('file_queue_{{$n}}');">插入编辑器</a></span>
        <span class="cancel"><a class="option" href="javascript:uploader.cancel('file_queue_{{$n}}');">删除</a></span>
        <input type="hidden" class="id" name="attachment[]" value="{{$v['id']}}" />
    </div><div class="clear"></div>
    @endforeach
@endif
</div>

<div id="fileQueueDraft">
@if($attachList['draft'])
    @foreach($attachList['draft'] as $n => $v)
    <div id="queue_draft_{{$n}}" class="uploadify-queue-item">
        <span class="file-name"><strong class="red" title="草稿附件">!</strong> <a href="javascript:uploader.file('queue_draft_{{$n}}');">{{$v['title']}}</a></span>
        <span class="file-size">({{human_filesize($v['size'])}})</span>
        <span class="insert"><a class="option" href="javascript:uploader.insert('queue_draft_{{$n}}');">插入编辑器</a></span>
        <span class="cancel"><a class="option" href="javascript:uploader.cancel('queue_draft_{{$n}}');">删除</a></span>
        <input type="hidden" class="id" name="attachment[]" value="{{$v['id']}}" />
    </div><div class="clear"></div>
    @endforeach
@endif
</div>