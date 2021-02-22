<?php namespace Gdoo\Approach\Services;

use DB;
use Gdoo\Index\Services\BadgeService;

class ApproachService
{
    /**
     * 获取待办的进店申请
     */
    public static function getBadge()
    {
        return BadgeService::getModelTodo('approach');
    }
}
