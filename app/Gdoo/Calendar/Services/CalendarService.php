<?php namespace Gdoo\Calendar\Services;

/**
 * This class manages our calendar objects
 */

use Auth;
use App\Support\VObject;
use Gdoo\Index\Services\AttachmentService;
use Gdoo\Calendar\Models\Calendar;
use Gdoo\Calendar\Models\CalendarObject;
use Gdoo\Calendar\Models\CalendarReminder;
use Gdoo\Index\Services\ShareService;

class CalendarService
{
    /**
     * @brief timezone of the user
     */
    public static $tz;

    /**
     * We need to specify a max date, because we need to stop *somewhere*
     *
     * On 32 bit system the maximum for a signed integer is 2147483647, so
     * MAX_DATE cannot be higher than date('Y-m-d', 2147483647) which results
     * in 2038-01-19 to avoid problems when the date is converted
     * to a unix timestamp.
     */
    const MAX_DATE = '2038-01-01';

    /**
     * @return (string) $timezone as set by user or the default timezone
     */
    public static function getTimezone()
    {
        self::$tz = date_default_timezone_get();
        return self::$tz;
    }
    
    public static function getCalendars($userId, $active = false)
    {
        $model = Calendar::where('userid', $userId);
        if ($active) {
            $model->where('active', $active);
        }

        // 如果用户没有日历则添加一个默认的
        if ($model->count() == 0) {
            static::addDefaultCalendar($userId);
        }

        $rows = $model->get();

        return $rows;
    }

    public static function getCalendar($id, $security = true)
    {
        $calendar = Calendar::find($id);

        // FIXME: Correct arguments to just check for permissions
        if ($security === true) {
            if (Auth::id() == $calendar['userid']) {
                return $calendar;
            } else {
                return false;
            }
        }
        return $calendar;
    }

    public static function setCalendarActive($id, $active)
    {
        Calendar::where('id', $id)->update(array('active'=>$active));
    }

    public static function addCalendar($userid, $name, $components='VEVENT,VTODO,VJOURNAL', $timezone=null, $order=0, $color=null)
    {
        $uri = self::createURI();
        $data = array(
            'userid' => $userid,
            'displayname' => $name,
            'principaluri' => 'principals/'.Auth::user()->id,
            'uri' => $uri,
            'ctag' => 1,
            'calendarorder' => $order,
            'calendarcolor' => $color,
            'timezone' => $timezone,
            'components' => $components
        );
        return Calendar::insertGetId($data);
    }

    public static function editCalendar($id, $name = null, $components = null, $timezone = null, $order = null, $color = null)
    {
        $calendar = self::getCalendar($id, false);
        if ($calendar['userid'] != Auth::id()) {
            abort_error('您没有权限编辑此日历。');
        }

        if (is_null($name)) {
            $name = $calendar['displayname'];
        }
        if (is_null($components)) {
            $components = $calendar['components'];
        }
        if (is_null($timezone)) {
            $timezone = $calendar['timezone'];
        }
        if (is_null($order)) {
            $order = $calendar['calendarorder'];
        }
        if (is_null($color)) {
            $color = $calendar['calendarcolor'];
        }

        $data = array(
            'displayname' => $name,
            'calendarorder' => $order,
            'calendarcolor' => $color,
            'timezone' => $timezone,
            'components' => $components,
        );
        Calendar::where('id', $id)->update($data);
        self::touchCalendar($id);
        return $id;
    }

    /**
     * @brief gets the userid from a principal path
     * @return string
     */
    public static function extractUserID($principaluri)
    {
        list($prefix, $userid) = \Sabre\DAV\URLUtil::splitPath($principaluri);
        return $userid;
    }

    public static function addDefaultCalendar($userid = 0)
    {
        if ($userid == 0) {
            $userid = Auth::id();
        }
        $id = self::addCalendar($userid, '默认日历');
        return true;
    }

    /**
     * @brief removes a calendar
     * @param integer $id
     * @return boolean
     */
    public static function deleteCalendar($id)
    {
        $calendar = self::getCalendar($id, false);

        if ($calendar['userid'] != Auth::id()) {
            abort_error('您没有权限删除此日历。');
        }
        $events = CalendarObject::where('calendarid', $id)->get();
        if (sizeof($events)) {
            foreach ($events as $event) {
                AttachmentService::remove($event->attachment);
            }
            CalendarObject::where('calendarid', $id)->delete();
        }
        Calendar::where('id', $id)->delete();
        return true;
    }

    public static function createURI()
    {
        return substr(md5(rand().time()), 0, 10);
    }

