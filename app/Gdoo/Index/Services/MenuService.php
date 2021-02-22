<?php namespace Gdoo\Index\Services;

use Auth;
use DB;

use Gdoo\User\Services\UserAssetService;

class MenuService
{
    /**
     * 取得菜单列表
     */
    public static function getItems()
    {
        static $data = [];

        if ($data) {
            return $data;
        }

        $assets = UserAssetService::getRoleAuthorise(Auth::user()->role_id);
        $menus = DB::table('menu')->where('status', 1)->orderBy('lft', 'asc')->get();
        $menus = array_tree($menus);

        $positions = [];

        foreach ($menus as $menuId => &$menu) {
            if ($menu['children']) {
                // 二级菜单
                foreach ($menu['children'] as $groupId => &$group) {

                    $group['url'] = str_replace('.', '/', $group['url']);
                    if (substr_count($group['url'], '/') < 2) {
                        $group['url'] = '';
                    }

                    if ($group['url']) {
                        $group['url'] = str_replace('.', '/', $group['url']);
                        $group['key'] = str_replace('/', '_', $group['url']);
                        if ($group['access'] == 0 || isset($assets[$group['url']])) {
                            $menu['selected'] = 1;
                            $group['selected']  = 1;
                        }
                    }

                    if ($group['children']) {
                        // 三级菜单
                        foreach ($group['children'] as $actionId => &$action) {
                            $action['url'] = str_replace('.', '/', $action['url']);
                            $action['key'] = str_replace('/', '_', $action['url']);
                            $positions[$action['url']] = $menuId.','.$groupId.','.$actionId;

                            if ($action['access'] == 0 || isset($assets[$action['url']])) {
                                if (empty($group['url'])) {
                                    $group['url'] = $action['url'];
                                    $group['key'] = $action['key'];
                                }
                                $menu['selected'] = 1;
                                $group['selected']  = 1;
                                $action['selected'] = 1;
                            }
                        }
                    }
                }
            }
        }
        $data['children'] = $menus;
        return $data;
    }
}
