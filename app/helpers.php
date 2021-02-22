<?php

/**
 * 公共文件目录
 */
function public_path($path = '')
{
    return base_path('public').($path ? '/'.$path : $path);
}

/**
 * 文件上传目录
 */
function upload_path($path = '')
{
    return public_path('uploads').($path ? '/'.$path : $path);
}

/*
 * 主动抛出异常
 */
function abort_error($message, $code = 200)
{
    throw new App\Exceptions\AbortException($message, $code);
}

/*
 * 判断是否为empty
 */
function not_empty($data)
{
    return !empty($data);
}
