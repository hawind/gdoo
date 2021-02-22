<?php namespace Gdoo\User\Services;

use Auth;
use Request;
use DB;
use Session;

use Gdoo\User\Models\Department;
use Gdoo\User\Models\User;
use AIke\User\Models\UserAsset;
use Gdoo\User\Models\Role;

use Gdoo\System\Models\SystemLog;

class UserService
{
    public static function getUser($user_id = 0) {
        $user = null;
        if ($user_id == 0) {
            $user = auth()->user();
        }
        $user = User::find($user_id);
        return $user;
    }

    /**
     * 验证权限
     */
    public static function authorise($action = null, $asset_name = null)
    {
        if ($asset_name === null) {
            $asset_name = Request::module();
        }

        if ($action === null) {
            $action = Request::controller().'.'.Request::action();
        } else {
            if (substr_count($action, '.') === 0) {
                $action = Request::controller().'.'.$action;
            }
        }

        return UserAssetService::check(Auth::user()->role_id, $action, $asset_name);
    }

    /**
     * 验证查看权限
     */
    public static function authoriseAccess($action = null, $asset_name = null)
    {
        $level = static::authorise($action, $asset_name);

        $user = Auth::user();

        // 本人
        if ($level == 1) {
            return [$user->id];
        }

        // 本人和下属
        if ($level == 2) {
            $roles = Role::from(DB::raw('role as node, role as parent'))
            ->select(['node.id'])
            ->whereRaw('node.lft BETWEEN parent.lft AND parent.rgt')
            ->where('parent.id', $user->role_id)
            ->pluck('id');
            return User::whereIn('role_id', $roles)->pluck('id')->toArray();
        }

        // 部门所有人
        if ($level == 3) {
            $departments = Department::from(DB::raw('department as node, department as parent'))
            ->select(['node.id'])
            ->whereRaw('node.lft BETWEEN parent.lft AND parent.rgt')
            ->where('parent.id', $user->department_id)
            ->pluck('id');
            return User::whereIn('department_id', $departments)->pluck('id')->toArray();
        }

        // 销售团队
        if ($level == 5) {
            $regions = DB::table('customer_region')->get()->toNested();
            // 审批权限
            $owners = DB::table('customer_region')
            ->where('owner_user_id', $user->id)
            ->get()->toArray();
            // 查询权限
            $assists = DB::table('customer_region')
            ->whereRaw(db_instr('owner_assist', $user->id))
            ->get()->toArray();
            $ids = [];
            foreach($owners as $v) {
                $ids = array_merge($ids, $regions[$v['id']]['parent']);
                $ids = array_merge($ids, $regions[$v['id']]['child']);
            }
            foreach($assists as $v) {
                $ids = array_merge($ids, $regions[$v['id']]['parent']);
                $ids = array_merge($ids, $regions[$v['id']]['child']);
            }
            $user_ids = [];
            foreach($regions as $region) {
                if (in_array($region['id'], $ids)) {
                    if ($region['owner_user_id']) {
                        $user_ids = array_merge($user_ids, [$region['owner_user_id']]);
                    }
                    if ($region['owner_assist']) {
                        $user_ids = array_merge($user_ids, explode(',', $region['owner_user_id']));
                    }
                }
            }
            return $user_ids;
        }

        return [];
    }

    /**
     * 检查动态密码
     */
    public static function wantsTotp()
    {
        if (env('AUTH_TOTP_STATUS', true) == false) {
            return 0;
        }

        $auth_totp = Auth::user()->auth_totp;

        if ($auth_totp == 0) {
            return 0;
        } elseif ($auth_totp == 1 && Session::get('auth_totp') == true) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * 取得用户列表
     */
    public static function getAll($userId = 0)
    {
        static $data = [];

        if (empty($data)) {
            $data = User::get(['id', 'department_id', 'role_id', 'username', 'name', 'email', 'phone', 'birthday', 'gender'])->keyBy('id');
        }

        return $userId > 0 ? $data[$userId] : $data;
    }

    /**
     *
     * 传入部门编号，角色编码，用户编码，进行并集处理返回用户编号
     */
    public static function getDRU($receive_id, $status = 1)
    {
        if ($receive_id == '') {
            return [];
        }
        $receives = explode(',', str_replace(['u', 'r', 'd'], ['u_', 'r_', 'd_'], $receive_id));

        $scope = [];
        foreach ($receives as $receive) {
            list($type, $id) = explode('_', $receive);
            $scope[$type][] = $id;
        }

        return DB::table('user')
        ->where('status', $status)
        ->where(function ($q) use ($scope) {
            if ($scope['d']) {
                $q->orwhereIn('department_id', $scope['d']);
            }
            if ($scope['r']) {
                $q->orwhereIn('role_id', $scope['r']);
            }
            if ($scope['u']) {
                $q->orwhereIn('id', $scope['u']);
            }
        })->get(['id', 'role_id', 'department_id', 'username', 'name', 'email', 'phone']);
    }

    // 登录失败，记录错误信息
    public static function authLogWrite($ip)
    {
        $log = SystemLog::where('ip', $ip)->first();

        $data['ip'] = $ip;
        if ($log->id) {
            $data['error_count'] = $log->error_count + 1;
            SystemLog::where('ip', $ip)->update($data);
        } else {
            $data['error_count'] = 1;
            SystemLog::insert($data);
        }
    }

    // 获取记录，没有记录返回空
    public static function authLogRead($ip)
    {
        return SystemLog::where('ip', $ip)->first();
    }

    // 登录成功，销毁错误信息
    public static function authLogDelete($ip)
    {
        SystemLog::where('ip', $ip)->delete();
    }

    // 检查IP
    public static function authLogCheckIp($ip, $auth_ip)
    {
        if (empty($auth_ip)) {
            return true;
        }
        $auth_ip = explode(PHP_EOL, $auth_ip);
        return ($auth_ip && in_array($ip, $auth_ip));
    }
}