    /**
     * @brief Returns all objects of a calendar between $start and $end
     * @param array $id
     * @param int $start
     * @param int $end
     * @return array
     *
     * The objects are associative arrays. You'll find the original vObject
     * in ['calendardata']
     */
    public static function getRangeEvents($id, $start, $end, $shared = false)
    {
        $start = strtotime($start);
        $end = strtotime($end);

        $model = CalendarObject::whereRaw('(
            (firstoccurence between '.$start.' and '.$end.' or lastoccurence between '.$start.' and '.$end.')
             or (rrule = 1 and firstoccurence <= '.$end.')
        )');

        // 获取分享事件
        if ($shared == true) {
            $model->whereIn('id', $id);
        } elseif (count($id)) {
            $model->whereIn('calendarid', $id);
        }
        $events = self::getRruleEvents($model->get(), $start, $end);
        
        return $events;
    }

    public static function getRruleEvents($rows, $start, $end)
    {
        $result = [];
        foreach ($rows as $key => $row) {
            $vcalendar = \Sabre\VObject\Reader::read($row->calendardata);

            $output = [];

            if ($vcalendar->name === 'VEVENT') {
                $vevent = $vcalendar;
            } elseif (isset($vcalendar->VEVENT)) {
                $vevent = $vcalendar->VEVENT;
            } else {
                return $output;
            }

            $allday = ($vcalendar->VEVENT->DTSTART->getDateType() == \Sabre\VObject\Property\DateTime::DATE) ? true : false;

            $output = array(
                'id'           => (int)$row->id,
                'calendarid'   => (int)$row->calendarid,
                'title'        => (isset($vevent->SUMMARY) && $vevent->SUMMARY->value) ? strtr($vevent->SUMMARY->value, array('\,' => ',', '\;' => ';')) : 'unnamed',
                'description'  => (isset($vevent->DESCRIPTION) && $vevent->DESCRIPTION->value) ? strtr($vevent->DESCRIPTION->value, array('\,' => ',', '\;' => ';')) : '',
                'location'     => (isset($vevent->LOCATION) && $vevent->LOCATION->value) ? strtr($vevent->LOCATION->value, array('\,' => ',', '\;' => ';')) : '',
                'lastmodified' => $row->lastmodified,
                'allDay'       => $allday,
            );

            if ($vcalendar->VEVENT->RRULE) {
                $start_dt = \DateTime::createFromFormat('U', $start);
                $end_dt = \DateTime::createFromFormat('U', $end);
                $vcalendar->expand($start_dt, $end_dt);
            }

            foreach ($vcalendar->getComponents() as $vevent) {
                if (!($vevent instanceof \Sabre\VObject\Component\VEvent)) {
                    continue;
                }
                $rrule = self::generateStartEndDate($vevent->DTSTART, self::getDTEndFromVEvent($vevent), $allday, self::$tz);
                $result[] = array_merge($output, $rrule);
            }
        }
        return $result;
    }

    /**
     * @brief Returns all objects of a calendar
     * @param integer $id
     * @return array
     *
     * The objects are associative arrays. You'll find the original vObject in
     * ['calendardata']
     */
    public static function getEvent($id)
    {
        return CalendarObject::find($id);
    }

    /**
     * @brief Adds an object
     * @param integer $id
     * @param integer $calendardata
     * @param string $attachment
     * @return integer
     */
    public static function add($params, $vcalendar)
    {
        $calendarid = $params['calendarid'];
        $calendar = self::getCalendar($calendarid);
        
        if ($calendar['userid'] != Auth::id()) {
            abort_error('您没有权限添加事件到此日历。');
        }

        $calendardata = $vcalendar->serialize();
        $extraData = self::getDenormalizedData($calendardata);
        $uri = self::createURI().'.ics';
        $data = [
            'calendarid' => $calendarid,
            'calendardata' => $calendardata,
            'uri' => $uri,
            'rrule' => $extraData['rrule'],
            'etag' => $extraData['etag'],
            'size' => $extraData['size'],
            'lastmodified' => time(),
            'attachment' => $params['attachment'],
            'componenttype' => $extraData['componentType'],
            'firstoccurence' => $extraData['firstOccurence'],
            'lastoccurence' => $extraData['lastOccurence'],
        ];
        $objectId = CalendarObject::insertGetId($data);

        // 是重复事件
        $is_recurring = $vcalendar->VEVENT->RRULE ? 1 : 0;

        // 事件提醒
        if (isset($vcalendar->VEVENT->VALARM)) {
            $triggerTime = $vcalendar->VEVENT->VALARM->getEffectiveTriggerTime();
            $valarm_at = $triggerTime->getTimeStamp();
            CalendarReminder::insert([
                'calendar_id' => $calendarid,
                'object_id' => $objectId,
                'is_recurring' => $is_recurring,
                'alarm_at' => $valarm_at,
            ]);
        }

        // 写入共享数据
        ShareService::addItem([
            'source_id' => $objectId,
            'source_type' => 'event',
            'is_repeat' => $is_recurring,
            'receive_id' => $params['receive_id'],
            'receive_name' => $params['receive_name'],
            'start_at' => $extraData['firstOccurence'],
            'end_at' => $extraData['lastOccurence'],
        ]);
        
        self::touchCalendar($calendarid);
        return $objectId;
    }

