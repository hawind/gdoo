<?php namespace Gdoo\User\Controllers;

use DB;
use Auth;
use Hash;
use Request;
use Validator;
use URL;
use File;

use App\Support\Totp;
use App\Support\Pinyin;
use App\Support\License;

use Gdoo\Hr\Models\Hr;
use Gdoo\User\Models\UserPost;
use Gdoo\User\Models\User;

use Gdoo\Index\Controllers\DefaultController;

use function GuzzleHttp\json_encode;

class ProfileController extends DefaultController
{
    public $permission = ['index', 'password', 'avatar', 'secret', 'getUser'];

    // 资料修改
    public function index()
    {
        if (Request::method() == 'POST') {
            
            License::demoCheck();

            $gets = Request::all();

            $user = User::find(Auth::id());

            $rules = [];

            $v = Validator::make($gets, $rules);
            if ($v->fails()) {
                return $this->back()->withErrors($v);
            }
            $user->fill($gets);
            $user->save();
            return $this->json( '资料修改成功。', true);
        }

        $t = new Totp();
        $secretURL = Totp::getURL(Auth::user()->username, Request::server('HTTP_HOST'), Auth::user()->auth_secret, Auth::user()->name);
        $user = User::find(Auth::id());
        return $this->display([
            'user' => $user,
            'secretURL' => $secretURL,
        ]);
    }

    /**
     * 获取用户信息
     */
    public function getUser()
    {
        $user['avatar'] = avatar(auth()->user()->avatar);
        $user['name'] = auth()->user()->name;
        return json_encode($user, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 更新安全密钥
     */
    public function secret()
    {
        if (Request::method() == 'POST') {

            License::demoCheck();

            $id = Request::get('id');
            $t = new Totp();
            $secretKey = $t->generateSecret();
            $data['auth_secret'] = $secretKey;
            User::where('id', $id)->update($data);
            return $this->json($secretKey, true);
        }
    }

    // 修改密码 
    public function password()
    {
        if (Request::method() == 'POST') {

            License::demoCheck();

            $gets = Request::all();

            $user = User::find(Auth::id());

            $rules = [
                'old_password' => 'required',
                'new_password' => 'required|confirmed|different:old_password',
                'new_password_confirmation' => 'required|different:old_password|same:new_password'
            ];

            $attributes = [
                'old_password' => '旧密码',
                'new_password' => '新密码',
                'new_password_confirmation' => '确认新密码'
            ];

            $v = Validator::make($gets, $rules, [], $attributes);
            if ($v->fails()) {
                return $this->json(join('<br>', $v->errors()->all()));
            }

            // 旧密码不正确
            if (Hash::check($gets['old_password'], $user->getAuthPassword()) === false) {
                return $this->back()->withErrors(['old password' => 'old password 不正确。']);
            }

            $user->password = bcrypt($gets['new_password']);
            $user->password_text = $gets['new_password'];
            $user->save();

            return $this->json('密码修改成功。', true);
        }
        $user = User::find(Auth::id());
        return $this->display([
            'user' => $user,
        ]);
    }

    // 用户头像
    public function avatar()
    {
        $gets = Request::all();

        if (Request::method() == 'POST') {

            License::demoCheck();

            if (Request::hasFile('image')) {
                $rules = [
                    'image' => 'image',
                ];
                $v = Validator::make($gets, $rules);

                if ($v->fails()) {
                    return $this->back()->withErrors($v);
                }

                $userId = Auth::id();

                $avatar_path = upload_path('avatar');
                File::isDirectory($avatar_path) or File::makeDirectory($avatar_path, 0777, true, true);

                $file = Request::file('image');
                $filename = $userId.'.'.$file->extension();

                if ($file->move($avatar_path, $filename)) {
                    $user = User::find($userId);
                    $user->avatar = $filename;
                    $user->save();
                    return $this->json($filename, true);
                }
            }
        }
        return $this->render();
    }
}
