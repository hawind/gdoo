<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{$setting['title']}}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <link href="{{$asset_url}}/dist/app.min.css" rel="stylesheet" type="text/css">
    <script src="{{$asset_url}}/vendor/jquery.min.js"></script>
</head>
<style>
li { list-style: none; }
body {
    margin: 0 auto;
    text-align: center;
    width: 80%;
}
.header {
    text-align: center;
    margin-top: 50px;
    margin-bottom: 10px;
}
.logo img { border-radius: 500px; width:86px; height:86px; }
.input-group-addon { 
    background-color: #fff;
}
</style>
<body>
    <div class="header">
        <div class="logo">
            <img class="circle" src="{{$asset_url}}/images/a1.jpg" />
            <div>{{auth()->user()->name}}</div>
            <div>欢迎使用{{$setting['title']}} 扫一扫</div>
            <p class="message text-sm" style="color:red;"></p>
        </div>
    </div>

    <form id="fileForm" class="form-horizontal">
        <div class="form-group input-group">
            <span class="input-group-addon">单据名称</span>
            <input id="name" name="name" type="text" value="{{$model['name']}}" class="form-control input-sm" readonly="readonly" />
        </div>

        <div class="form-group input-group">
            <span class="input-group-addon">文件名称</span>
            <input type="text" id="filename" class="form-control input-sm" name="filename" />
        </div>

        <div class="form-group">
            <a class="btn btn-info btn-lg btn-block" id="qrcode-btn"><i class="fa fa-qrcode"></i> 扫一扫</a>
            <span style="display:none;">
                <input type="file" name="file" id="fileToUpload" capture="camera" accept="image/*" onchange="fileSelected();">
            </span>
        </div>
        <input type="hidden" name="key" value="{{$key}}" />
        <input type="hidden" name="x-auth-token" value="{{$token}}" />
    </form>
</body>
</html>
<script>

$(function() {
    $('#qrcode-btn').on('click', function() {
        $('#fileToUpload').click();
    });
});

var btn = '<i class="fa fa-qrcode"></i> 扫一扫';

function fileSelected() {
    var formData = new FormData($('#fileForm')[0]);
    $('.message').html('');
    $('#fileToUpload').attr('disabled', 'disabled');
    $.ajax({
        url: '{{url("uploader")}}',
        type: 'POST',
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        xhr: function(){
            var xhr = $.ajaxSettings.xhr();
            if (xhr.upload) {
                $('#qrcode-btn').html('上传中...');
                // xhr.upload.addEventListener("progress", onprogress, false);
                return xhr;
            }
        },
        success: function (returndata) {
            $('#qrcode-btn').html(btn);
            $('#fileToUpload').removeAttr('disabled');
            $('#fileToUpload').val('');
            $('.message').html('上传成功,请在提单界面等待图片出现。');
        },
        error: function (returndata) {
            $('#qrcode-btn').html(btn);
            $('#fileToUpload').removeAttr('disabled');
            $('#fileToUpload').val('');
            $('.message').html('上传失败。');
        }
    });
}
/*
function onprogress(evt) {
    // $(".form-footer>a").html("上传中...");
}*/
</script>
