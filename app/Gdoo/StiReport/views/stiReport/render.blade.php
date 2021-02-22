<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>打印预览</title>
    <script src="{{$asset_url}}/vendor/stimulsoft/scripts/stimulsoft.reports.js" type="text/javascript"></script>
	<script type="text/javascript">
		function onLoad() {
			var report = new Stimulsoft.Report.StiReport();
			report.loadFile("{{$public_url}}/reports/delivery.mrt");
			
			// 加载数据
            var ds = new Stimulsoft.System.Data.DataSet("data");
            ds.readJsonFile("{{$public_url}}/reports/delivery.json");
            report.regData("data", "data", ds);
			report.renderAsync(function() {
				var pdfData = report.exportDocument(Stimulsoft.Report.StiExportFormat.Html);
                document.getElementById('data').innerHTML = pdfData;
			});
		}
	</script>
</head>
<style>
* { padding: 0; margin: 0; position: relative; }
.stiJsViewerPage table:first-of-type > tbody tr:first-child { display:none; }
.stiJsViewerPage table tbody tr:last-child { display:none; }
</style>
<style media="screen">
.body {text-align: center;}
.stiJsViewerPage {display:inline-block;}
</style>
<body onload="onLoad();" class="body">
<div id="data" class="stiJsViewerPage"></div>
</body>
</html>
