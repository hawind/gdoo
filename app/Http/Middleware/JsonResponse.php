<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        }
        return $response;
    }
}
