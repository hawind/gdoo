<?php namespace Gdoo\User\Controllers;

use DB;
use Gdoo\Index\Controllers\DefaultController;

class WidgetController extends DefaultController
{
    public $permission = ['birthday'];

    // 生日提醒
    public function birthday()
    {
        $rows = DB::table('user')->whereRaw("concat(year(getdata()), DATE_FORMAT(birthday,'-%m-%d')) BETWEEN DATE_FORMAT(getdate(),'%Y-%m-%d') AND DATE_FORMAT(DATE_ADD(getdate(), interval 30 day),'%Y-%m-%d')")->get();
        return $this->render(array(
            'rows' => $rows,
        ));
    }
}
