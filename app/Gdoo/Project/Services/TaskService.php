<?php namespace Gdoo\Project\Services;

use DB;
use Auth;

class TaskService
{
    /**
     * 获取待办任务
     */
    public static function getBadge()
    {
        $rows = DB::table('project_task')
        ->where('user_id', Auth::id())
        ->whereRaw('isnull(progress, 0) < 1')
        ->get();
        $ret['total'] = sizeof($rows);
        $ret['data'] = $rows;
        return $ret;
    }
}