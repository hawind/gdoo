<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $data = $next($request);
        if ($data instanceof \Illuminate\Http\JsonResponse) {
            $data->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }
}
