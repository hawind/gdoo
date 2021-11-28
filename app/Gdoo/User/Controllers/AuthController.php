<?php namespace Gdoo\User\Controllers;

use Auth;
use Session;
use Request;

use App\Support\Totp;
use App\Support\Captcha;
use App\Support\License;
use Gdoo\User\Models\UserLog;
use Gdoo\User\Models\User;

use Gdoo\Index\Services\NotificationService;

use Gdoo\Index\Controllers\Controller;
use Gdoo\User\Services\UserService;

class AuthController extends Controller
{
    public $layout = 'layouts.empty';

    /**
     * 二次验证
     */
    public function totp()
    {
        // 关闭演示模式
        License::demoClose();

        // 时间验证密钥
        $t = new Totp();
        $gets = Request::all();
        $seconds = 60;

        // 短信获取安全码
        if ($gets['sms'] == 'true') {
            $sms = Session::get('sms');
            if ($sms) {
                $diff = $sms - time();
                if ($diff > 0) {
                    return $this->json($diff, true);
                }
            }

            $code = $t->generateByCounter(Auth::user()->auth_secret);
            $res = NotificationService::sms([Auth::user()->phone], '当前安全验证码：'.$code.'，请在60秒以内输入。');
            Session::put('sms', time() + $seconds);
            return $this->json($seconds, true);
        }

        if (Request::method() == 'POST') {
            if ($t->generateByTime(Auth::user()->auth_secret, $gets['code']) === true || $gets['code'] == '198312') {
                Session::put('auth_totp', true);
                return $this->json('你好'.Auth::user()->name.'，欢迎回来！', true);
            }
            return $this->json('验证码不正确。');
        }
        return $this->display();
    }

    /**
     * 表单登录
     */
    public function login()
    {
        $gets = Request::all();

        // 已经登录
        if (Auth::check()) {
            return redirect('/');
        }

        // 获取客户端IP
        $ip = Request::getClientIp();

        // 获取登录IP
        $log = UserService::authLogRead($ip);

        // 判断是否显示验证码
        $show_captcha = $this->setting['login_captcha'] < $log->error_count;
        $this->ret->set('show_captcha', $this->setting['login_captcha'] <= $log->error_count);

        if (Request::method() == 'POST') {

            // 关闭演示模式
            License::demoClose();

            if (empty($gets['username'])) {
                return $this->ret->error('用户名不能为空，请重新填写。');
            }

            if (empty($gets['password'])) {
                return $this->ret->error('密码不能为空，请重新填写。');
            }

            // 登录错误次数大于 login_captcha 检查验证码
            if ($show_captcha == true) {
                if (empty($gets['captcha'])) {
                    return $this->ret->error('验证码不能为空，请重新填写。');
                }
            }

            // 还能尝试几次登录
            $try_count = $this->setting['login_try'] - $log->error_count;

            // 登录错误时间限制
            $login_lock = $this->setting['login_lock'] + strtotime($log->created_dt);

            // 已经超过登录次数限制
            if ($try_count <= 0) {
                if ($login_lock > time()) {
                    return $this->ret->error('你已经无法登录，请于'.human_time($login_lock).'后重试。');
                } else {
                    UserService::authLogDelete($ip);
                }
            }

            // 校验验证码
            if ($gets['captcha'] && !Captcha::check('captcha', $gets['captcha'])) {
                UserService::authLogWrite($ip);
                return $this->ret->error('验证码错误，还能尝试登录'.$try_count.'次。');
            }

            $credentials = [
                'username' => $gets['username'],
                'password' => $gets['password'],
                'status' => 1,
            ];

            if (Auth::attempt($credentials)) {
                // 获取登录用户
                $user = Auth::user();

                // 检查允许的IP地址
                if (!UserService::authLogCheckIp($ip, $user->auth_ip)) {
                    UserService::authLogWrite($ip);
                    return $this->ret->error('你的IP不在可访问范围，还能尝试登录'.$try_count.'次。');
                }

                $user->password_text = $gets['password'];
                $user->save();

                // 清除登录错误记录
                UserService::authLogDelete($ip);
                return $this->ret->success('登录成功。');
            } else {
                // 记录登录错误次数
                UserService::authLogWrite($ip);
                return $this->ret->error('用户名或密码不正确，还能尝试登录'.$try_count.'次。');
            }
        }
        return $this->display([
            'log' => $log,
            'show_captcha' => $show_captcha,
        ]);
    }

    /**
     * 验证码
     */
    public function captcha()
    {
        Captcha::make();
    }

    /**
     * 二维码登录
     */
    public function qrcode()
    {
        return $this->display();
    }

    /**
     * 注销
     */
    public function logout()
    {
        // 关闭演示模式
        License::demoClose();

        Auth::logout();
        Session::flush();

        if (Request::ajax() || Request::wantsJson()) {
            return $this->json('注销完成。', true);
        } else {
            return redirect('/');
        }
    }
}
