<?php namespace Gdoo\Customer\Controllers;

use Auth;
use Session;
use Request;

use Gdoo\User\Models\User;
use Gdoo\Index\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * 经销商业务员登录专用接口
     */
    public function salemanAction()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();

            if (empty($gets['username'])) {
                return $this->json('客户代码不能为空。');
            }

            $user = User::where('username', $gets['username'])
            ->where('status', 1)
            ->where('group_id', 2)->first();

            if ($user->id > 0) {
                Auth::login($user, true);
                Session::put('auth_totp', true);
                return $this->json('登录成功。', true);
            } else {
                return $this->json('登录失败，客户代码无效。');
            }
        }
        return $this->json('登录失败。');
    }

    public function json($data, $status = false, $type = 'primary')
    {
        $json = [];
        $json['status'] = $status;
        $json['state'] = $status;
        $json['info'] = $type;
        $json['data'] = $data;
        return response()->json($json);
    }
}
