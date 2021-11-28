<?php namespace Gdoo\Project\Controllers;

use DB;
use Auth;
use Request;
use Gdoo\Index\Controllers\DefaultController;
use Gdoo\Index\Services\InfoService;

class WidgetController extends DefaultController
{
    public $permission = ['info'];

    /**
     * 项目任务信息
     */
    public function info()
    {
        $config = InfoService::getInfo('project_task');

        $count = DB::table('project_task')
        ->where('user_id', Auth::id())
        ->whereRaw('isnull(progress, 0) < 1')
        ->whereRaw('('.$config['sql'].')')
        ->count();
        $count2 = DB::table('project_task')
        ->where('user_id', Auth::id())
        ->whereRaw('isnull(progress, 0) < 1')
        ->whereRaw('('.$config['sql2'].')')
        ->count();
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
