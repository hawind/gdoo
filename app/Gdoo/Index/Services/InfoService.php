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

    public static function getInfo($table, $field = 'created_at', $type = 'ts')
    {
        $auth = auth()->user();
        $gets = Request::all();
        $params = $gets['params'];

        $info = Widget::where('id', $gets['id'])
        ->first();

        $user_info = UserWidget::where('user_id', $auth['id'])
        ->where('node_id', $gets['id'])
        ->first();

        if ($user_info['name']) {
            $info['name'] = $user_info['name'];
        }
        if ($user_info['color']) {
            $info['color'] = $user_info['color'];
        }
        if ($user_info['icon']) {
            $info['icon'] = $user_info['icon'];
        }
        if ($user_info['params']) {
            $info['params'] = json_decode($user_info['params'], true);
        }

        $permission = empty($params['permission']) ? 'dept2' : $params['permission'];
        $date = empty($params['date']) ? 'month' : $params['date'];

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
                $year3 = $year - 2;
                break;
        }

        $sql = $sql2 = '';
        $key = $table.'.'.$field;
        switch ($date) {
            case 'day':
                $sql = sql_year_month_day($key, $type)." = '$day'";
                $sql2 = sql_year_month_day($key, $type)." = '$day2'";
                break;
            case 'day2':
                $sql = sql_year_month_day($key, $type)." = '$day2'";
                $sql2 = sql_year_month_day($key, $type)." = '$day3'";
                break;    
            case 'week':
                $sql = sql_year_month_day($key, $type)." between '$week[0]' and '$week[1]'";
                $sql2 = sql_year_month_day($key, $type)." between '$week2[0]' and '$week2[1]'";
                break;
            case 'week2':
                $sql = sql_year_month_day($key, $type)." between '$week2[0]' and '$week2[1]'";
                $sql2 = sql_year_month_day($key, $type)." between '$week3[0]' and '$week3[1]'";
                break;
            case 'month':
                $sql = sql_year_month($key, $type)." = '$month'";
                $sql2 = sql_year_month($key, $type)." = '$month2'";
                break;
            case 'month2':
                $sql = sql_year_month($key, $type)." = '$month2'";
                $sql2 = sql_year_month($key, $type)." = '$month3'";
                break;
            case 'season':
                $sql = sql_year_month_day($key, $type)." between '$season[0]' and '$season[1]'";
                $sql2 = sql_year_month_day($key, $type)." between '$season2[0]' and '$season2[1]'";
                break;
            case 'season2':
                $sql = sql_year_month_day($key, $type)." between '$season2[0]' and '$season2[1]'";
                $sql2 = sql_year_month_day($key, $type)." between '$season3[0]' and '$season3[1]'";
                break;
            case 'year':
                $sql = sql_year($key, $type)." = '$year'";
                $sql2 = sql_year($key, $type)." = '$year2'";
                break;
            case 'year2':
                $sql = sql_year($key, $type)." = '$year2'";
                $sql2 = sql_year($key, $type)." = '$year3'";
                break;
        }
        return ['info' => $info, 'sql' => $sql, 'sql2' => $sql2, 'gets' => $gets, 'params' => $params, 'auth' => $auth];
    }

    public static function getWidget($table, $field = 'created_at', $type = 'ts')
    {
        $auth = auth()->user();
        $gets = Request::all();
        $params = $gets['params'];

        $widget = Widget::where('id', $gets['id'])
        ->first();

        $user_widget = UserWidget::where('user_id', $auth['id'])
        ->where('node_id', $gets['id'])
        ->first();

        if ($user_widget['name']) {
            $widget['name'] = $user_widget['name'];
        }
        if ($user_widget['color']) {
            $widget['color'] = $user_widget['color'];
        }
        if ($user_widget['icon']) {
            $widget['icon'] = $user_widget['icon'];
        }
        if ($user_widget['params']) {
            $widget['params'] = json_decode($user_widget['params'], true);
        }

        $permission = empty($params['permission']) ? 'dept2' : $params['permission'];
        $date = empty($params['date']) ? 'month' : $params['date'];

        switch ($date) {
            case 'last_day7':
                $day = date('Y-m-d');
                $day2 = date('Y-m-d', strtotime('-7 day'));
            case 'last_day28':
                $day = date('Y-m-d');
                $day2 = date('Y-m-d', strtotime('-28 day'));
                break;    
            case 'last_month':
                $month2 = date('Y-m', strtotime("-1 month"));
                break;
            case 'year2':
                $year2 = date('Y', strtotime("-1 year"));
                break;
        }

        $sql = '';
        $key = $table.'.'.$field;
        switch ($date) {
            case 'day7':
            case 'day28':
                $sql = sql_year_month_day($key, $type)." between '$day' and '$day2'";
                break;
            case 'last_month':
                $sql = sql_year_month($key, $type)." = '$month2'";
                break;
            case 'year2':
                $sql = sql_year($key, $type)." = '$year2'";
                break;
        }
        return ['widget' => $widget, 'sql' => $sql, 'gets' => $gets, 'params' => $params, 'auth' => $auth];
    }
}
