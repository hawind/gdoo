<?php namespace Gdoo\User\Controllers;

use DB;
use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\InfoService;

class WidgetController extends DefaultController
{
    public $permission = ['birthday', 'userCount'];

    // 生日提醒
    public function birthday()
    {
        $rows = DB::table('user')->whereRaw("concat(year(getdata()), DATE_FORMAT(birthday,'-%m-%d')) BETWEEN DATE_FORMAT(getdate(),'%Y-%m-%d') AND DATE_FORMAT(DATE_ADD(getdate(), interval 30 day),'%Y-%m-%d')")->get();
        return $this->render(array(
            'rows' => $rows,
        ));
    }

    /**
     * 用户数量
     */
    public function userCount()
    {
        $config = InfoService::getInfo('user');
        $model = DB::table('user')->whereRaw('('.$config['sql'].')');
        $model2 = DB::table('user')->whereRaw('('.$config['sql2'].')');
        $count = $model->count();
        $count2 = $model2->count();

        $rate = 0;
        if ($count2 > 0) {
            $rate = ($count - $count2) / $count2 * 100;
            $rate = number_format($rate, 2);
        }
        $res = [
            'count' => $count,
            'count2' => $count2,
            'rate' => $rate,
        ];
        return $this->json($res, true);
    }
}
