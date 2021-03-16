<?php namespace Gdoo\Customer\Controllers;

use Auth;
use Request;
use Validator;
use DB;

use Gdoo\Customer\Models\Business;
use Gdoo\User\Models\User;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\AttachmentService;

class BusinessController extends DefaultController
{
    public $permission = [];

    // 商机列表
    public function indexAction()
    {
    }
}
