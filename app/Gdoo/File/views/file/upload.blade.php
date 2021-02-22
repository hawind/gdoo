<div class="table-responsive">
    <table class="table table-form m-b-none">
        <tr>
            <td>
                <script type="text/javascript" src="{{$asset_url}}/libs/swfobject.min.js"></script>
                <script type="text/javascript" src="{{$asset_url}}/vendor/uploadify/jquery.uploadify.js"></script>
                <script type="text/javascript">
                $(function()
                {
                    $("#uploadify").uploadify({
                        formData: {
                            PHPSESSID: '{{session()->getId()}}',
                            parent_id: '{{$parent_id}}',
                        },
                        'swf'            : '{{$asset_url}}/vendor/uploadify/uploadify.swf',
                        'buttonClass'    : 'btn btn-sm btn-info',
                        'buttonText'     : '<i class="fa fa-cloud-upload" aria-hidden="true"></i> 添加文件',
                        'uploader'       : '{{url("")}}',
                        'queueID'        : 'fileQueue',
                        'multi'          : true,
                        'auto'           : true,
                        'width'          : 90,
                        'height'         : 30,
                        'itemTemplate' : '<div id="${fileID}" class="uploadify-queue-item">\
                            <span class="file-name"><strong class="red" title="草稿附件">!</strong> <a href="javascript:attach.file(\'${fileID}\');">${fileName}</a></span>\
                            <span class="file-size">(${fileSize})</span>\
                            <span class="data"></span>\
                            <span class="uploadify-progress"><span class="uploadify-progress-bar"></span></span>\
                            <span class="insert"><a href="javascript:attach.insert(\'${fileID}\');">添加到正文</a></span>\
                            <span class="cancel"><a href="javascript:attach.cancel(\'${fileID}\');">删除</a></span>\
                            <input type="hidden" class="id" name="attachment[]" />\
                        </div><div class="clear"></div>',
                        'fileTypeExts' : "{{'*.'.str_replace(',',';*.',$setting['upload_type'])}}",
                        'fileTypeDesc' : "只能上传({{'*.'.str_replace(',',';*.',$setting['upload_type'])}})文件",
                        'fileSizeLimit': '{{$setting["upload_max"]*(1024*1024)}}',
                        'removeCompleted': false,
                        'onUploadSuccess':function(file, data, response)
                        {
                            $('#'+file.id).find(".id").val(data);
                            $('#'+file.id).find(".uploadify-progress").remove();
                        }
                    });
                });
                </script>
                <input type="file" name="uploadify" id="uploadify">
                <div class="uploadify-info text-ellipsis">&nbsp;支持 {{strtoupper($setting['upload_type'])}}, 大小限制{{$setting['upload_max']}}MB</div>
                <div class="clear"></div>
                <div id="fileQueue"></div>
            </td>
        </tr>
    </table>
</div>