<?php namespace Gdoo\Wechat\Controllers;

use Request;
use DB;

use Gdoo\System\Models\Setting;
use Gdoo\Wechat\Models\Config;

use Gdoo\Wechat\Services\WechatService;
use Gdoo\Wechat\Services\MenuService;

use Gdoo\Index\Controllers\DefaultController;

class ConfigController extends DefaultController
{
    public $permission = [];

    protected $layout = 'layouts.wechat';

    protected $menu = [];
    protected $config = [];

    public function __construct(MenuService $menu) {
        parent::__construct();
        $this->menu = $menu;
    }

    public function config()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            foreach ($gets as $key => $value) {
                Setting::where('type', 'wechat')->where('key', $key)->update([
                    'value' => $value,
                ]);
            }
            return $this->json('保存成功。', true);
        }
        $header['tabs'] = Config::$tabs;
        $app = Setting::where('type', 'wechat')->pluck('value', 'key');
        return $this->display([
            'app' => $app,
            'header' => $header,
        ]);
    }

    /**
     * 自定义菜单
     */
    public function menu()
    {
        if (Request::ajax()) {
            
            $app = WechatService::getApp();

            $gets = Request::all();
            $data = $gets['data'];

            // 空菜单认为删除全部菜单
            if (empty($data)) {
                Setting::where('type', 'wechat')->where('key', 'menu')->update(['value' => '']);
                $app->menu->delete();
                return $this->json('保存成功', true);
            }
            $menu_type = [
                'view' => '跳转URL',
                'click' => '点击推事件',
                'scancode_push' => '扫码推事件',
                'scancode_waitmsg' => '扫码推事件且弹出“消息接收中”提示框',
                'pic_sysphoto' => '弹出系统拍照发图',
                'pic_photo_or_album' => '弹出拍照或者相册发图',
                'pic_weixin' => '弹出微信相册发图器',
                'location_select' => '弹出地理位置选择器',
            ];

            Setting::where('type', 'wechat')->where('key', 'menu')->update([
                'value' => json_encode($data, JSON_UNESCAPED_UNICODE)
            ]);
            
            foreach ($data as &$row) {
                if (empty($row['content'])) {
                    $row['content'] = uniqid();
                }
                switch ($row['type']) {
                    case 'miniprogram':
                        list($row['appid'], $row['url'], $row['pagepath']) = explode(',', $row['content'].',,');
                        break;
                    case 'view':
                        $row['url'] = preg_match('#^(\w+:)?//#i', $row['content']) ? $row['content'] : url($row['content']);
                        break;
                    case 'event':
                        if (isset($menu_type[$row['content']])) {
                            $row['type'] = $row['content'];
                            $row['key'] = "wechat_menu#id#{$row['id']}";
                        }
                        break;
                    case 'media_id':
                        $row['media_id'] = $row['content'];
                        break;
                    default:
                        (!in_array($row['type'], $menu_type)) && $row['type'] = 'click';
                        $row['key'] = "{$row['content']}";
                }
                unset($row['content']);
            }

            $menus = $this->menu->GetTreeByMenu($data, 'index', 'pindex', 'sub_button');
            foreach ($menus as &$menu) {
                unset($menu['index'], $menu['pindex'], $menu['id']);
                if (empty($menu['sub_button'])) {
                    continue;
                }
                foreach ($menu['sub_button'] as &$submenu) {
                    unset($submenu['index'], $submenu['pindex'], $submenu['id']);
                }
                unset($menu['type']);
            }

            $ret = $app->menu->create($menus);
            if ($ret['errcode'] == 0) {
                return $this->json('发布成功', true);
            } else {
                return $this->json('errcode:'.$ret['errcode'].' errmsg:'.$ret['errmsg']);
            }
        }

        $app = Setting::where('type', 'wechat')->pluck('value', 'key');
        $menus = (array)json_decode($app['menu'], JSON_UNESCAPED_UNICODE);
        $menus = $this->menu->GetTreeByMenu($menus, 'index', 'pindex');
        $header['tabs'] = Config::$tabs;
        return $this->display([
            'header' => $header,
            'menus' => $menus,
        ]);
    }
}
