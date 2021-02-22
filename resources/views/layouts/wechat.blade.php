<!DOCTYPE html>
<html>
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{$setting['title']}}</title>

    <link href="{{$asset_url}}/css/wechat/console.css" rel="stylesheet" type="text/css" />
    <link href="{{$asset_url}}/dist/app.min.css" rel="stylesheet" type="text/css" />
    <link href="{{$asset_url}}/vendor/layui/css/layui.css" rel="stylesheet" type="text/css" />
    <script src="{{$public_url}}/index/api/common" type="text/javascript"></script>
    <script src="{{$asset_url}}/vendor/layui/layui.js" type="text/javascript"></script>
    <script src="{{$asset_url}}/dist/app.min.js" type="text/javascript"></script>
    <link href="{{$asset_url}}/css/wechat/icon/icon.css" rel="stylesheet" type="text/css" />

</head>
<body>
    <div class="content-body">
        {{$content}}
    </div>
</body>
</html>