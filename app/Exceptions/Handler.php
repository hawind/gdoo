<?php

namespace App\Exceptions;

use ErrorException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        // 自定义错误
        if ($e instanceof AbortException) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $result = [
                'success' => false,
                'status' => false,
                'code' => $code,
                'data' => $msg,
                'msg' => $msg,
            ];
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($result);
            } else {
                return response()->view('errors.abort', $result);
            }
        } else {
            return parent::render($request, $e);
        }
    }
}
