<?php

require __DIR__.'/macros.php';
require __DIR__.'/sql.php';

View::composer('*', function ($view) {
    $shared = View::getShared();
    $licenseType = env('LICENSE_TYPE', '开源版');
    $view->with('title', 'Gdoo');
    $view->with('powered', 'Powered By Gdoo');
    $view->with('version', '<a target="_blank" href="http://www.gdoo.net">Gdoo</a> '.$shared['version'].' '.$licenseType);
});

/*
 * 判断是否为empty
 */
function not_empty($data)
{
    return !empty($data);
}

/*
 * 主动抛出异常
 */
function abort_error($message, $code = 200)
{
    throw new App\Exceptions\AbortException($message, $code);
}