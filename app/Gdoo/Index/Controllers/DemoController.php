<?php namespace Gdoo\Index\Controllers;

use App\Jobs\SendSms;
use DB;
use URL;
use Request;

class DemoController extends Controller
{
    //#[Attribute(Attribute::TARGET_FUNCTION)]
    public function vouch()
    {
        return $this->display();
    }

    public function hello()
    {
        $abc = SendSms::dispatch([1], '我是测试', '我也是测试');
        print_r($abc);
    }
}