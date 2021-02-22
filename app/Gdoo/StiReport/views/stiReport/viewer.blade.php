<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Report Viewer - {{$setting['title']}}</title>

	<!-- Office2013 style -->
	<link href="{{$asset_url}}/vendor/stimulsoft/css/stimulsoft.viewer.office2013.whiteblue.css" rel="stylesheet">

	<!-- Stimulsoft Reports.JS -->
	<script src="{{$asset_url}}/vendor/stimulsoft/scripts/stimulsoft.reports.js" type="text/javascript"></script>
	<!-- Stimulsoft JS Viewer -->
	<script src="{{$asset_url}}/vendor/stimulsoft/scripts/stimulsoft.viewer.js" type="text/javascript"></script>
	<script type="text/javascript">
		var options = new Stimulsoft.Viewer.StiViewerOptions();
		options.appearance.fullScreenMode = true;
		options.toolbar.showSendEmailButton = true;

		Stimulsoft.Base.Localization.StiLocalization.addLocalizationFile("{{$asset_url}}/vendor/stimulsoft/localization/zh-CHS.xml", false, "Chinese (Simplified)");
		Stimulsoft.Base.Localization.StiLocalization.cultureName = "Chinese (Simplified)";
		
		var viewer = new Stimulsoft.Viewer.StiViewer(options, "StiViewer", false);
		
		// Process SQL data source
		viewer.onBeginProcessData = function (event, callback) {
		}
		
		// Manage export settings on the server side
		viewer.onBeginExportReport = function (args) {
			//args.fileName = "MyReportName";
		}
		
		// Process exported report file on the server side
		/*viewer.onEndExportReport = function (event) {
			event.preventDefault = true; // Prevent client default event handler (save the exported report as a file)
		}*/
		
		// Send exported report to Email
		viewer.onEmailReport = function (event) {
		}
		
		var report = new Stimulsoft.Report.StiReport();
		report.loadFile("{{$public_url}}/reports/delivery.mrt");

		// 加载数据
		var ds = new Stimulsoft.System.Data.DataSet("data");
		ds.readJsonFile("{{$public_url}}/reports/delivery.json");
		report.regData("data", "data", ds);

		viewer.report = report;
		
		function onLoad() {
			viewer.renderHtml("viewerContent");
		}
	</script>
</head>

<style>
.stiJsViewerPage > table > tbody > tr:first-child { display:none; }
.stiJsViewerPage > table > tbody > tr:last-child { display:none; }
</style>

<body onload="onLoad();">
	<div id="viewerContent"></div>
</body>
</html>
