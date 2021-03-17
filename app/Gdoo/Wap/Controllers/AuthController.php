<?php namespace Gdoo\Wap\Controllers;

use Auth;
use Cache;
use Request;
use DB;
use Hash;

use Gdoo\User\Models\UserAsset;
use Gdoo\System\Models\Setting;

use Gdoo\Index\Controllers\Controller;
use Gdoo\User\Services\UserAssetService;

class AuthController extends Controller
{
    public $permission = [];

    /**
     * 用户登录
     */
    public function login()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $username = $gets['username'];
            $password = $gets['password'];
            $ret = [];
            $user = DB::table('user')->where('username', $username)->first();
            if (Hash::check($password, $user['password'])) {
                $ret['access'] = UserAssetService::getRoleAssets($user['role_id']);
                $ret['token'] = create_token($user['id'], 365);
                $ret['user'] = $user;
                return $this->json($ret, true);
            } else {
                return $this->json('帐号或密码不正确。');
            }
        }
    }

    /**
     * 用户注销
     */
    public function logout()
    {
        Auth::logout();
        return $this->json('解绑成功。', true);
    }
}
