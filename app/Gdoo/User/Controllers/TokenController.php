<?php namespace Gdoo\User\Controllers;

use Auth;
use Session;
use Request;

use App\Support\JWT;

use Gdoo\User\Models\User;
use Gdoo\Index\Models\Access;

use Gdoo\Index\Controllers\Controller;
use Gdoo\User\Services\UserAssetService;

class TokenController extends Controller
{
    protected function createToken($userId)
    {
        $payload = array(
            'sub' => $userId,
            'iat' => time(),
            // 一年有效
            'exp' => time() + (365 * 24 * 60 * 60),
        );
        return JWT::encode($payload, config('app.key'));
    }

    /**
     * APP登录
     */
    public function loginAction()
    {
        if (Request::isJson()) {
            $gets = json_decode(Request::getContent(), true);
        } else {
            $gets = Request::all();
        }

        if (empty($gets['username'])) {
            return response()->json(['message'=>'账户不能为空。','success'=>false]);
        }

        if (empty($gets['password'])) {
            return response()->json(['message'=>'密码不能为空。','success'=>false]);
        }

        $credentials = [
            'username' => $gets['username'],
            'password' => $gets['password'],
            'status' => 1
        ];

        if (Auth::validate($credentials)) {
            $user = User::where('username', $gets['username'])->first();

            if ($user['auth_device']) {
                if (empty($gets['deviceId'])) {
                    return response()->json(['message'=>'设备ID不能为空。','success'=>false]);
                }

                // 设备ID为空时自动绑定
                if ($user['auth_device_id'] == '') {
                    $user->auth_device_id = $gets['deviceId'];
                } else {
                    // 存在设备ID检查是否匹配
                    $auth_device_id = explode(PHP_EOL, $user['auth_device_id']);
                    if (in_array($gets['deviceId'], $auth_device_id) == false) {
                        return response()->json(['message'=>'设备ID错误，请联系相关人员。','success'=>false]);
                    }
                }
            }

            // 保存用户表数据
            $user->save();

            $assets = UserAssetService::getRoleAssets($user->role_id);
            return response()->json([
                'token'   => $this->createToken($user->id),
                'access'  => $assets,
                'user'    => $user,
                'success' => 1,
            ]);
        }
        return response()->json(['message'=>'账户或密码错误。', 'success'=>false]);
    }

    public function logoutAction()
    {
        return response('注销完成。');
    }
}
