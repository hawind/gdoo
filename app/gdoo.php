<?php

require __DIR__.'/macros.php';
require __DIR__.'/sql.php';

$keys = get_gdoo_var();
View::composer('*', function ($view) use($keys) {
    foreach($keys as $k => $v) {
        $view->with($k, $v);
    }
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