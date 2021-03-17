<?php namespace Gdoo\Calendar\Controllers;

use Auth;
use Request;

use Gdoo\Calendar\Services\CalendarService;

use Gdoo\User\Models\Department;
use Gdoo\User\Models\User;

use Gdoo\Index\Controllers\DefaultController;

class CalendarController extends DefaultController
{
    public $permission = ['calendars', 'help'];

    /**
     * 日历首页
     */
    public function index()
    {
        $user_id = Request::get('user_id', Auth::id());

        // 获取下属用户列表
        $users = User::where('status', 1)->where('leader_id', $user_id)->get(['id', 'department_id', 'name']);
        $departments = Department::orderBy('lft', 'asc')->get()->toNested()->toArray();
        $underling = array();
        foreach ($users as $row) {
            $underling['role'][$row['department_id']] = $departments[$row['department_id']];
            $underling['user'][$row['department_id']][$row['id']] = $row;
        }
        $user = User::find($user_id);

        return $this->display([
            'user' => $user,
            'underling' => $underling,
        ]);
    }

    /**
     * 日历列表
     */
    public function calendars()
    {
        $user_id = Request::get('user_id', Auth::id());
        $calendars = CalendarService::getCalendars($user_id);

        $calendars[] = [
            'id' => 'shared',
            'displayname' => '共享事件',
            'calendarcolor' => '#999',
        ];
        $sources = [];
        foreach ($calendars as $calendar) {
            if ($calendar['id'] == 'shared') {
                $url = url('event/share', ['user_id'=>$user_id]);
            } else {
                $url = url('event/index', ['calendar_id'=>$calendar['id']]);
            }
            $sources[] = [
                'url'             => $url,
                'id'              => $calendar['id'],
                'userid'          => $calendar['userid'],
                'backgroundColor' => $calendar['calendarcolor'],
                "borderColor"     => $calendar['calendarcolor'],
            ];
        }
        return $this->json([
            'calendars' => $calendars,
            'sources'   => $sources,
        ], true);
    }

    public function active()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $calendar = CalendarService::getCalendar($gets['id'], true);
            if ($calendar) {
                try {
                    CalendarService::setCalendarActive($gets['id'], $gets['active']);
                } catch (\Exception $e) {
                    return $this->json($e->getMessage());
                }
            }
            $calendar = CalendarService::getCalendar($gets['id'], false);
            return $this->json([
                'active' => $gets['active'],
                'eventSource' => array(
                    'id' => $calendar['id'],
                    'url' => url('event/index', ['calendar_id' => $calendar['id']]),
                    'backgroundColor' => $calendar['calendarcolor'],
                    "borderColor" => $calendar['calendarcolor'],
                )
            ], true);
        }
    }

    public function add()
    {
        $gets = Request::all();
        if (Request::method() == 'POST') {
            if ($gets['id'] > 0) {
                $id = CalendarService::editCalendar($gets['id'], $gets['displayname'], null, null, null, $gets['calendarcolor']);
            } else {
                $id = CalendarService::addCalendar(Auth::id(), $gets['displayname'], 'VEVENT,VTODO,VJOURNAL', null, 0, $gets['calendarcolor']);
            }
            return $this->json(['id' => $id], true);
        }
        $calendar = CalendarService::getCalendar((int)$gets['id']);
        return $this->render(array(
            'calendar' => $calendar,
        ));
    }

    // 帮助信息
    public function help()
    {
        return $this->render();
    }

    public function delete()
    {
        $id = Request::get('id');
        if ($id > 0) {
            CalendarService::deleteCalendar($id);
            return $this->json(['id'=>$id], true);
        }
    }
}
