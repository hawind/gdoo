<!DOCTYPE html>
<html>
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{$setting['title']}}</title>

    <link href="{{mix('/assets/dist/vendor.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{mix('/assets/dist/gdoo.min.css')}}" rel="stylesheet" type="text/css" />

    <link href="{{$asset_url}}/vendor/layui/css/layui.css" rel="stylesheet" type="text/css" />
    <link href="{{$asset_url}}/css/wechat/console.css" rel="stylesheet" type="text/css" />
    <link href="{{$asset_url}}/css/wechat/icon/icon.css" rel="stylesheet" type="text/css" />

    <script type="text/javascript" src="{{mix('/assets/dist/vendor.min.js')}}"></script>
    <script type="text/javascript" src="{{$asset_url}}/vendor/layui/layui.js"></script>
    <script type="text/javascript" src="{{$asset_url}}/vendor/datepicker/datepicker.js"></script>
    <script type="text/javascript" src="{{$public_url}}/common?s={{time()}}"></script>
    <script type="text/javascript" src="{{mix('/assets/dist/gdoo.min.js')}}"></script>
</head>
<body>
    <div class="content-body">
        {{$content}}
    </div>
</body>
</html>