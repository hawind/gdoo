<script type="text/javascript">
var uploader = {
    file:function(fileId) {
        var id = $('#'+fileId).find(".id").val();
        location.href = '{{url('file/attachment/file')}}?model={{$attach['model']}}&id='+id;
    },
    cancel:function(fileId) {
        var id = $('#'+fileId).find(".id").val();
        if (id > 0) {
            var name = $('#'+fileId).find(".fileName a").text();
            $.messager.confirm('操作警告', '确定要删除 <strong>'+name+'</strong> 此文件吗', function(btn) {
                if (btn == true) {
                    var url = "{{url('file/attachment/delete')}}";
                    $.get(url,{model:"{{$attach['model']}}",id:id},function(res) {
                        if(res == 1) {
                            $('#'+fileId).remove();
                        }
                    });
                }
            });
        } else {
            $('#'+fileId).remove();
        }
    },
    copyLink:function() {
        var clipBoardContent = this.location.href;
        window.clipboardData.setData("Text", clipBoardContent);
        alert("复制成功。");
    }
}
</script>

<!--
<span class="file-name"><span class="text-danger" title="草稿状态">!</span> <a href="javascript:uploader.file('<%=fileId%>');"><%=title%></a></span> 
-->

<script id="uploader-item-tpl" type="text/html">
    <div id="<%=fileId%>" class="uploadify-queue-item">
        <span class="file-name"><span class="text-danger" title="草稿状态">!</span> <a href="javascript:uploader.file('<%=fileId%>');"><%=title%></a></span>
        <span class="file-size">(<%=size%>)</span>
        <span class="cancel"><a class="option" href="javascript:uploader.cancel('<%=fileId%>');">删除</a></span>
        <input type="hidden" class="id" name="attachment[]" value="<%=id%>" />
    </div>
    <div class="clear"></div>
</script>

@if($attach['auth']['add'] == true)
    <a class="btn btn-sm btn-info" href="javascript:viewBox('reader', '文件上传', '{{url('file/attachment/uploader', ['model' => $attach['model'], 'path' => $attach['path']])}}');"><i class="fa fa-cloud-upload"></i> 文件上传</a>
    <span class="uploader-size">&nbsp;文件大小限制{{$setting['upload_max']}} MB</span>
@endif

<div id="fileQueue">
@if($attach['queue'])
    @if($attach['queue'])
    @foreach($attach['queue'] as $v)
    <div id="file_queue_{{$v['id']}}" class="uploadify-queue-item">
        <span class="file-name"><a href="javascript:uploader.file('file_queue_{{$v['id']}}');">{{$v['title']}}</a></span>
        <span class="file-size">(
            上传者: {{get_user($v['add_user_id'], 'name')}}
            &nbsp;上传时间: @datetime($v['add_time'])
            &nbsp;大小: {{human_filesize($v['size'])}}
        )</span>
            
        @if($attach['auth']['add'] == true)
            <span class="insert"><a class="option" href="javascript:uploader.cancel('file_queue_{{$v['id']}}');">删除</a></span>
        @endif

        <input type="hidden" class="id" name="attachment[]" value="{{$v['id']}}">
    </div><div class="clearfix"></div>
    @endforeach
    @endif
@endif
</div>
<div id="fileQueueDraft">
    @if($attach['draft'])
    @foreach($attach['draft'] as $k => $v)
    <div id="queue_draft_{{$v['id']}}" class="uploadify-queue-item">
        <span class="file-name"><strong class="red" title="草稿附件">!</strong> <a download="{{$v['title']}}" href="{{$upload_url.'/'.$v['path'].'/'.$v['name']}}">{{$v['title']}}</a> ({{human_filesize($v['size'])}})</span>
        <span class="file-size">
            {{get_user($v['add_user_id'], 'name')}} - @datetime($v['add_time'])
        </span>
        @if($attach['auth']['add'] == true)
            <span class="insert"><a class="option" href="javascript:uploader.cancel('queue_draft_{{$v['id']}}');">删除</a></span>
        @endif

        <input type="hidden" class="id" name="attachment[]" value="{{$v['id']}}">
    </div><div class="clearfix"></div>
    @endforeach
    @endif
</div>
