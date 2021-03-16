<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{{$setting['title']}} - Powered By {{$setting['powered']}}</title>

    <link href="{{mix('/assets/dist/vendor.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{mix('/assets/dist/gdoo.min.css')}}" rel="stylesheet" type="text/css" />

    <script type="text/javascript" src="{{mix('/assets/dist/vendor.min.js')}}"></script>
    <script type="text/javascript" src="{{$asset_url}}/vendor/layer/layer.js"></script>
    <script type="text/javascript" src="{{$asset_url}}/vendor/datepicker/datepicker.js"></script>
    <script type="text/javascript" src="{{$public_url}}/common?s={{time()}}"></script>

    <script type="text/javascript" src="{{mix('/assets/dist/bundle.min.js')}}"></script>
    <script type="text/javascript" src="{{mix('/assets/dist/gdoo.min.js')}}"></script>
</head>
<body class="frame-{{auth()->user()->theme ?: 'lilac'}}">