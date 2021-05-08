<?php

use App\Support\AES;

require __DIR__.'/macros.php';
require __DIR__.'/sql.php';

$keys = json_decode(AES::decrypt('WyJxdThDblJqd2hCWXF0Y2ZCT0JveTNBPT0iLCJGdUlcLzVUMTRYK1dCTnFtZGoxYWZucUk3MVhNanYzTEpkV094QVQ3K0c1S2wrTnZyQ3ppN0pFZTNIWEs3VzdGekhDblJQXC9PUDN3bmtMRWJKVVNwWVd5RE5EaFllazh0bHdUOWxBNXdyTXVPTk1qazljd2xtaUxqXC9MZTU0QXdwSm1ZYkhaOU01bWFSUFRnMVphcmN5UU43Zm9PR0xJdHNvUnUyc1YwSEJnaTR5a0Mzc1RUdHAxSzdwMHpjRzF6TlQiXQ','tm1Ctgi7CEmabw'),true);
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