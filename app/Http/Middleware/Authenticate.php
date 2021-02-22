<?php namespace App\Http\Middleware;

use Closure;
use Request;
use Illuminate\Contracts\Auth\Guard;

use Gdoo\User\Services\UserService;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                abort_error('Unauthorized('.$request->path().').', 401);
            } else {
                return response('<script type="text/javascript">top.location.href="'.url('user/auth/login').'";</script>');
            }
        } else {
            // 需要二次验证
            if (UserService::wantsTotp()) {
                return redirect('user/auth/totp');
            }

            // 无权限操作
            if (UserService::authorise() == 0) {
                $response = '权限不足('.$request->path().')';
                abort_error($response, 403);
            }
        }
        return $next($request);
    }
}
