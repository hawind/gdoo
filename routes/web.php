<?php

use Illuminate\Support\Str;

// 设置用户错误处理器
function exception_error_handler($errno, $errstr, $errfile, $errline) {
}
set_error_handler("exception_error_handler");

$uris = [];

foreach (['module', 'controller', 'action'] as $key => $uri) {
    $uris[$uri] = Request::segment($key + 1, 'index');
}

// 初始化所有模块
Gdoo\Model\Services\ModuleService::allWithDetails();

$path = Request::path();

if (strpos($path, 'calendar/caldav') === 0) {
    App\Support\DAV::caldav('calendar/caldav');
}

if (strpos($path, 'common') === 0) {
    app('Gdoo\Index\Controllers\ApiController')->common();
}

// 首字母大写
$controller = Str::studly($uris['controller']);
$action = 'Gdoo\\'.ucfirst($uris['module']).'\\Controllers\\'.$controller.'Controller@'.$uris['action'];

$method = Request::method();

if ($method == 'GET') {
    Route::get($path, $action)->middleware('json');
}

if ($method == 'POST') {
    Route::post($path, $action)->middleware('json');
}

if ($method == 'OPTIONS') {
    Route::options($path, $action)->middleware('json');
}

View::addLocation(base_path('app/Gdoo/'.ucfirst(Request::module()).'/views'));
