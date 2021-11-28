<?php namespace Gdoo\Calendar\Controllers;

use DB;
use Request;

use Gdoo\Index\Controllers\DefaultController;

class WidgetController extends DefaultController
{
    public $permission = ['index'];

    // 日程
    public function index()
    {
        return $this->render();
    }
}
