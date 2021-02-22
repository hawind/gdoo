<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo isset($title) ? $title : ''; ?></title>
<link href="<?php echo URL::to('assets'); ?>/css/app.css" rel="stylesheet">
</head>
<body>

<div class="text-center wrapper-xl">

    <i class="fa fa-5x fa-ban text-muted"></i>
    <h1><?php echo isset($title) ? $title : ''; ?></h1>
    <h4><?php echo isset($message) ? $message : ''; ?></h4>

  	<div class="padder-v m-t">
  		<a href="javascript:history.back();" class="btn btn-sm btn-default">返回上一页</a>
  		<a href="<?php echo URL::to('/'); ?>" class="btn btn-sm btn-info">返回首页</a>
  	</div>
	<div class="m-t">
    	<small class="text-muted">© {{date('Y')}}</small>
	</div>
</div>
</body>
</html>