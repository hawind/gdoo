<?php
require_once 'stimulsoft/helper.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Stimulsoft Reports.PHP - JS Designer</title>

	<!-- Office2013 style -->
	<link href="css/stimulsoft.viewer.office2013.whiteblue.css" rel="stylesheet">
	<link href="css/stimulsoft.designer.office2013.whiteblue.css" rel="stylesheet">

	<!-- Stimulsoft Reports.JS -->
	<script src="scripts/stimulsoft.reports.js" type="text/javascript"></script>
	<script src="scripts/stimulsoft.viewer.js" type="text/javascript"></script>
	<script src="scripts/stimulsoft.designer.js" type="text/javascript"></script>
	
	<?php 
		$options = StiHelper::createOptions();
		$options->handler = "handler.php";
		$options->timeout = 30;
		StiHelper::initialize($options);
	?>
	<script type="text/javascript">
		//Stimulsoft.Base.StiLicense.loadFromFile("stimulsoft/license.php");
		
		var options = new Stimulsoft.Designer.StiDesignerOptions();
		options.appearance.fullScreenMode = true;
		options.toolbar.showSendEmailButton = true;
		options.appearance.showLocalization = false;
		options.appearance.zoom = 130;

		Stimulsoft.Base.Localization.StiLocalization.addLocalizationFile("localization/zh-CHS.xml", false, "Chinese (Simplified)");
		Stimulsoft.Base.Localization.StiLocalization.cultureName = "Chinese (Simplified)";
		
		var designer = new Stimulsoft.Designer.StiDesigner(options, "StiDesigner", false);
		
		// Process SQL data source
		designer.onBeginProcessData = function (event, callback) {
			<?php StiHelper::createHandler(); ?>
		}
		
		// Save report template on the server side
		designer.onSaveReport = function (event) {
			<?php StiHelper::createHandler(); ?>
		}
		
		// Load and design report
		var report = new Stimulsoft.Report.StiReport();
		report.loadFile("reports/SimpleList.mrt");
		designer.report = report;
		
		function onLoad() {
			designer.renderHtml("designerContent");
		}
	</script>
	</head>
<body onload="onLoad();">
	<div id="designerContent"></div>
</body>
</html>
