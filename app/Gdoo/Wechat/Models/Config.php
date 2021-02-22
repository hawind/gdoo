<?php namespace Gdoo\Wechat\Models;

class Config
{
    static public $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'config.config', 'url' => 'wechat/config/config', 'name' => '微信配置'],
            ['value' => 'config.menu', 'url' => 'wechat/config/menu', 'name' => '微信菜单'],
        ]
    ];
}
