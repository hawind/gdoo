<?php namespace Gdoo\Index\Controllers;

use Gdoo\Index\Services\InfoService;
use Request;
use Gdoo\Model\Services\ModuleService;

class IndexController extends DefaultController
{
    /**
      * 设置可直接访问的方法
      */
    public $permission = [
        'info', 
        'badge',
        'badges',
        'index',
        'unsupportedBrowser',
        'support',
    ];

    public function index()
    {
        $user = auth()->user();
        return $this->render([
            'user' => $user,
        ]);
    }

    // 技术支持
    public function support()
    {
        return $this->render();
    }

    /*
     * 通用对话框
     */
    public function dialog()
    {
        $gets = Request::all();
        return $this->render([
            'gets' => $gets
        ]);
    }

    // 获取单个待办数量
    public function badge()
    {
        $key = Request::input('key');
        if ($key) {
            $badge = ModuleService::badges($key);
            if ($badge) {
                return $badge();
            }
        }
        return ['total' => 0, 'data' => []];
    }

    // 获取全部待办数量
    public function badges()
    {
        $badges = ModuleService::badges();
        $json = [];
        foreach($badges as $key => $badge) {
            $json[$key] = $badge();
        }
        return $json;
    }
}