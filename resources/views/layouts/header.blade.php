<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{{$setting['title']}} - Powered By {{$setting['powered']}}</title>
    <script type="text/javascript" src="{{$public_url}}/common?v={{$resVersion}}"></script>
    <link href="{{$asset_url}}/vendor/ag-grid/ag-grid.min.css" rel="stylesheet" type="text/css" />
    <link href="{{$asset_url}}/dist/app.min.css?v={{$resVersion}}" rel="stylesheet" type="text/css" />
    <script src="{{$asset_url}}/dist/app.min.js?v={{$resVersion}}" type="text/javascript"></script>
    <script src="{{$asset_url}}/vendor/layer/layer.js" type="text/javascript"></script>
    <script src="{{$asset_url}}/vendor/datepicker/datepicker.js"></script>
    <script src="{{$asset_url}}/vendor/ag-grid/ag-grid.min.js"></script>
    <script src="{{$asset_url}}/dist/bundle.min.js"></script>
    <script>
    agGrid.LicenseManager.setLicenseKey('{{env("AGGRID_LICENSE")}}');
    {{env('DEMO_DATA')}}
    </script>
</head>
<body class="frame-{{auth()->user()->theme ?: 'lilac'}}">