<?php namespace Gdoo\Index\Services;

use DB;
use Auth;
use Gdoo\System\Models\Widget;
use Gdoo\User\Models\UserWidget;
use Request;

class InfoService
{
    /**
     * 获取季度日期
     */
    public static function getSeason($interval = 0) {
        $season = ceil(date('n') / 3) + $interval;
        $a = date('Y-m-d', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
        $b = date('Y-m-d', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')));
        return [$a, $b];
    }

    public static function getInfo($table)
    {
        $auth = auth()->user();
        $gets = Request::all();

        $dates = [
            'day' => '昨天',
            'day2' => '前天',
            'week' => '上周',
            'week2' => '前周',
            'month' => '上月',
            'month2' => '前月',
            'quarter' => '上季度',
            'quarter2' => '前季度',
            'year' => '去年',
            'year2' => '前年',
        ];

        $info = Widget::where('id', $gets['id'])
        ->first();

        $user_info = UserWidget::where('user_id', $auth['id'])
        ->where('node_id', $gets['id'])->first();

        if (not_empty($user_info)) {
            $info['id'] = $user_info['id'];
            if ($user_info['name']) {
                $info['name'] = $user_info['name'];
            }
            if ($user_info['color']) {
                $info['color'] = $user_info['color'];
            }
            if ($user_info['icon']) {
                $info['icon'] = $user_info['icon'];
            }
            $info['params'] = json_decode($user_info['params'], true);
        }
        $params = $info['params'];

        $permission = empty($params['permission']) ? 'department' : $params['permission'];
        $date = empty($params['date']) ? 'month' : $params['date'];
        $params['permission'] = $permission;
        $params['date'] = $date;
        $info['params'] = $params;

        switch ($date) {
            case 'day':
            case 'day2':
                // 天
                $day = date('Y-m-d');
                $day2 = strtotime('-1 day '.$day);
                $day3 = strtotime('-2 day '.$day);
                break;    
            case 'week':
            case 'week2':
                // 周
                $week[] = date('Y-m-d', strtotime('this week'));
                $week[] = date('Y-m-d', strtotime('this week +6 day'));
                $week2[] = date('Y-m-d', strtotime('next week'));
                $week2[] = date('Y-m-d', strtotime('next week +6 day'));
                $week3[] = date('Y-m-d', strtotime('monday -2 week'));
                $week3[] = date('Y-m-d', strtotime('sunday -1 week'));
                break;
            case 'month':
            case 'month2':
                // 月
                $month = date('Y-m');
                $month2 = date('Y-m', strtotime("-1 month"));
                $month3 = date('Y-m', strtotime("-2 month"));
                break;
            case 'season':
            case 'season2':
                // 季度
                $season = static::getSeason();
                $season2 = static::getSeason(-1);
                $season3 = static::getSeason(-2);
                break;
            case 'year':
            case 'year2':
                // 年
                $year = date('Y');
                $year2 = $year - 1;
                $year3 =  $year - 2;
                break;
        }

        $sql = $sql2 = '';
        switch ($date) {
            case 'day':
                $sql = sql_year_month_day($table.'.created_at','ts')."='$day'";
                $sql2 = sql_year_month_day($table.'.created_at','ts')."='$day2'";
                break;
            case 'day2':
                $sql = sql_year_month_day($table.'.created_at','ts')."='$day2'";
                $sql2 = sql_year_month_day($table.'.created_at','ts')."='$day3'";
                break;    
            case 'week':
                $sql = sql_year_month_day($table.'.created_at','ts')." between '$week[0]' and '$week[1]'";
                $sql2 = sql_year_month_day($table.'.created_at','ts')." between '$week2[0]' and '$week2[1]'";
                break;
            case 'week2':
                $sql = sql_year_month_day($table.'.created_at','ts')." between '$week2[0]' and '$week2[1]'";
                $sql2 = sql_year_month_day($table.'.created_at','ts')." between '$week3[0]' and '$week3[1]'";
                break;
            case 'month':
                $sql = sql_year_month($table.'.created_at','ts')."='$month'";
                $sql2 = sql_year_month($table.'.created_at','ts')."='$month2'";
                break;
            case 'month2':
                $sql = sql_year_month($table.'.created_at','ts')."='$month2'";
                $sql2 = sql_year_month($table.'.created_at','ts')."='$month3'";
                break;
            case 'season':
                $sql = sql_year_month_day($table.'.created_at','ts')." between '$season[0]' and '$season[1]'";
                $sql2 = sql_year_month_day($table.'.created_at','ts')." between '$season2[0]' and '$season2[1]'";
                break;
            case 'season2':
                $sql = sql_year_month_day($table.'.created_at','ts')." between '$season2[0]' and '$season2[1]'";
                $sql2 = sql_year_month_day($table.'.created_at','ts')." between '$season3[0]' and '$season3[1]'";
                break;
            case 'year':
                $sql = sql_year($table.'.created_at','ts')."='$year'";
                $sql2 = sql_year($table.'.created_at','ts')."='$year2'";
                break;
            case 'year2':
                $sql = sql_year($table.'.created_at','ts')."='$year2'";
                $sql2 = sql_year($table.'.created_at','ts')."='$year3'";
                break;
        }
        return ['info' => $info, 'dates' => $dates, 'sql' => $sql, 'sql2' => $sql2, 'gets' => $gets, 'params' => $params, 'auth' => $auth];
    }
}
