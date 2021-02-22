<?php namespace Gdoo\User\Hooks;

use DB;
use Gdoo\User\Models\User;

class UserHook
{
    static $linkOptions = [];

    public function onBeforeForm($params) {
        return $params;
    }

    public function onAfterForm($params) {
        return $params;
    }

    public function onBeforeStore($params) 
    {
        $gets = $params['gets'];
        $_user = $gets['user'];

        // 设置用户组
        $_user['group_id'] = 1;

        $user = User::findOrNew($_user['id']);

        if ($_user['password']) {
            $user->password = bcrypt($_user['password']);
            $user->password_text = $_user['password'];
        }

        $user->fill($_user)->save();

        // 终止执行的进程后
        $params['terminate'] = false;
        return $params;
    }

    public function onAfterStore($params) {
        return $params;
    }

    public function onBeforeDelete($params) {
        return $params;
    }
    
}
