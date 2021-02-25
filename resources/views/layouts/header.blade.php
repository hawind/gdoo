<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{{$setting['title']}} - Powered By {{$setting['powered']}}</title>
    <link href="{{$asset_url}}/vendor/ag-grid/ag-grid.min.css" rel="stylesheet" type="text/css" />
    <link href="{{mix('/assets/dist/app.min.css')}}" rel="stylesheet" type="text/css" />

    <script type="text/javascript" src="{{$public_url}}/common?v={{time()}}"></script>
    <script type="text/javascript" src="{{mix('/assets/dist/app.min.js')}}"></script>
    <script type="text/javascript" src="{{mix('/assets/dist/bundle.min.js')}}"></script>

    <!-- 第三方库 -->
    <script type="text/javascript" src="{{$asset_url}}/vendor/layer/layer.js"></script>
    <script type="text/javascript" src="{{$asset_url}}/vendor/datepicker/datepicker.js"></script>
    <script type="text/javascript" src="{{$asset_url}}/vendor/ag-grid/ag-grid.min.js"></script>

    <script>
    {{env("AGGRID_LICENSE")}}
    </script>
</head>
<body class="frame-{{auth()->user()->theme ?: 'lilac'}}">