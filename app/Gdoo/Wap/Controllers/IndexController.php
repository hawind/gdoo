<?php namespace Gdoo\Wap\Controllers;

use Cache;
use Request;
use DB;

use Gdoo\System\Models\Setting;

use Gdoo\Index\Controllers\DefaultController;

class IndexController extends DefaultController
{
    public $permission = [];

    protected $layout = 'layouts.wechat';

    public function indexAction()
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
        return $this->display([
            'app' => $this->config,
        ]);
    }
}