    /**
     * @brief edits an object
     * @param integer $id id of object
     * @param string $data  object
     * @return boolean
     */
    public static function edit($params, $vcalendar)
    {
        $event = self::getEvent($params['id']);
        $calendar = self::getCalendar($event['calendarid']);

        if ($calendar['userid'] != Auth::id()) {
            abort_error('您没有权限编辑此事件。');
        }

        $calendardata = $vcalendar->serialize();
        $extraData = self::getDenormalizedData($calendardata);
        $data = [
            'calendardata' => $calendardata,
            'lastmodified' => time(),
            'rrule' => $extraData['rrule'],
            'etag' => $extraData['etag'],
            'size' => $extraData['size'],
            'componenttype' => $extraData['componentType'],
            'firstoccurence' => $extraData['firstOccurence'],
            'lastoccurence' => $extraData['lastOccurence'],
        ];

        // 存在附件字段
        if (isset($params['attachment'])) {
            $data['attachment'] = $params['attachment'];
        }

        CalendarObject::where('id', $params['id'])->update($data);

        // 是重复事件
        $is_recurring = $vcalendar->VEVENT->RRULE ? 1 : 0;

        // 事件提醒
        if ($vcalendar->VEVENT->VALARM->TRIGGER) {
            $triggerTime = $vcalendar->VEVENT->VALARM->getEffectiveTriggerTime();
            $valarm_at = $triggerTime->getTimeStamp();
            $reminder = CalendarReminder::firstOrNew(['calendar_id' => $event['calendarid'], 'object_id' => $event['id']]);
            $reminder->is_recurring = $is_recurring;
            $reminder->alarm_at = $valarm_at;
            $reminder->save();
        } else {
            CalendarReminder::where('object_id', $event['id'])->delete();
        }

        // 修改共享数据
        $share_data = [
            'source_id' => $params['id'],
            'source_type' => 'event',
            'is_repeat' => $is_recurring,
            'start_at' => $extraData['firstOccurence'],
            'end_at' => $extraData['lastOccurence'],
        ];

        // 存在接收人字段
        if (isset($params['receive_id'])) {
            $share_data['receive_id'] = $params['receive_id'];
            $share_data['receive_name'] = $params['receive_name'];
        }

        $share = ShareService::getItem('event', $params['id']);
        if (empty($share)) {
            ShareService::addItem($share_data);
        } else {
            ShareService::editItem('event', $params['id'], $share_data);
        }

        self::touchCalendar($event['calendarid']);
        return true;
    }

    /**
     * @brief deletes an object
     * @param integer $id id of object
     * @return boolean
     */
    public static function remove($id)
    {
        $event = self::getEvent($id);
        $calendar = self::getCalendar($event['calendarid']);

        if ($calendar['userid'] != Auth::id()) {
            abort_error('您没有权限删除此事件。');
        }

        AttachmentService::remove($event['attachment']);
        CalendarObject::where('id', $id)->delete();
        CalendarReminder::where('object_id', $id)->delete();
        self::touchCalendar($event['calendarid']);
        return true;
    }

    public static function moveToCalendar($id, $calendarid)
    {
        $calendar = self::getCalendar($calendarid);
        if ($calendar['userid'] != Auth::id()) {
            abort_error('您没有权限添加事件到此日历。');
        }
        CalendarObject::where('id', $id)->update(array('calendarid'=>$calendarid));
        self::touchCalendar($calendarid);
        return true;
    }

