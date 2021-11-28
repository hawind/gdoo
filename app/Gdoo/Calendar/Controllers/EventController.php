<?php namespace Gdoo\Calendar\Controllers;

use Auth;
use DB;
use Request;

use App\Support\VObject;

use Gdoo\Calendar\Services\CalendarService;
use Gdoo\Calendar\Services\CalendarObjectService;

use Gdoo\Index\Services\ShareService;
use Gdoo\Index\Services\AttachmentService;

use Gdoo\Index\Controllers\DefaultController;
use Illuminate\Support\Arr;

class EventController extends DefaultController
{
    public $permission = ['data'];

    // 事件列表
    public function index()
    {
        $gets = Request::all();
        $calendars = CalendarService::getCalendars($gets['user_id']);
        $ids = [];
        foreach ($calendars as $calendar) {
            $ids[] = $calendar['id'];
        }

        $cals = $calendars->keyBy('id');

        // 普通事件
        $rows = CalendarService::getRangeEvents($ids, $gets['start'], $gets['end']);
        foreach ($rows as $row) {
            $calendar = $cals[$row['calendarid']];
            if ($calendar['active'] == 1) {
                $row['backgroundColor'] = $calendar['calendarcolor'];
                $row['borderColor'] = $calendar['calendarcolor'];
                $row['userid'] = $calendar['userid'];
                $events[] = $row;
            }
        }

        // 获取共享事件
        $shared = ShareService::getItemsSourceBy(['event'], $gets['user_id'], $gets['start'], $gets['end']);
        $share_id = Arr::pluck($shared, 'source_id');
        if (count($share_id)) {
            $share = Arr::pluck($shared, 'name', 'source_id');
            $rows = CalendarService::getRangeEvents($share_id, $gets['start'], $gets['end'], true);
            foreach ($rows as $row) {
                $row['title'] = '['.$share[$row['id']].']'.$row['title'];
                $row['backgroundColor'] = '#666666';
                $row['borderColor'] = '#666666';
                $row['shared'] = true;
                $events[] = $row;
            }
        }
        return $events;
    }

    // 客户端获取数据
    public function data()
    {
        $gets = Request::all();
        $start = strtotime($gets['start']);
        $end = strtotime($gets['end']) + 86400;

        $items = [];

        // 读取共享事件
        $shared = ShareService::getItemsSourceBy(['event'], auth()->id());
        $share_id = Arr::pluck($shared, 'source_id');
        $share = Arr::pluck($shared, 'name', 'source_id');

        $events = CalendarService::getRangeEvents($share_id, $gets['start'], $gets['end'], true);

        foreach ($events as $key => $row) {
            $master = [
                'id' => $row['id'],
                'title' => '['.$share[$row['id']].']'.$row['title'],
                'start' => $row['start'],
                'end' => $row['end'],
                'allday' => $row['allDay'],
                'calendar' => [
                    'color' => '#666666',
                    'name' => '共享事件',
                ],
            ];

            $repeat = CalendarObjectService::getEventRepeat($master, '1D', 'Y-m-d');
            $items = array_merge($items, $repeat);
        }
        
        // 读取正常事件
        $rows = DB::table('calendar_object')
        ->leftJoin('calendar', 'calendar_object.calendarid', '=', 'calendar.id')
        ->where('calendar.userid', auth()->id())
        ->whereRaw('(
            (calendar_object.firstoccurence between '.$start.' and '.$end.' or calendar_object.lastoccurence between '.$start.' and '.$end.')
             or (calendar_object.rrule = 1 and calendar_object.firstoccurence <= '.$end.')
        )')
        ->get(['calendar_object.*','calendar.calendarcolor','calendar.displayname']);

        foreach ($rows as $key => $row) {
            $vcalendar = \Sabre\VObject\Reader::read($row['calendardata']);
            $allday = ($vcalendar->VEVENT->DTSTART->getDateType() == \Sabre\VObject\Property\DateTime::DATE) ? true : false;
            $master = [
                'id' => $row['id'],
                'title' => $vcalendar->VEVENT->SUMMARY->value,
                'start' => $vcalendar->VEVENT->DTSTART->value,
                'end' => $vcalendar->VEVENT->DTEND->value,
                'allday' => $allday,
                'calendar' => [
                    'color' => $row['calendarcolor'],
                    'name' => $row['displayname'],
                ],
            ];
            $repeat = CalendarObjectService::getEventRepeat($master, '1D', 'Y-m-d');
            $items = array_merge($items, $repeat);
        }
        return $items;
    }

