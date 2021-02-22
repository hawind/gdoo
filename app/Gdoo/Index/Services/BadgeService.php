<?php namespace Gdoo\Index\Services;

use DB;

class BadgeService
{
    /**
     * 获取待办事项
     */
    public static function getModelTodo($table)
    {
        $master = DB::table('model')->where('table', $table)->first();
        $rows = DB::table('model_run_log')
        ->leftJoin('model_run', 'model_run.id', '=', 'model_run_log.run_id')
        ->leftJoin($table, $table.'.id', '=', 'model_run.data_id')
        ->leftJoin('user as run_log_user', 'run_log_user.id', '=', 'model_run_log.user_id')
        ->where('model_run_log.updated_id', 0)
        ->where('model_run_log.user_id', auth()->id())
        ->where('model_run_log.bill_id', $master['id'])
        ->where($table.'.id', '>', 0)
        ->get(['model_run_log.*']);

        $ret['total'] = sizeof($rows);
        $ret['data'] = $rows;
        return $ret;
    }
}
