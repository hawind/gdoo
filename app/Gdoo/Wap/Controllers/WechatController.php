<?php namespace Gdoo\Wap\Controllers;

use Auth;
use Cache;
use Request;
use DB;
use Hash;

use Gdoo\User\Models\UserAsset;
use Gdoo\System\Models\Setting;
use Gdoo\Wechat\Services\WechatService;

use Gdoo\Index\Controllers\Controller;
use Gdoo\User\Services\UserAssetService;

class WechatController extends Controller
{
    public $permission = [];

    public function config()
    {
        $config = Setting::where('type', 'wechat')->get()->pluck('value', 'key');
        return $this->json($config, true);
    }

    /**
     * 微信用户绑定
     */
    public function login()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $username = $gets['username'];
            $password = $gets['password'];
            $openid = $gets['openid'];
            $ret = [];
            $wechat = DB::table('wechat_user')->where('openid', $openid)->first();
            $user = DB::table('user')->where('username', $username)->first();
            if (Hash::check($password, $user['password'])) {
                if ($wechat) {
                    DB::table('wechat_user')
                        ->where('openid', $openid)
                        ->update(['user_id' => $user['id']]);
                } else {
                    DB::table('wechat_user')->insert([
                        'openid' => $openid,
                        'user_id' => $user['id'],
                    ]);
                }
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
     * 获取微信登录数据
     */
    public function wxAuthorize()
    {
        try {
            $app = WechatService::getApp();
            $user = $app->oauth->user();
            $openid = $user['id'];
            $wechat = DB::table('wechat_user')->where('openid', $openid)->first();
            $ret = ['openid' => $openid];
            if ($wechat) {
                $user = DB::table('user')->where('id', $wechat['user_id'])->first();
                $ret['access'] = UserAssetService::getRoleAssets($user['role_id']);
                $ret['token'] = create_token($user['id'], 365);
                $ret['user'] = $user;
                return $this->json($ret, true);
            } else {
                return $this->json($ret);
            }
        } catch (\Exception $e) {
            return $this->json($e->getMessage());
        }
    }

    /**
     * 微信用户解绑
     */
    public function logout()
    {
        $openid = Request::get('openid');
        DB::table('wechat_user')->where('openid', $openid)->delete();
        Auth::logout();
        return $this->json('解绑成功。', true);
    }

    /**
     * 微信获取配置
     */
    public function jsConfig()
    {
        $app = WechatService::getApp();
        $url = Request::get('url');
        $app->jssdk->setUrl($url);
        return $app->jssdk->buildConfig(['chooseImage', 'getLocation'], false, false, true);
    }

    /**
     * 微信获取配置
     */
    public function mapGeocoder()
    {
        $location = Request::get('location');
        $text = file_get_contents('https://apis.map.qq.com/ws/geocoder/v1/?location=' . $location . '&key=W6FBZ-JV4K4-O6WU5-XGZOL-GMTPJ-KIFWJ&get_poi=0');
        return $text;
    }
}
