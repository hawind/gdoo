<?php namespace Gdoo\Wechat\Services;

use Cache;

use Gdoo\System\Models\Setting;

use EasyWeChat\Factory;

class WechatService
{
    public static function getApp()
    {
        static $app = null;
        if ($app == null) {
            $config = static::getConfig();
            $app = Factory::officialAccount([
                'app_id' => $config['wechat_appid'],
                'secret' => $config['wechat_secret'],
                'token' => $config['wechat_token'],
                'aes_key' => $config['wechat_aeskey'],
                'response_type' => 'array',
                'log' => [
                    'level' => 'debug',
                    'file' => storage_path().'/logs/wechat.log',
                ],
            ]);
        }
        return $app;
    }

    public static function getConfig()
    {
        static $config = null;
        if ($config == null) {
            $config = Setting::where('type', 'wechat')->pluck('value', 'key');
        }
        return $config;
    }
}