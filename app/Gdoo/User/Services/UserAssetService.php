<?php namespace Gdoo\User\Services;

use Auth;
use Request;

use Gdoo\User\Models\UserAsset;

class UserAssetService
{
    protected static $permissions = [];

    public static function setPermissions($permissions)
    {
        self::$permissions = $permissions;
    }
    
    /**
     * 检查权限
     */
    public static function check($role_id, $action, $asset)
    {
        static $assets = null;

        if ($assets == null) {
            $assets = static::getRoleAssets($role_id);
        }

        // 授权访问
        if (isset($assets[$asset][$action])) {
            return $assets[$asset][$action];
        }

        // 跳过ACL检查的方法
        if (in_array(Request::action(), static::$permissions)) {
            return 1;
        }

        return 0;
    }

    public static function getAssets()
    {
        static $assets = null;

        if ($assets == null) {
            $assets = UserAsset::get(['id', 'name', 'rules'])->toArray();
        }
        return $assets;
    }

    /**
     * 获取角色资源
     */
    public static function getRoleAssets($roleId)
    {
        static $assets = null;

        if ($assets == null) {
            $assets = static::getAssets();
        }

        foreach ($assets as $key => $asset) {
            $rules = (array)json_decode($asset['rules'], true);

            foreach ($rules as $key => $rule) {
                if (isset($rule[$roleId])) {
                    $res[$asset['name']][$key] = ($rule[$roleId] > 0 ? $rule[$roleId] : 1);
                }
            }
        }
        return $res;
    }

    /**
     * 获取角色资源
     */
    public static function getNowRoleAssets()
    {
        $res = [];
        $assets = static::getRoleAssets(Auth::user()->role_id);

        $assets = $assets[Request::module()];
        if ($assets) {
            foreach ($assets as $key => $asset) {
                list($controller, $action) = explode('.', $key);
                if ($controller == Request::controller()) {
                    $res[$action] = $asset;
                }
            }
        }
        return $res;
    }
    
    /**
     * 获取角色资源
     */
    public static function getRoleAuthorise($roleId)
    {
        static $assets = null;

        if ($assets == null) {
            $assets = static::getAssets();
        }

        foreach ($assets as $key => $asset) {
            $rules = (array)json_decode($asset['rules'], true);
            
            foreach ($rules as $key => $rule) {
                if (isset($rule[$roleId])) {
                    $key = str_replace('.', '/', $asset['name'].'/'.$key);
                    $res[$key] = ($rule[$roleId] > 0 ? $rule[$roleId] : 1);
                }
            }
        }
        return $res;
    }
}
