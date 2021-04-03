<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Report Designer - {{$setting['title']}}</title>

	<!-- Office2013 style -->
	<link href="{{$asset_url}}/vendor/stimulsoft/css/stimulsoft.viewer.office2013.whiteblue.css" rel="stylesheet">
	<link href="{{$asset_url}}/vendor/stimulsoft/css/stimulsoft.designer.office2013.whiteblue.css" rel="stylesheet">

	<script src="{{$asset_url}}/vendor/jquery.js" type="text/javascript"></script>

	<!-- Stimulsoft Reports.JS -->
	<script src="{{$asset_url}}/vendor/stimulsoft/scripts/stimulsoft.reports.js" type="text/javascript"></script>
	<script src="{{$asset_url}}/vendor/stimulsoft/scripts/stimulsoft.viewer.js" type="text/javascript"></script>
	<script src="{{$asset_url}}/vendor/stimulsoft/scripts/stimulsoft.designer.js" type="text/javascript"></script>
	<script src="{{$asset_url}}/vendor/stimulsoft/scripts/stimulsoft.reports.export.js" type="text/javascript"></script>
	<script type="text/javascript">
		var report_name = "{{$report_name}}";
		var report_file = "{{$report_file}}";
		var printData = JSON.parse(localStorage.getItem(report_name));
		var options = new Stimulsoft.Designer.StiDesignerOptions();
		options.appearance.fullScreenMode = true;
		options.toolbar.showSendEmailButton = true;
		options.appearance.showLocalization = false;
		options.appearance.zoom = 120;

		Stimulsoft.Base.Localization.StiLocalization.addLocalizationFile("{{$asset_url}}/vendor/stimulsoft/localization/zh-CHS.xml", false, "Chinese (Simplified)");
		Stimulsoft.Base.Localization.StiLocalization.cultureName = "Chinese (Simplified)";
		
		var designer = new Stimulsoft.Designer.StiDesigner(options, "StiDesigner", false);
		
		designer.onBeginProcessData = function (event, callback) {}
		
		// 保存报表
		designer.onSaveReport = function (event) {
			var data = event.report.saveToJsonString();
			$.post('/stiReport/StiReport/saveReport', {fileName: report_name, data: data}, function(res) {
				Stimulsoft.System.StiError.errorMessageForm.show(res.msg, res.success);
			});
		}

		var report = new Stimulsoft.Report.StiReport();
		report.reportName = report_name;
		// 加载模板文件
		if (report_file) {
			report.loadFile("{{$public_url}}/reports/" + report_file + '.mrt');
		}
	
		// 加载数据
		var dataSet = new Stimulsoft.System.Data.DataSet("data");
		dataSet.readJson(printData);

		report.dictionary.clear();
		report.regData("data", "data", dataSet);
		report.dictionary.synchronize();

		designer.report = report;
		
		function onLoad() {
			designer.renderHtml("designerContent");
		}
	</script>
</head>
<style>
.stiJsViewerPage > table > tbody > tr:first-child { display:none; }
.stiJsViewerPage > table > tbody > tr:last-child { display:none; }
</style>
<body onload="onLoad();">
	<div id="designerContent"></div>
</body>
</html>