    // 调整事件
    public function resize()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $event = CalendarService::getEvent($gets['id']);

            if ($event['lastmodified'] != $gets['lastmodified']) {
                return $this->json('事件已被修改。');
            }

            $vcalendar = VObject::parse($event['calendardata']);
            $vevent = $vcalendar->VEVENT;

            $delta = new \DateInterval('P0D');
            $delta->s = $gets['delta'];

            $dtend = CalendarService::getDTEndFromVEvent($vevent);
            $end_type = $dtend->getDateType();
            $dtend->setDateTime($dtend->getDateTime()->add($delta), $end_type);
            unset($vevent->DURATION);

            $vevent->setDateTime('LAST-MODIFIED', 'now', \Sabre\VObject\Property\DateTime::UTC);
            $vevent->setDateTime('DTSTAMP', 'now', \Sabre\VObject\Property\DateTime::UTC);

            CalendarService::edit($gets, $vcalendar);
            
            $lastmodified = $vevent->__get('LAST-MODIFIED')->getDateTime();
            return $this->json(['lastmodified' => $lastmodified->format('U')], true);
        }
    }

    // 移动事件
    public function move()
    {
        if (Request::method() == 'POST') {
            $gets = Request::all();
            $event = CalendarService::getEvent($gets['id']);

            if ($event['lastmodified'] != $gets['lastmodified']) {
                return $this->json('事件已被修改。');
            }

            $vcalendar = VObject::parse($event['calendardata']);
            $vevent = $vcalendar->VEVENT;

            $allday = $gets['allday'] == 'true' ? 1 : 0;
            $delta = new \DateInterval('P0D');
            $delta->s = $gets['delta'];

            $dtstart = $vevent->DTSTART;
            $dtend = CalendarService::getDTEndFromVEvent($vevent);
            $start_type = $dtstart->getDateType();
            $end_type = $dtend->getDateType();

            if ($allday && $start_type != \Sabre\VObject\Property\DateTime::DATE) {
                $start_type = $end_type = \Sabre\VObject\Property\DateTime::DATE;
                $dtend->setDateTime($dtend->getDateTime()->modify('+1 day'), $end_type);
            }

            if (!$allday && $start_type == \Sabre\VObject\Property\DateTime::DATE) {
                $start_type = $end_type = \Sabre\VObject\Property\DateTime::LOCALTZ;
            }

            $dtstart->setDateTime($dtstart->getDateTime()->add($delta), $start_type);
            $dtend->setDateTime($dtend->getDateTime()->add($delta), $end_type);
            unset($vevent->DURATION);

            $vevent->setDateTime('LAST-MODIFIED', 'now', \Sabre\VObject\Property\DateTime::UTC);
            $vevent->setDateTime('DTSTAMP', 'now', \Sabre\VObject\Property\DateTime::UTC);

            try {
                CalendarService::edit($gets, $vcalendar);
            } catch (\Exception $e) {
                return $this->json($e->getMessage());
            }
            $lastmodified = $vevent->__get('LAST-MODIFIED')->getDateTime();
            return $this->json(['lastmodified' => $lastmodified->format('U')], true);
        }
    }

    public function add()
    {
        $gets = Request::all();
        // 更新数据
        if (Request::method() == 'POST') {
            $error = CalendarService::validateRequest($gets);
            if ($error) {
                return $this->json($error);
            }
            $vcalendar = CalendarService::createVCalendarFromRequest($gets);
            try {
                $attachment = join(',', array_filter((array)$gets['attachment']));
                $id = CalendarService::add($gets, $vcalendar);
                AttachmentService::publish($gets['attachment']);

                return $this->json(['id' => $id], true);
            } catch (\Exception $e) {
                return $this->json($e->getMessage());
            }
        }

        // 新增表单
        if (Request::method() == 'GET') {
            $start = $gets['start'];
            $end = $gets['end'];
            $allday = $gets['allDay'];

            $start = new \DateTime('@'.strtotime($start));
            $end = new \DateTime('@'.strtotime($end));

            $timezone = CalendarService::getTimezone();
            $start->setTimezone(new \DateTimeZone($timezone));
            $end->setTimezone(new \DateTimeZone($timezone));

            if ($allday == 'true') {
                $end->modify('-1 day');
            }

            $calendar_options = CalendarService::getCalendars(Auth::id(), false);

            $options['calendar_options'] = $calendar_options;
            $options['access_class_options'] = CalendarService::getAccessClassOptions();
            $options['valarm_options'] = CalendarService::getValarmOptions();
            $options['repeat_options'] = CalendarService::getRepeatOptions();
            $options['repeat_end_options'] = CalendarService::getEndOptions();
            $options['repeat_month_options'] = CalendarService::getMonthOptions();
            $options['repeat_year_options'] = CalendarService::getYearOptions();
            $options['repeat_weekly_options'] = CalendarService::getWeeklyOptions();
            $options['repeat_weekofmonth_options'] = CalendarService::getWeekofMonth();
            $options['repeat_byyearday_options'] = CalendarService::getByYearDayOptions();
            $options['repeat_bymonth_options'] = CalendarService::getByMonthOptions();
            $options['repeat_byweekno_options'] = CalendarService::getByWeekNoOptions();
            $options['repeat_bymonthday_options'] = CalendarService::getByMonthDayOptions();

            $options['access'] = 'owner';
            $options['accessclass'] = 'PUBLIC';
            $options['startdate'] = $start->format('Y-m-d');
            $options['starttime'] = $start->format('H:i');
            $options['enddate'] = $end->format('Y-m-d');
            $options['endtime'] = $end->format('H:i');
            $options['allday'] = $allday;
            $options['valarm'] = '';

            $repeats['repeat'] = 'doesnotrepeat';
            $repeats['repeat_month'] = 'monthday';
            $repeats['repeat_weekdays'] = array();
            $repeats['repeat_interval'] = 1;
            $repeats['repeat_end'] = 'never';
            $repeats['repeat_count'] = 10;
            $repeats['repeat_weekofmonth'] = 'auto';
            $repeats['repeat_date'] = '';
            $repeats['repeat_year'] = 'bydate';

            $attachment = AttachmentService::edit('', 'calendar_object', 'attachment', 'calendar');
            return $this->render(array(
                'attachment' => $attachment,
                'options' => $options,
                'repeats' => $repeats,
            ));
        }
    }

    // 编辑事件
    public function edit()
    {
        $gets = Request::all();

        // 更新数据
        if (Request::method() == 'POST') {
            $error = CalendarService::validateRequest($gets);
            if ($error) {
                return $this->json($error);
            }

            $event = CalendarService::getEvent($gets['id']);

            if ($event['lastmodified'] != $gets['lastmodified']) {
                return $this->json('事件已被修改。', true);
            }

            $vcalendar = VObject::parse($event['calendardata']);
            CalendarService::updateVCalendarFromRequest($gets, $vcalendar);
            try {
                $attachment = join(',', array_filter((array)$gets['attachment']));
                CalendarService::edit($gets, $vcalendar);
                AttachmentService::publish($gets['attachment']);

            } catch (\Exception $e) {
                return $this->json($e->getMessage());
            }

            if ($event['calendarid'] != $gets['calendarid']) {
                try {
                    CalendarService::moveToCalendar($gets['id'], $gets['calendarid']);
                } catch (\Exception $e) {
                    return $this->json($e->getMessage());
                }
            }
            return $this->json('事件编辑完成。', true);
        }

        // 新增表单
        $event = CalendarService::getEvent($gets['id']);

        if (empty($event)) {
            return $this->json('事件数据不正确。');
        }

        $object = VObject::parse($event['calendardata']);
        $vevent = $object->VEVENT;
        $dtstart = $vevent->DTSTART;
        $dtend = CalendarService::getDTEndFromVEvent($vevent);

        switch ($dtstart->getDateType()) {
            case \Sabre\VObject\Property\DateTime::UTC:
                $timezone = new \DateTimeZone(CalendarService::getTimezone());
                $newDT = $dtstart->getDateTime();
                $newDT->setTimezone($timezone);
                $dtstart->setDateTime($newDT);
                $newDT = $dtend->getDateTime();
                $newDT->setTimezone($timezone);
                $dtend->setDateTime($newDT);
                // no break
            case \Sabre\VObject\Property\DateTime::LOCALTZ:
            case \Sabre\VObject\Property\DateTime::LOCAL:
                $startdate = $dtstart->getDateTime()->format('Y-m-d');
                $starttime = $dtstart->getDateTime()->format('H:i');
                $enddate = $dtend->getDateTime()->format('Y-m-d');
                $endtime = $dtend->getDateTime()->format('H:i');
                $allday = false;
                break;
            case \Sabre\VObject\Property\DateTime::DATE:
                $startdate = $dtstart->getDateTime()->format('Y-m-d');
                $starttime = '';
                $dtend->getDateTime()->modify('-1 day');
                $enddate = $dtend->getDateTime()->format('Y-m-d');
                $endtime = '';
                $allday = true;
                break;
        }

        $summary = strtr($vevent->getAsString('SUMMARY'), array('\,' => ',', '\;' => ';'));
        $location = strtr($vevent->getAsString('LOCATION'), array('\,' => ',', '\;' => ';'));
        $description = strtr($vevent->getAsString('DESCRIPTION'), array('\,' => ',', '\;' => ';'));
        $categories = $vevent->getAsString('CATEGORIES');

        if ($vevent->VALARM) {
            $valarm = $vevent->VALARM->getAsString('TRIGGER');
        }

        if ($vevent->RRULE) {
            $rrule = explode(';', $vevent->getAsString('RRULE'));
            $rrulearr = array();
            foreach ($rrule as $rule) {
                list($attr, $val) = explode('=', $rule);
                $rrulearr[$attr] = $val;
            }
            if (!isset($rrulearr['INTERVAL']) || $rrulearr['INTERVAL'] == '') {
                $rrulearr['INTERVAL'] = 1;
            }
            if (array_key_exists('BYDAY', $rrulearr)) {
                if (substr_count($rrulearr['BYDAY'], ',') == 0) {
                    if (strlen($rrulearr['BYDAY']) == 2) {
                        $repeat['weekdays'] = array($rrulearr['BYDAY']);
                    } elseif (strlen($rrulearr['BYDAY']) == 3) {
                        $repeat['weekofmonth'] = substr($rrulearr['BYDAY'], 0, 1);
                        $repeat['weekdays'] = array(substr($rrulearr['BYDAY'], 1, 2));
                    } elseif (strlen($rrulearr['BYDAY']) == 4) {
                        $repeat['weekofmonth'] = substr($rrulearr['BYDAY'], 0, 2);
                        $repeat['weekdays'] = array(substr($rrulearr['BYDAY'], 2, 2));
                    }
                } else {
                    $byday_days = explode(',', $rrulearr['BYDAY']);
                    foreach ($byday_days as $byday_day) {
                        if (strlen($byday_day) == 2) {
                            $repeat['weekdays'][] = $byday_day;
                        } elseif (strlen($byday_day) == 3) {
                            $repeat['weekofmonth'] = substr($byday_day, 0, 1);
                            $repeat['weekdays'][] = substr($byday_day, 1, 2);
                        } elseif (strlen($byday_day) == 4) {
                            $repeat['weekofmonth'] = substr($byday_day, 0, 2);
                            $repeat['weekdays'][] = substr($byday_day, 2, 2);
                        }
                    }
                }
            }
            if (array_key_exists('BYMONTHDAY', $rrulearr)) {
                if (substr_count($rrulearr['BYMONTHDAY'], ',') == 0) {
                    $repeat['bymonthday'][] = $rrulearr['BYMONTHDAY'];
                } else {
                    $bymonthdays = explode(',', $rrulearr['BYMONTHDAY']);
                    foreach ($bymonthdays as $bymonthday) {
                        $repeat['bymonthday'][] = $bymonthday;
                    }
                }
            }
            if (array_key_exists('BYYEARDAY', $rrulearr)) {
                if (substr_count($rrulearr['BYYEARDAY'], ',') == 0) {
                    $repeat['byyearday'][] = $rrulearr['BYYEARDAY'];
                } else {
                    $byyeardays = explode(',', $rrulearr['BYYEARDAY']);
                    foreach ($byyeardays as $yearday) {
                        $repeat['byyearday'][] = $yearday;
                    }
                }
            }
            if (array_key_exists('BYWEEKNO', $rrulearr)) {
                if (substr_count($rrulearr['BYWEEKNO'], ',') == 0) {
                    $repeat['byweekno'][] = (string) $rrulearr['BYWEEKNO'];
                } else {
                    $byweekno = explode(',', $rrulearr['BYWEEKNO']);
                    foreach ($byweekno as $weekno) {
                        $repeat['byweekno'][] = (string) $weekno;
                    }
                }
            }
            if (array_key_exists('BYMONTH', $rrulearr)) {
                if (substr_count($rrulearr['BYMONTH'], ',') == 0) {
                    $repeat['bymonth'][] = $month;
                } else {
                    $bymonth = explode(',', $rrulearr['BYMONTH']);
                    foreach ($bymonth as $month) {
                        $repeat['bymonth'][] = $month;
                    }
                }
            }
            switch ($rrulearr['FREQ']) {
                case 'DAILY':
                    $repeat['repeat'] = 'daily';
                    break;
                case 'WEEKLY':
                    if ($rrulearr['INTERVAL'] % 2 == 0) {
                        $repeat['repeat'] = 'biweekly';
                        $rrulearr['INTERVAL'] = $rrulearr['INTERVAL'] / 2;
                    } elseif ($rrulearr['BYDAY'] == 'MO,TU,WE,TH,FR') {
                        $repeat['repeat'] = 'weekday';
                    } else {
                        $repeat['repeat'] = 'weekly';
                    }
                    break;
                case 'MONTHLY':
                    $repeat['repeat'] = 'monthly';
                    if (array_key_exists('BYDAY', $rrulearr)) {
                        $repeat['month'] = 'weekday';
                    } else {
                        $repeat['month'] = 'monthday';
                    }
                    break;
                case 'YEARLY':
                    $repeat['repeat'] = 'yearly';
                    if (array_key_exists('BYMONTH', $rrulearr)) {
                        $repeat['year'] = 'bydaymonth';
                    } elseif (array_key_exists('BYWEEKNO', $rrulearr)) {
                        $repeat['year'] = 'byweekno';
                    } else {
                        $repeat['year'] = 'byyearday';
                    }
            }
            $repeat['interval'] = $rrulearr['INTERVAL'];
            if (array_key_exists('COUNT', $rrulearr)) {
                $repeat['end'] = 'count';
                $repeat['count'] = $rrulearr['COUNT'];
            } elseif (array_key_exists('UNTIL', $rrulearr)) {
                $repeat['end'] = 'date';
                $endbydate_year = substr($rrulearr['UNTIL'], 0, 4);
                $endbydate_month = substr($rrulearr['UNTIL'], 4, 2);
                $endbydate_day = substr($rrulearr['UNTIL'], 6, 2);
                $repeat['date'] = $endbydate_year . '-' .  $endbydate_month . '-' . $endbydate_day;
            } else {
                $repeat['end'] = 'never';
            }
            if (array_key_exists('weekdays', $repeat)) {
                $repeat_weekdays_ = array();
                foreach ($repeat['weekdays'] as $weekday) {
                    $repeat_weekdays_[] = $weekday;
                }
                $repeat['weekdays'] = $repeat_weekdays_;
            }
        } else {
            $repeat['repeat'] = 'doesnotrepeat';
        }

        $options['calendar_options']= CalendarService::getCalendars(Auth::id(), false);
        $options['access_class_options'] = CalendarService::getAccessClassOptions();
        $options['valarm_options'] = CalendarService::getValarmOptions();
        $options['repeat_options'] = CalendarService::getRepeatOptions();
        $options['repeat_end_options'] = CalendarService::getEndOptions();
        $options['repeat_month_options'] = CalendarService::getMonthOptions();
        $options['repeat_year_options'] = CalendarService::getYearOptions();
        $options['repeat_weekly_options'] = CalendarService::getWeeklyOptions();
        $options['repeat_weekofmonth_options'] = CalendarService::getWeekofMonth();
        $options['repeat_byyearday_options'] = CalendarService::getByYearDayOptions();
        $options['repeat_bymonth_options'] = CalendarService::getByMonthOptions();
        $options['repeat_byweekno_options'] = CalendarService::getByWeekNoOptions();
        $options['repeat_bymonthday_options'] = CalendarService::getByMonthDayOptions();

        $options['id'] = $gets['id'];
        $options['lastmodified'] = $event['lastmodified'];
        $options['title'] = $summary;
        $options['location'] = $location;
        $options['categories'] = $categories;
        $options['calendarid'] = $event['calendarid'];
        $options['allday'] = $allday;
        $options['valarm'] = $valarm;
        $options['startdate'] = $startdate;
        $options['starttime'] = $starttime;
        $options['enddate'] = $enddate;
        $options['endtime'] = $endtime;
        $options['description'] = $description;

        $repeats['repeat'] = $repeat['repeat'];

        if ($repeat['repeat'] != 'doesnotrepeat') {
            $repeats['repeat_month'] = isset($repeat['month']) ? $repeat['month'] : 'monthday';
            $repeats['repeat_weekdays'] = isset($repeat['weekdays']) ? $repeat['weekdays'] : array();
            $repeats['repeat_interval'] = isset($repeat['interval']) ? $repeat['interval'] : '1';
            $repeats['repeat_end'] = isset($repeat['end']) ? $repeat['end'] : 'never';
            $repeats['repeat_count'] = isset($repeat['count']) ? $repeat['count'] : '10';
            $repeats['repeat_weekofmonth'] = $repeat['weekofmonth'];
            $repeats['repeat_date'] = isset($repeat['date']) ? $repeat['date'] : '';
            $repeats['repeat_year'] = isset($repeat['year']) ? $repeat['year'] : array();
            $repeats['repeat_byyearday'] = isset($repeat['byyearday']) ? $repeat['byyearday'] : array();
            $repeats['repeat_bymonthday'] = isset($repeat['bymonthday']) ? $repeat['bymonthday'] : array();
            $repeats['repeat_bymonth'] = isset($repeat['bymonth']) ? $repeat['bymonth'] : array();
            $repeats['repeat_byweekno'] = isset($repeat['byweekno']) ? $repeat['byweekno'] : array();
        } else {
            $repeats['repeat_month'] = 'monthday';
            $repeats['repeat_weekdays'] = array();
            $repeats['repeat_byyearday'] = array();
            $repeats['repeat_bymonthday'] = array();
            $repeats['repeat_bymonth'] = array();
            $repeats['repeat_byweekno'] = array();
            $repeats['repeat_interval'] = '1';
            $repeats['repeat_end'] = 'never';
            $repeats['repeat_count'] = '10';
            $repeats['repeat_weekofmonth'] = 'auto';
            $repeats['repeat_date'] = '';
            $repeats['repeat_year'] = 'bydate';
        }

        $attachment = AttachmentService::edit($event['attachment'], 'calendar_object', 'attachment', 'calendar');
        $share = ShareService::getItem('event', $gets['id']);
        return $this->render(array(
            'attachment' => $attachment,
            'repeats' => $repeats,
            'options' => $options,
            'share' => $share,
        ), 'add');
    }

    public function view()
    {
        $id = (int)Request::get('id');
        $event = CalendarService::getEvent($id);

        if (empty($event)) {
            return $this->json('事件数据不正确。');
        }

        $object = VObject::parse($event['calendardata']);
        $vevent = $object->VEVENT;

        $dtstart = $vevent->DTSTART;
        $dtend = CalendarService::getDTEndFromVEvent($vevent);
        switch ($dtstart->getDateType()) {
            case \Sabre\VObject\Property\DateTime::UTC:
                $timezone = new \DateTimeZone(CalendarService::getTimezone());
                $newDT = $dtstart->getDateTime();
                $newDT->setTimezone($timezone);
                $dtstart->setDateTime($newDT);
                $newDT = $dtend->getDateTime();
                $newDT->setTimezone($timezone);
                $dtend->setDateTime($newDT);
                // no break
            case \Sabre\VObject\Property\DateTime::LOCALTZ:
            case \Sabre\VObject\Property\DateTime::LOCAL:
                $startdate = $dtstart->getDateTime()->format('Y-m-d');
                $starttime = $dtstart->getDateTime()->format('H:i');
                $enddate = $dtend->getDateTime()->format('Y-m-d');
                $endtime = $dtend->getDateTime()->format('H:i');
                $allday = false;
                break;
            case \Sabre\VObject\Property\DateTime::DATE:
                $startdate = $dtstart->getDateTime()->format('Y-m-d');
                $starttime = '';
                $dtend->getDateTime()->modify('-1 day');
                $enddate = $dtend->getDateTime()->format('Y-m-d');
                $endtime = '';
                $allday = true;
                break;
        }

        $summary = strtr($vevent->getAsString('SUMMARY'), array('\,' => ',', '\;' => ';'));
        $location = strtr($vevent->getAsString('LOCATION'), array('\,' => ',', '\;' => ';'));
        $categories = $vevent->getAsString('CATEGORIES');
        $description = strtr($vevent->getAsString('DESCRIPTION'), array('\,' => ',', '\;' => ';'));
        
        $options['id'] = $id;
        $options['lastmodified'] = $event['lastmodified'];
        $options['title'] = $summary;
        $options['location'] = $location;
        $options['categories'] = $categories;
        $options['calendarid'] = $event['calendarid'];
        $options['allday'] = $allday;
        $options['startdate'] = $startdate;
        $options['starttime'] = $starttime;
        $options['enddate'] = $enddate;
        $options['endtime'] = $endtime;
        $options['description'] = $description;
        $options['accessclass'] = 'PUBLIC';
        $options['access_class_options'] = CalendarService::getAccessClassOptions();

        $attachment['rows'] = AttachmentService::get($event['attachment']);
        $calendar = CalendarService::getCalendar($event['calendarid'], false);
        $share = ShareService::getItem('event', $id);

        return $this->render(array(
            'attachment' => $attachment,
            'options' => $options,
            'calendar' => $calendar,
            'share' => $share,
        ));
    }

    public function delete()
    {
        $id = Request::get('id');
        if ($id > 0) {
            CalendarService::remove($id);
            ShareService::removeItem('event', $id);

            return $this->json('删除成功。', true);
        }
    }
}
