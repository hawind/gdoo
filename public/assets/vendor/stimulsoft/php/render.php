<?php
require_once 'stimulsoft/helper.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Stimulsoft Reports.PHP - Render & Export</title>

	<!-- Stimulsoft Reports.JS -->
	<script src="scripts/stimulsoft.reports.js" type="text/javascript"></script>
	
	<?php 
		$options = StiHelper::createOptions();
		$options->handler = "handler.php";
		$options->timeout = 30;
		StiHelper::initialize($options);
	?>
	<script type="text/javascript">
		function onLoad() {
			// Load and show report
			var report = new Stimulsoft.Report.StiReport();
			report.loadFile("reports/SimpleList.mrt");
			
			// Process SQL data source
			report.onBeginProcessData = function (event, callback) {
				<?php StiHelper::createHandler(); ?>
			}
			
			report.renderAsync(function() {
				var pdfData = report.exportDocument(Stimulsoft.Report.StiExportFormat.Pdf);
			
				// Get report file name
				var fileName = String.isNullOrEmpty(report.reportAlias) ? report.reportName : report.reportAlias;
				// Save data to file
				Stimulsoft.System.StiObject.saveAs(pdfData, fileName + ".pdf", "application/pdf");
			});
		}
	</script>
	</head>
<body onload="onLoad();">
	Render & Export
</body>
</html>
