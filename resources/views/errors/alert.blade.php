<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>信息提示</title>
<link href="<?php echo URL::to('assets'); ?>/css/app.css" rel="stylesheet">
<style type="text/css">
.window {
	font: 12px 'Lucida Grande', Verdana, sans-serif;
	color: #58666e;
	margin-left:-240px;
	margin-top:-100px;position:absolute;left:50%;top:50%;display:block;
	width: 480px;
	height: 160px;
}
.h5 { padding: 5px 0; }
a {
	color:#999;
}
a:hover {
	color:#666;
}
</style>
</head>
<body>
<div class="window">
	<div class="panel b-a">
  		<div class="panel-heading no-border bg-info dk"><strong>信息提醒</strong></div>
	  	<div class="panel-body">
	    	<p class="h5" align="center">
	    		<strong><?php echo $message; ?></strong>
	    	</p>
	    	<?php if($html): ?>
	    		<div align="center"><?php echo $html; ?></div>
	    	<?php endif; ?>
	  	</div>
	  	<div class="panel-footer">
	  		<a href="<?php echo URL::to('/'); ?>">返回首页</a>
	  	</div>
	</div>
</div>
</body>
</html>