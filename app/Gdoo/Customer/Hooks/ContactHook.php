<?php namespace Gdoo\Customer\Hooks;

use Validator;
use DB;

use Gdoo\User\Models\User;
use Gdoo\Customer\Models\Contact;

class ContactHook
{
    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) 
    {
        $master = $params['master'];

        $_user = [
            'role_id' => 95,
            'group_id' => 3,
            'username' => $master['code'],
            'name' => $master['name'],
            'phone' => $master['phone'],
            'status' => 1,
        ];

        $v = Validator::make($_user, [
            'username' => 'unique:user,username,'.$master['user_id']
        ], [], ['username' => '编码']);
        if ($v->fails()) {
            abort_error($v->errors()->first('username'));
        }

        // 更新用户表
        $user = User::findOrNew($master['user_id']);
        // 密码处理
        if (empty($master['password'])) {
            unset($master['password']);
        } else {
            $user->password = bcrypt($master['password']);
            $master['password'] = $user['password'];
        }
        $user->fill($_user)->save();
        $master['user_id'] = $user->id;
        $params['master'] = $master;

        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        $ids = $params['ids'];
        $userIds = Contact::whereIn('id', $ids)->pluck('user_id');
        User::whereIn('id', $userIds)->delete();
        return $params;
    }
}
