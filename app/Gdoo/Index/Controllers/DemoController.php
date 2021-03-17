<?php namespace Gdoo\Index\Controllers;

use DB;
use URL;
use Request;

class DemoController extends Controller
{
    #[Attribute(Attribute::TARGET_FUNCTION)]
    public function vouch()
    {
        return $this->display();
    }

    public function hello()
    {
    }
}