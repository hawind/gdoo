<?php

require __DIR__.'/macros.php';
require __DIR__.'/sql.php';

$keys = decrypt('eyJpdiI6Imp2a0w1ck5lUS9jWTRKbXVWNHg3VVE9PSIsInZhbHVlIjoiRTcwU2pHZ3JMZEltM1RtU3diNjZmbXpONVl4Ymh3WHR3Ujl4UmEyUjRZdGFFVGdCRURoeXBONmJMdGlTODRUVWtTZ2k5VTJNVVB5ZHN3R2FMU0FkdHpVaGwxZndVaWlaNi9acGZXaUl0eFR0OFpYWC9pVEQ2YU9QK2MyQXVOTDFBa09Lc3hwQWlEanlwMWFSSXFQV2plb1VlQXFtQTdJNzZBRmlFQ3ZKWHJVOHd3SVovd2hhQ3JFU2NUem1hMWhvOVptcUFIVTZLa1Y3OS9ZWTQ3YlArUT09IiwibWFjIjoiZWVjODM4YzdiZjAzNjI0YWQxMTg1NmIyNGJhMDU5M2Q3ZWRhMmY5YzkxMGI4NDM0ZDBhZDY1YWJiZThiMDNjMiJ9');
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