    /**
     * Parses some information from calendar objects, used for optimized
     * calendar-queries.
     *
     * Returns an array with the following keys:
     *   * etag
     *   * size
     *   * componentType
     *   * firstOccurence
     *   * lastOccurence
     *
     * @param string $calendarData
     * @return array
     */
    public static function getDenormalizedData($calendarData)
    {
        $vObject = \Sabre\VObject\Reader::read($calendarData);

        $componentType  = null;
        $component      = null;
        $firstOccurence = null;
        $rrule          = 0;

        foreach ($vObject->getComponents() as $component) {
            if ($component->name !== 'VTIMEZONE') {
                $componentType = $component->name;
                break;
            }
        }

        if (!$componentType) {
            throw new \Sabre\DAV\Exception\BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
        }

        if ($componentType === 'VEVENT') {
            $firstOccurence = $component->DTSTART->getDateTime()->getTimeStamp();
            // Finding the last occurence is a bit harder
            if (!isset($component->RRULE)) {
                if (isset($component->DTEND)) {
                    $lastOccurence = $component->DTEND->getDateTime()->getTimeStamp();
                } elseif (isset($component->DURATION)) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->add(\VObject\DateTimeParser::parse($component->DURATION->getValue()));
                    $lastOccurence = $endDate->getTimeStamp();
                } elseif (!$component->DTSTART->hasTime()) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->modify('+1 day');
                    $lastOccurence = $endDate->getTimeStamp();
                } else {
                    $lastOccurence = $firstOccurence;
                }
            } else {
                $it = new \Sabre\VObject\RecurrenceIterator($vObject, (string)$component->UID);
                $maxDate = new \DateTime(self::MAX_DATE);
                if ($it->isInfinite()) {
                    $lastOccurence = $maxDate->getTimeStamp();
                } else {
                    $end = $it->getDtEnd();
                    while ($it->valid() && $end < $maxDate) {
                        $end = $it->getDtEnd();
                        $it->next();
                    }
                    $lastOccurence = $end->getTimeStamp();
                }
                $rrule = 1;
            }
        }

        return array(
            'etag' => md5($calendarData),
            'size' => strlen($calendarData),
            'rrule' => $rrule,
            'componentType' => $componentType,
            'firstOccurence' => $firstOccurence,
            'lastOccurence' => $lastOccurence,
        );
    }

    /**
     * @brief returns the DTEND of an $vevent object
     * @param object $vevent vevent object
     * @return object
     */
    public static function getDTEndFromVEvent($vevent)
    {
        if ($vevent->DTEND) {
            $dtend = $vevent->DTEND;
        } else {
            $dtend = clone $vevent->DTSTART;
            // clone creates a shallow copy, also clone DateTime
            $dtend->setDateTime(clone $dtend->getDateTime(), $dtend->getDateType());
            
            if ($vevent->DURATION) {
                $duration = strval($vevent->DURATION);
                $invert = 0;
                if ($duration[0] == '-') {
                    $duration = substr($duration, 1);
                    $invert = 1;
                }
                if ($duration[0] == '+') {
                    $duration = substr($duration, 1);
                }
                $interval = new \DateInterval($duration);
                $interval->invert = $invert;
                $dtend->getDateTime()->add($interval);
            }
        }
        return $dtend;
    }

    /**
     * @brief Get the permissions determined by the access class of an event/todo/journal
     * @param VObject $vobject Sabre VObject
     * @return (int) $permissions - CRUDS permissions
     * @see OCP\Share
     */
    public static function getAccessClassPermissions($vobject)
    {
        if (isset($vobject->VEVENT)) {
            $velement = $vobject->VEVENT;
        } elseif (isset($vobject->VJOURNAL)) {
            $velement = $vobject->VJOURNAL;
        } elseif (isset($vobject->VTODO)) {
            $velement = $vobject->VTODO;
        }
        $accessclass = $velement->getAsString('CLASS');
        return static::getAccessClassPermissions($accessclass);
    }

    /**
     * @brief returns the options for the access class of an event
     * @return array - valid inputs for the access class of an event
     */
    public static function getAccessClassOptions()
    {
        return array(
            'PUBLIC' => '共享',
            'PRIVATE' => '私人',
            'CONFIDENTIAL' => '保密',
        );
    }

    /**
     * @brief returns the options for the repeat rule of an repeating event
     * @return array - valid inputs for the repeat rule of an repeating event
     */
    public static function getValarmOptions()
    {
        return array(
            'time' => array(
                'PT0S' => '事件发生时',
                '-PT5M' => '5分钟前',
                '-PT15M' => '15分钟前',
                '-PT30M' => '30分钟前',
                '-PT1H' => '1小时前',
                '-PT2H' => '2小时前',
                '-P1D' => '1天前',
                '-P2D' => '2天前',
                '-P1W' => '1周前'
            ),
            'day' => array(
                'PT9H' => '事件发生当天(9:00)',
                '-PT15H' => '1天前(9:00)',
                '-P1DT15H' => '2天前(9:00)',
                '-P6DT15H' => '1周前'
            )
        );
    }

    /**
     * @brief returns the options for the repeat rule of an repeating event
     * @return array - valid inputs for the repeat rule of an repeating event
     */
    public static function getRepeatOptions()
    {
        return array(
            'doesnotrepeat' => ' - ',
            'daily' => '每天',
            'weekly' => '每周',
            'weekday' => '每个工作日',
            'biweekly' => '每两周',
            'monthly' => '每月',
            'yearly' => '每年'
        );
    }

    /**
     * @brief returns the options for the end of an repeating event
     * @return array - valid inputs for the end of an repeating events
     */
    public static function getEndOptions()
    {
        return array(
            'never' => '从不',
            'count' => '根据发生次',
            'date' => '根据日期'
        );
    }

    /**
     * @brief returns the options for an monthly repeating event
     * @return array - valid inputs for monthly repeating events
     */
    public static function getMonthOptions()
    {
        return array(
            'monthday' => '根据月天',
            'weekday' => '根据星期'
        );
    }

    /**
     * @brief returns the options for an weekly repeating event
     * @return array - valid inputs for weekly repeating events
     */
    public static function getWeeklyOptions()
    {
        return array(
            'MO' => '星期一',
            'TU' => '星期二',
            'WE' => '星期三',
            'TH' => '星期四',
            'FR' => '星期五',
            'SA' => '星期六',
            'SU' => '星期日'
        );
    }

    /**
     * @brief returns the options for an monthly repeating event which occurs on specific weeks of the month
     * @return array - valid inputs for monthly repeating events
     */
    public static function getWeekofMonth()
    {
        return array(
            'auto' => '事件每月发生的周数',
            '1' => '首先',
            '2' => '其次',
            '3' => '第三',
            '4' => '第四',
            '5' => '第五',
            '-1' => '最后'
        );
    }

    /**
     * @brief returns the options for an yearly repeating event which occurs on specific days of the year
     * @return array - valid inputs for yearly repeating events
     */
    public static function getByYearDayOptions()
    {
        $return = array();
        foreach (range(1, 366) as $num) {
            $return[(string)$num] = (string)$num;
        }
        return $return;
    }

    /**
     * @brief returns the options for an yearly or monthly repeating event which occurs on specific days of the month
     * @return array - valid inputs for yearly or monthly repeating events
     */
    public static function getByMonthDayOptions()
    {
        $return = array();
        foreach (range(1, 31) as $num) {
            $return[(string)$num] = (string)$num;
        }
        return $return;
    }

    /**
     * @brief returns the options for an yearly repeating event which occurs on specific month of the year
     * @return array - valid inputs for yearly repeating events
     */
    public static function getByMonthOptions()
    {
        return array(
            '1'  => '一月',
            '2'  => '二月',
            '3'  => '三月',
            '4'  => '四月',
            '5'  => '五月',
            '6'  => '六月',
            '7'  => '七月',
            '8'  => '八月',
            '9'  => '九月',
            '10' => '十月',
            '11' => '十一月',
            '12' => '十二月'
        );
    }

    /**
     * @brief returns the options for an yearly repeating event
     * @return array - valid inputs for yearly repeating events
     */
    public static function getYearOptions()
    {
        return array(
            'bydate' => '根据时间日期',
            'byyearday' => '根据年数',
            'byweekno' => '根据周数',
            'bydaymonth' => '根据天和月'
        );
    }

    /**
     * @brief returns the options for an yearly repeating event which occurs on specific week numbers of the year
     * @return array - valid inputs for yearly repeating events
     */
    public static function getByWeekNoOptions()
    {
        return range(1, 52);
    }

    /**
     * @brief validates a request
     * @param array $request
     * @return mixed (array / boolean)
     */
    public static function validateRequest($request)
    {
        $errnum = 0;
        $errarr = array('title'=>'false', 'cal'=>'false', 'from'=>'false', 'fromtime'=>'false', 'to'=>'false', 'totime'=>'false', 'endbeforestart'=>'false');
        if ($request['title'] == '') {
            $errarr['title'] = 'true';
            $errnum++;
        }

        list($fromyear, $frommonth, $fromday) = explode('-', $request['from']);
        if (!checkdate($frommonth, $fromday, $fromyear)) {
            $errarr['from'] = 'true';
            $errnum++;
        }
        $allday = isset($request['allday']);
        if (!$allday && self::checkTime(urldecode($request['fromtime']))) {
            $errarr['fromtime'] = 'true';
            $errnum++;
        }

        list($toyear, $tomonth, $today) = explode('-', $request['to']);
        if (!checkdate($tomonth, $today, $toyear)) {
            $errarr['to'] = 'true';
            $errnum++;
        }
        if ($request['repeat'] != 'doesnotrepeat') {
            if (is_nan($request['interval']) && $request['interval'] != '') {
                $errarr['interval'] = 'true';
                $errnum++;
            }
            if (array_key_exists('repeat', $request) && !array_key_exists($request['repeat'], self::getRepeatOptions())) {
                $errarr['repeat'] = 'true';
                $errnum++;
            }
            if (array_key_exists('advanced_month_select', $request) && !array_key_exists($request['advanced_month_select'], self::getMonthOptions())) {
                $errarr['advanced_month_select'] = 'true';
                $errnum++;
            }
            if (array_key_exists('advanced_year_select', $request) && !array_key_exists($request['advanced_year_select'], self::getYearOptions())) {
                $errarr['advanced_year_select'] = 'true';
                $errnum++;
            }
            if (array_key_exists('weekofmonthoptions', $request) && !array_key_exists($request['weekofmonthoptions'], self::getWeekofMonth())) {
                $errarr['weekofmonthoptions'] = 'true';
                $errnum++;
            }
            if ($request['end'] != 'never') {
                if (!array_key_exists($request['end'], self::getEndOptions())) {
                    $errarr['end'] = 'true';
                    $errnum++;
                }
                if ($request['end'] == 'count' && is_nan($request['byoccurrences'])) {
                    $errarr['byoccurrences'] = 'true';
                    $errnum++;
                }
                if ($request['end'] == 'date') {
                    list($bydate_year, $bydate_month, $bydate_day) = explode('-', $request['bydate']);
                    if (!checkdate($bydate_month, $bydate_day, $bydate_year)) {
                        $errarr['bydate'] = 'true';
                        $errnum++;
                    }
                }
            }
            if (array_key_exists('weeklyoptions', $request)) {
                foreach ($request['weeklyoptions'] as $option) {
                    if (!array_key_exists($option, self::getWeeklyOptions())) {
                        $errarr['weeklyoptions'] = 'true';
                        $errnum++;
                    }
                }
            }
            if (array_key_exists('byyearday', $request)) {
                foreach ($request['byyearday'] as $option) {
                    if (!array_key_exists($option, self::getByYearDayOptions())) {
                        $errarr['byyearday'] = 'true';
                        $errnum++;
                    }
                }
            }
            if (array_key_exists('weekofmonthoptions', $request)) {
                if (is_nan((double)$request['weekofmonthoptions'])) {
                    $errarr['weekofmonthoptions'] = 'true';
                    $errnum++;
                }
            }
            if (array_key_exists('bymonth', $request)) {
                foreach ($request['bymonth'] as $option) {
                    if (!array_key_exists($option, self::getByMonthOptions())) {
                        $errarr['bymonth'] = 'true';
                        $errnum++;
                    }
                }
            }
            if (array_key_exists('byweekno', $request)) {
                foreach ($request['byweekno'] as $option) {
                    if (!array_key_exists($option, self::getByWeekNoOptions())) {
                        $errarr['byweekno'] = 'true';
                        $errnum++;
                    }
                }
            }
            if (array_key_exists('bymonthday', $request)) {
                foreach ($request['bymonthday'] as $option) {
                    if (!array_key_exists($option, self::getByMonthDayOptions())) {
                        $errarr['bymonthday'] = 'true';
                        $errnum++;
                    }
                }
            }
        }
        if (!$allday && self::checkTime(urldecode($request['totime']))) {
            $errarr['totime'] = 'true';
            $errnum++;
        }
        if ($today < $fromday && $frommonth == $tomonth && $fromyear == $toyear) {
            $errarr['endbeforestart'] = 'true';
            $errnum++;
        }
        if ($today == $fromday && $frommonth > $tomonth && $fromyear == $toyear) {
            $errarr['endbeforestart'] = 'true';
            $errnum++;
        }
        if ($today == $fromday && $frommonth == $tomonth && $fromyear > $toyear) {
            $errarr['endbeforestart'] = 'true';
            $errnum++;
        }
        if (!$allday && $fromday == $today && $frommonth == $tomonth && $fromyear == $toyear) {
            list($tohours, $tominutes) = explode(':', $request['totime']);
            list($fromhours, $fromminutes) = explode(':', $request['fromtime']);
            if ($tohours < $fromhours) {
                $errarr['endbeforestart'] = 'true';
                $errnum++;
            }
            if ($tohours == $fromhours && $tominutes < $fromminutes) {
                $errarr['endbeforestart'] = 'true';
                $errnum++;
            }
        }
        if ($errnum) {
            return $errarr;
        }
        return false;
    }

    /**
     * @brief validates time
     * @param string $time
     * @return boolean
     */
    protected static function checkTime($time)
    {
        if (strpos($time, ':') === false) {
            return true;
        }
        list($hours, $minutes) = explode(':', $time);
        return empty($time) || $hours < 0 || $hours > 24 || $minutes < 0 || $minutes > 60;
    }

    /**
     * @brief creates an VCalendar Object from the request data
     * @param array $request
     * @return object created $vcalendar
     */
    public static function createVCalendarFromRequest($request)
    {
        $vcalendar = new VObject('VCALENDAR');
        $vcalendar->add('PRODID', 'gdoo.com Calendar');
        $vcalendar->add('VERSION', '2.0');

        $vevent = new VObject('VEVENT');
        $vcalendar->add($vevent);
        $vevent->setDateTime('CREATED', 'now', \Sabre\VObject\Property\DateTime::UTC);
        $vevent->setUID();

        return self::updateVCalendarFromRequest($request, $vcalendar);
    }

    /**
     * @brief updates an VCalendar Object from the request data
     * @param array $request
     * @param object $vcalendar
     * @return object updated $vcalendar
     */
    public static function updateVCalendarFromRequest($request, $vobject)
    {
        $accessclass = $request["accessclass"];
        $title = $request["title"];
        $location = $request["location"];
        $categories = $request["categories"];
        $allday = isset($request["allday"]);

        $from = $request["from"];
        $to  = $request["to"];

        if (!$allday) {
            $fromtime = $request['fromtime'];
            $totime = $request['totime'];
        }
        $vevent = $vobject->VEVENT;
        $description = $request["description"];
        $repeat = $request["repeat"];
        if ($repeat != 'doesnotrepeat') {
            $rrule = '';
            $interval = $request['interval'];
            $end = $request['end'];
            $byoccurrences = $request['byoccurrences'];

            switch ($repeat) {
                case 'daily':
                    $rrule .= 'FREQ=DAILY';
                    break;
                case 'weekly':
                    $rrule .= 'FREQ=WEEKLY';
                    if (array_key_exists('weeklyoptions', $request)) {
                        $byday = '';
                        foreach ($request['weeklyoptions'] as $days) {
                            if ($byday == '') {
                                $byday .= $days;
                            } else {
                                $byday .= ',' .$days;
                            }
                        }
                        $rrule .= ';BYDAY=' . $byday;
                    }
                    break;
                case 'weekday':
                    $rrule .= 'FREQ=WEEKLY';
                    $rrule .= ';BYDAY=MO,TU,WE,TH,FR';
                    break;
                case 'biweekly':
                    $rrule .= 'FREQ=WEEKLY';
                    $interval = $interval * 2;
                    break;
                case 'monthly':
                    $rrule .= 'FREQ=MONTHLY';
                    if ($request['advanced_month_select'] == 'monthday') {
                        break;
                    } elseif ($request['advanced_month_select'] == 'weekday') {
                        if ($request['weekofmonthoptions'] == 'auto') {
                            list($_year, $_month, $_day) = explode('-', $from);
                            $weekofmonth = floor($_day/7);
                        } else {
                            $weekofmonth = $request['weekofmonthoptions'];
                        }
                        $byday = '';
                        foreach ($request['weeklyoptions'] as $day) {
                            if ($byday == '') {
                                $byday .= $weekofmonth . $day;
                            } else {
                                $byday .= ',' . $weekofmonth . $day;
                            }
                        }
                        if ($byday == '') {
                            $byday = 'MO,TU,WE,TH,FR,SA,SU';
                        }
                        $rrule .= ';BYDAY=' . $byday;
                    }
                    break;
                case 'yearly':
                    $rrule .= 'FREQ=YEARLY';
                    if ($request['advanced_year_select'] == 'bydate') {
                    } elseif ($request['advanced_year_select'] == 'byyearday') {
                        list($_year, $_month, $_day) = explode('-', $from);
                        $byyearday = date('z', mktime(0, 0, 0, $_month, $_day, $_year)) + 1;
                        if (array_key_exists('byyearday', $request)) {
                            foreach ($request['byyearday'] as $yearday) {
                                $byyearday .= ',' . $yearday;
                            }
                        }
                        $rrule .= ';BYYEARDAY=' . $byyearday;
                    } elseif ($request['advanced_year_select'] == 'byweekno') {
                        list($_year, $_month, $_day) = explode('-', $from);
                        $rrule .= ';BYDAY=' . strtoupper(substr(date('l', mktime(0, 0, 0, $_month, $_day, $_year)), 0, 2));
                        $byweekno = '';
                        foreach ($request['byweekno'] as $weekno) {
                            if ($byweekno == '') {
                                $byweekno = $weekno;
                            } else {
                                $byweekno .= ',' . $weekno;
                            }
                        }
                        $rrule .= ';BYWEEKNO=' . $byweekno;
                    } elseif ($request['advanced_year_select'] == 'bydaymonth') {
                        if (array_key_exists('weeklyoptions', $request)) {
                            $byday = '';
                            foreach ($request['weeklyoptions'] as $day) {
                                if ($byday == '') {
                                    $byday .= $day;
                                } else {
                                    $byday .= ',' . $day;
                                }
                            }
                            $rrule .= ';BYDAY=' . $byday;
                        }
                        if (array_key_exists('bymonth', $request)) {
                            $bymonth = '';
                            foreach ($request['bymonth'] as $month) {
                                if ($bymonth == '') {
                                    $bymonth .= $month;
                                } else {
                                    $bymonth .= ',' . $month;
                                }
                            }
                            $rrule .= ';BYMONTH=' . $bymonth;
                        }
                        if (array_key_exists('bymonthday', $request)) {
                            $bymonthday = '';
                            foreach ($request['bymonthday'] as $monthday) {
                                if ($bymonthday == '') {
                                    $bymonthday .= $monthday;
                                } else {
                                    $bymonthday .= ',' . $monthday;
                                }
                            }
                            $rrule .= ';BYMONTHDAY=' . $bymonthday;
                        }
                    }
                    break;
                default:
                    break;
            }
            if ($interval != '') {
                $rrule .= ';INTERVAL=' . $interval;
            }
            if ($end == 'count') {
                $rrule .= ';COUNT=' . $byoccurrences;
            }
            if ($end == 'date') {
                list($bydate_year, $bydate_month, $bydate_day) = explode('-', $request['bydate']);
                
                $until = $bydate_year . $bydate_month . $bydate_day;
                if ($allday) {
                    $until = $until;
                } else {
                    $until = $until.'T155959Z';
                }
                $rrule .= ';UNTIL=' . $until;
            }
            $repeat = "true";
        } else {
            $rrule = '';
            $repeat = "false";
        }
        $vevent->setString('RRULE', $rrule);

        $vevent->setDateTime('LAST-MODIFIED', 'now', \Sabre\VObject\Property\DateTime::UTC);
        $vevent->setDateTime('DTSTAMP', 'now', \Sabre\VObject\Property\DateTime::UTC);
        $vevent->setString('SUMMARY', $title);

        unset($vevent->VALARM);

        $valarm = \Sabre\VObject\Component::create('VALARM');
        $valarm->ACTION  = 'DTSTART';
        $valarm->SUMMARY = 'Alarm notification';

        if ($allday) {
            $start = new \DateTime($from);
            $end = new \DateTime($to.' +1 day');
            $vevent->setDateTime('DTSTART', $start, \Sabre\VObject\Property\DateTime::DATE);
            $vevent->setDateTime('DTEND', $end, \Sabre\VObject\Property\DateTime::DATE);
            $valarm->TRIGGER = $request['valarm_day'];
        } else {
            $timezone = new \DateTimeZone(self::$tz);
            $start = new \DateTime($from.' '.$fromtime, $timezone);
            $end = new \DateTime($to.' '.$totime, $timezone);
            $vevent->setDateTime('DTSTART', $start, \Sabre\VObject\Property\DateTime::LOCALTZ);
            $vevent->setDateTime('DTEND', $end, \Sabre\VObject\Property\DateTime::LOCALTZ);
            $valarm->TRIGGER = $request['valarm_time'];
        }
        $vevent->add($valarm);

        unset($vevent->DURATION);

        $vevent->setString('CLASS', $accessclass);
        $vevent->setString('LOCATION', $location);
        $vevent->setString('DESCRIPTION', $description);
        $vevent->setString('CATEGORIES', $categories);

        return $vobject;
    }

    /**
     * @brief Updates ctag for calendar
     * @param integer $id
     * @return boolean
     */
    public static function touchCalendar($id)
    {
        $calendar = Calendar::find($id);
        $calendar->ctag + 1;
        $calendar->save();
        return true;
    }

    /**
     * @brief converts the start_dt and end_dt to a new timezone
     * @param object $dtstart
     * @param object $dtend
     * @param boolean $allday
     * @param string $tz
     * @return array
     */
    public static function generateStartEndDate($dtstart, $dtend, $allday, $tz)
    {
        $start_dt = $dtstart->getDateTime();
        $end_dt = $dtend->getDateTime();
        $return = [];

        if ($allday) {
            $return['start'] = $start_dt->format('Y-m-d');
            //$end_dt->modify('-1 minute');
            while ($start_dt >= $end_dt) {
                $end_dt->modify('+1 day');
            }
            $return['end'] = $end_dt->format('Y-m-d');
        } else {
            if ($dtstart->getDateType() !== \Sabre\VObject\Property\DateTime::LOCAL) {
                $start_dt->setTimezone(new \DateTimeZone($tz));
                $end_dt->setTimezone(new \DateTimeZone($tz));
            }
            $return['start'] = $start_dt->format('Y-m-d H:i:s');
            $return['end'] = $end_dt->format('Y-m-d H:i:s');
        }
        return $return;
    }
}
CalendarService::getTimezone();