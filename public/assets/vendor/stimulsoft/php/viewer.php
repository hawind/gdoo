<?php
require_once 'stimulsoft/helper.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Stimulsoft Reports.PHP - JS Viewer</title>

	<!-- Office2013 style -->
	<link href="css/stimulsoft.viewer.office2013.whiteblue.css" rel="stylesheet">

	<!-- Stimulsoft Reports.JS -->
	<script src="scripts/stimulsoft.reports.js" type="text/javascript"></script>
	<!-- Stimulsoft JS Viewer -->
	<script src="scripts/stimulsoft.viewer.js" type="text/javascript"></script>
	
	<?php 
		$options = StiHelper::createOptions();
		$options->handler = "handler.php";
		$options->timeout = 30;
		StiHelper::initialize($options);
	?>
	<script type="text/javascript">
		//Stimulsoft.Base.StiLicense.loadFromFile("stimulsoft/license.php");
		
		var options = new Stimulsoft.Viewer.StiViewerOptions();
		options.appearance.fullScreenMode = true;
		options.toolbar.showSendEmailButton = true;

		Stimulsoft.Base.Localization.StiLocalization.addLocalizationFile("localization/zh-CHS.xml", false, "Chinese (Simplified)");
		Stimulsoft.Base.Localization.StiLocalization.cultureName = "Chinese (Simplified)";
		
		var viewer = new Stimulsoft.Viewer.StiViewer(options, "StiViewer", false);
		
		// Process SQL data source
		viewer.onBeginProcessData = function (event, callback) {
			<?php StiHelper::createHandler(); ?>
		}
		
		// Manage export settings on the server side
		viewer.onBeginExportReport = function (args) {
			<?php //StiHelper::createHandler(); ?>
			//args.fileName = "MyReportName";
		}
		
		// Process exported report file on the server side
		/*viewer.onEndExportReport = function (event) {
			event.preventDefault = true; // Prevent client default event handler (save the exported report as a file)
			<?php StiHelper::createHandler(); ?>
		}*/
		
		// Send exported report to Email
		viewer.onEmailReport = function (event) {
			<?php StiHelper::createHandler(); ?>
		}
		
		// Load and show report
		var report = new Stimulsoft.Report.StiReport();
		report.loadFile("reports/SimpleList.mrt");
		viewer.report = report;
		
		function onLoad() {
			viewer.renderHtml("viewerContent");
		}
	</script>
	</head>
<body onload="onLoad();">
	<div id="viewerContent"></div>
</body>
</html>
