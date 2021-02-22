<?php namespace Gdoo\Workflow\Services;

use DB;
use Auth;

class WorkflowService
{
    /**
     * 获取待办工作流程
     */
    public static function getBadge()
    {
        $rows = [];
        $ret['total'] = sizeof($rows);
        $ret['data'] = $rows;
        return $ret;
    }
}
