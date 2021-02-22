<link rel="stylesheet" type="text/css" href="{{$asset_url}}/vendor/webuploader/webuploader.css">
<link rel="stylesheet" type="text/css" href="{{$asset_url}}/vendor/webuploader/demo.css">

<div id="uploader" class="wu-example">
    <div class="queueList">
        <div id="dndArea" class="placeholder">
            <div id="filePicker"></div>
            <p>或将文件拖到这里，单次最多可选99个文件</p>
        </div>
    </div>
    <div class="statusBar" style="display:none;">
        <div class="progress">
            <span class="text">0%</span>
            <span class="percentage"></span>
        </div><div class="info"></div>
        <div class="btns">
            <div id="filePicker2"></div><div class="uploadBtn">开始上传</div>
        </div>
    </div>
</div>

<script>
var BASE_URL    = '{{$asset_url}}/vendor/webuploader';
var SERVER_URL  = '{{$SERVER_URL}}';
var UPLOADER_ID = '{{$key}}'.replace('.', '_');
</script>
<script type="text/javascript" src="{{$asset_url}}/vendor/webuploader/webuploader.nolog.min.js"></script>
<script type="text/javascript" src="{{$asset_url}}/vendor/webuploader/demo.js"></script>