<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>信息提示</title>
<link href="<?php echo mix('/assets/dist/vendor.min.css'); ?>" rel="stylesheet">
<link href="<?php echo mix('/assets/dist/gdoo.min.css'); ?>" rel="stylesheet">
<style type="text/css">
.window {
	font: 12px 'Lucida Grande', Verdana, sans-serif;
	color: #58666e;
	margin-left:-240px;
	margin-top:-100px;position:absolute;left:50%;top:50%;display:block;
	width: 480px;
	height: 160px;
}
.data { padding: 5px 0; font-size: 16px; text-align: center; }
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
	<div class="panel">
	  	<div class="panel-body">
	    	<div class="data">
	    		<?php echo $data; ?>
            </div>
	  	</div>
	  	<div class="panel-footer">
            <a class="btn btn-sm btn-default" href="javascript:javascript:history.go(-1);"><i class="fa fa-mail-reply"></i> 返回</a>
            <!--
            <a href="javascript:top.location.href=<?php echo URL::to('/'); ?>">返回首页</a>
            -->
	  	</div>
	</div>
</div>
</body>
</html>