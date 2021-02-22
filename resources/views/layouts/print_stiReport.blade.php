<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$setting['title']}}</title>
<link rel="stylesheet" href="{{$asset_url}}/dist/app.min.css" type="text/css" />
<script src="{{$public_url}}/common"></script>
<script src="{{$asset_url}}/dist/app.min.js"></script>
<script src="{{$asset_url}}/vendor/datepicker/datepicker.js"></script>
<style type="text/css">
body {
    background: -webkit-linear-gradient(right, #198fb4, #0b6fab);
    background: -o-linear-gradient(right, #198fb4, #0b6fab);
    background: linear-gradient(to left, #198fb4, #0b6fab);
    background-color: #3586b7;
}
.title {
    font-size: 16px;
    font-weight: 400;
    color: #333;
    padding-top: 5px;
}
.title .icon {
    top: 3px;
}
.panel {
    margin: 0 auto 10px auto;
    border-radius: 0;
    border-color: #eee;
}
.panel-body {
    margin: 0 auto;
    padding: 5px;
    width: 1000px;
}

@media print {
    .panel {
        display: none; 
    }
    .main {
        margin: 0 auto;
        border: 0;
    }
}

#lodop-container {
    width: 1000px;
    margin: auto;
    display: none;
}
</style>

<style>
.viewer table:first-of-type > tbody tr:first-child { display:none; }
.viewer table tbody tr:last-child { display:none; }
</style>

<style media="screen">
.main { text-align: center; margin-bottom: 5px; }
.viewer { background-color: #fff; display:inline-block; padding: 20px; }
</style>

</head>

<body onload="onLoad();">

<div class="panel panel-default">
    <div class="panel-body">
        <div class="pull-right">
            <a class="btn btn-default" href="javascript:window.print();"><i class="fa fa-print"></i> HTML打印</a>
            <a class="btn btn-default" href="javascript:startPrint();"><i class="fa fa-print"></i> 打印</a>
            @if(auth()->user()->role_id == 1)
            <a target="_blank" class="btn btn-default" href="{{url('stiReport/stiReport/designer',['template_id' => $template['id']])}}"><i class="fa fa-pencil-square"></i> 设计 </a>
            <a class="btn btn-default" download="{{$template['code']}}.mrt" href="{{$public_url}}/reports/{{$template['code']}}.mrt"><i class="fa fa-file-code-o"></i> 下载模板 </a>
            @endif
            <a class="btn btn-default" href="javascript:window.close();"><i class="fa fa-remove"></i> 关闭</a>
        </div>
        <div class="title"><i class="icon icon-note"></i> 打印预览 <span id="print_msg"></span></div>
    </div>
</div>

<div id="lodop-container">
    <div class="alert alert-warning alert-dismissable m-b-sm">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <div id="lodop_msg"></div>
    </div>
</div>

<div class="main">
    <div id="report_data" class="viewer"></div>
</div>

<script src="{{$asset_url}}/js/gdoo.websocket.js" type="text/javascript"></script>
<script src="{{$asset_url}}/vendor/stimulsoft/scripts/stimulsoft.reports.js" type="text/javascript"></script>

<?php
$report_name = $template['code'];
$report_path = public_path()."/reports/".$report_name.".mrt";
$report_template = '';
if (is_file($report_path)) {
    $report_template = base64_encode(file_get_contents($report_path));
}
?>
<script type="text/javascript">
var reportName = "{{$report_name}}";
var reportTemplate = "{{$report_template}}";
var printData = {{json_encode($print_data, JSON_UNESCAPED_UNICODE)}};
printData.setting = [{
    action: "preview",
    topmargin: 0,
    leftmargin: 0,
    drive: '',
    taskid: '{{$report_name}}',
    template: reportTemplate,
    type: "base64"
}];
// 打印数据保存到本地
localStorage.setItem(reportName, JSON.stringify(printData));

function onLoad() {
    var report = new Stimulsoft.Report.StiReport();
    // 加载模板文件
    if (reportTemplate) {
        report.loadFile("{{$public_url}}/reports/{{$template['code']}}.mrt?s=" + (new Date()).valueOf());
    }
    
    // 加载数据
    var dataSet = new Stimulsoft.System.Data.DataSet("data");
    dataSet.readJson(printData);

    report.dictionary.clear();
    report.regData("data", "data", dataSet);
    report.dictionary.synchronize();

    report.renderAsync(function() {
        var htmlData = report.exportDocument(Stimulsoft.Report.StiExportFormat.Html);
        document.getElementById('report_data').innerHTML = htmlData;
    });
}

var GdooPrint = null;
// 打印机列表
var prints = [];
try {
    var options = {
        url: 'ws://127.0.0.1:6690',
        pingTimeout: 15000, 
        pongTimeout: 10000, 
        reconnectTimeout: 10000,
        pingMsg: "ping"
    }
    GdooPrint = new GdooWebSocket(options);
    GdooPrint.onopen = function(e) {
    }
    GdooPrint.onclose = function (e) {
    }
    GdooPrint.onerror = function (e) {
    }
    GdooPrint.onmessage = function (e) {
        var res = JSON.parse(e.data);
        if (res.type == 'ping') { 
        } else if (res.type == 'printers') { 
            prints = res.data;
        } else {
            labelSuccess(res.data);
        }
    }
    GdooPrint.onreconnectCountDown = function(count) {
        if (count > 0) {
            labelError('打印服务离线' + count + '秒后重新连接');
        } else {
            labelInfo('打印服务重新连接中...');
        }
    }
    GdooPrint.onreconnect = function (e) {
    }
    GdooPrint.onsend = function (e) {
        if(e == 'ping') return false;
    }
} catch (e) {
    labelError(e.message);
}

function startPrint() {
    try {
        GdooPrint.send(JSON.stringify(printData));
    } catch (e) {
        $.toastr('error', e.message);
    }
}

function labelInfo(text) {
    $('#print_msg').html('<span class="label label-info">' + text + '</span>');
}
function labelError(text) {
    $('#print_msg').html('<span class="label label-danger">' + text + '</span>');
}
function labelSuccess(text) {
    $('#print_msg').html('<span class="label label-success">' + text + '</span>');
}
</script>

</body>
</html>
