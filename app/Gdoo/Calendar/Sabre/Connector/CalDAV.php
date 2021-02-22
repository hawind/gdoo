<?php namespace Gdoo\Calendar\Sabre\Connector;

/**
 * PDO CalDAV backend
 *
 * This backend is used to store calendar-data in a PDO database, such as
 * sqlite or MySQL
 *
 * @copyright Copyright (C) 2007-2014 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */

use Gdoo\Index\Models\Share;
use Gdoo\Calendar\Models\Calendar;
use Gdoo\Calendar\Models\CalendarObject;

use Gdoo\Index\Services\ShareService;
use Gdoo\Calendar\Services\CalendarService;
use Gdoo\Calendar\Services\CalendarObjectService;
use Illuminate\Support\Arr;

class CalDAV extends \Sabre\CalDAV\Backend\AbstractBackend
{
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
     * List of CalDAV properties, and how they map to database fieldnames
     * Add your own properties by simply adding on to this array.
     *
     * Note that only string-based properties are supported here.
     *
     * @var array
     */
    public $propertyMap = array(
        '{DAV:}displayname'                                   => 'displayname',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
        '{urn:ietf:params:xml:ns:caldav}calendar-timezone'    => 'timezone',
        '{http://apple.com/ns/ical/}calendar-order'           => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color'           => 'calendarcolor',
    );

    /**
     * Returns a list of calendars for a principal.
     *
     * Every project is an array with the following keys:
     *  * id, a unique id that will be used by other functions to modify the
     *    calendar. This can be the same as the uri or a database key.
     *  * uri, which the basename of the uri with which the calendar is
     *    accessed.
     *  * principaluri. The owner of the calendar. Almost always the same as
     *    principalUri passed to this method.
     *
     * Furthermore it can contain webdav properties in clark notation. A very
     * common one is '{DAV:}displayname'.
     *
     * @param string $principalUri
     * @return array
     */
    public function getCalendarsForUser($principalUri)
    {
        $fields = array_values($this->propertyMap);
        $fields[] = 'id';
        $fields[] = 'uri';
        $fields[] = 'ctag';
        $fields[] = 'components';
        $fields[] = 'principaluri';
        $fields[] = 'transparent';

        $rows = Calendar::where('principaluri', $principalUri)->get($fields);
        
        foreach ($rows as $row) {
            $components = array();
            if ($row->components) {
                $components = explode(',', $row->components);
            }

            $calendar = array(
                'id'  => $row->id,
                'uri' => $row->uri,
                'principaluri' => $row->principaluri,
                '{' . \Sabre\CalDAV\Plugin::NS_CALENDARSERVER . '}getctag' => $row->ctag?$row->ctag:'0',
                '{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new \Sabre\CalDAV\Property\SupportedCalendarComponentSet($components),
                '{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp' => new \Sabre\CalDAV\Property\ScheduleCalendarTransp($row->transparent?'transparent':'opaque'),
            );

            foreach ($this->propertyMap as $xmlName => $dbName) {
                $calendar[$xmlName] = $row[$dbName];
            }
            $calendars[] = $calendar;
        }
        return $calendars;
    }

    /**
     * Creates a new calendar for a principal.
     *
     * If the creation was a success, an id must be returned that can be used to reference
     * this calendar in other methods, such as updateCalendar
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array $properties
     * @return string
     */
    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
        $fieldNames = array(
            'principaluri',
            'uri',
            'ctag',
            'transparent',
        );
        $values = array(
            ':principaluri' => $principalUri,
            ':uri' => $calendarUri,
            ':ctag'  => 1,
            ':transparent'  => 0,
        );

        $data = array(
            'principaluri' => $principalUri,
            'uri' => $calendarUri,
            'ctag' => 1,
            'transparent' => 0,
        );

        // Default value
        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        $fieldNames[] = 'components';
        if (!isset($properties[$sccs])) {
            $values[':components'] = 'VEVENT,VTODO';
        } else {
            if (!($properties[$sccs] instanceof \Sabre\CalDAV\Property\SupportedCalendarComponentSet)) {
                throw new \Sabre\DAV\Exception('The ' . $sccs . ' property must be of type: \Sabre\CalDAV\Property\SupportedCalendarComponentSet');
            }
            $values[':components'] = implode(',', $properties[$sccs]->getValue());
        }
        $transp = '{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp';
        if (isset($properties[$transp])) {
            $values[':transparent'] = $properties[$transp]->getValue()==='transparent';
        }

        foreach ($this->propertyMap as $xmlName => $dbName) {
            if (isset($properties[$xmlName])) {
                $values[':' . $dbName] = $properties[$xmlName];
                $fieldNames[] = $dbName;

                $data[$dbName] = $properties[$xmlName];
            }
        }
        return Calendar::insertGetId($data);
    }

    /**
     * Updates properties for a calendar.
     *
     * The mutations array uses the propertyName in clark-notation as key,
     * and the array value for the property value. In the case a property
     * should be deleted, the property value will be null.
     *
     * This method must be atomic. If one property cannot be changed, the
     * entire operation must fail.
     *
     * If the operation was successful, true can be returned.
     * If the operation failed, false can be returned.
     *
     * Deletion of a non-existent property is always successful.
     *
     * Lastly, it is optional to return detailed information about any
     * failures. In this case an array should be returned with the following
     * structure:
     *
     * array(
     *   403 => array(
     *      '{DAV:}displayname' => null,
     *   ),
     *   424 => array(
     *      '{DAV:}owner' => null,
     *   )
     * )
     *
     * In this example it was forbidden to update {DAV:}displayname.
     * (403 Forbidden), which in turn also caused {DAV:}owner to fail
     * (424 Failed Dependency) because the request needs to be atomic.
     *
     * @param string $calendarId
     * @param array $mutations
     * @return bool|array
     */
    public function updateCalendar($calendarId, array $mutations)
    {
        $newValues = array();
        $result = array(
            200 => array(), // Ok
            403 => array(), // Forbidden
            424 => array(), // Failed Dependency
        );

        $hasError = false;

        foreach ($mutations as $propertyName => $propertyValue) {
            switch ($propertyName) {
                case '{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp':
                    $fieldName = 'transparent';
                    $newValues[$fieldName] = $propertyValue->getValue()==='transparent';
                    break;
                default:
                    // Checking the property map
                    if (!isset($this->propertyMap[$propertyName])) {
                        // We don't know about this property.
                        $hasError = true;
                        $result[403][$propertyName] = null;
                        unset($mutations[$propertyName]);
                        continue;
                    }

                    $fieldName = $this->propertyMap[$propertyName];
                    $newValues[$fieldName] = $propertyValue;
            }
        }

        // If there were any errors we need to fail the request
        if ($hasError) {
            // Properties has the remaining properties
            foreach ($mutations as $propertyName => $propertyValue) {
                $result[424][$propertyName] = null;
            }

            // Removing unused statuscodes for cleanliness
            foreach ($result as $status => $properties) {
                if (is_array($properties) && count($properties)===0) {
                    unset($result[$status]);
                }
            }

            return $result;
        }

        // Success

        // Now we're generating the sql query.
        Calendar::where('id', $calendarId)->update($newValues);
        CalendarService::touchCalendar($calendarId);
        return true;
    }

    /**
     * Delete a calendar and all it's objects
     *
     * @param string $calendarId
     * @return void
     */
    public function deleteCalendar($calendarId)
    {
        CalendarService::deleteCalendar($calendarId);
    }

    /**
     * Returns all calendar objects within a calendar.
     *
     * Every item contains an array with the following keys:
     *   * id - unique identifier which will be used for subsequent updates
     *   * calendardata - The iCalendar-compatible calendar data
     *   * uri - a unique key which will be used to construct the uri. This can be any arbitrary string.
     *   * lastmodified - a timestamp of the last modification time
     *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
     *   '  "abcdef"')
     *   * calendarid - The calendarid as it was passed to this function.
     *   * size - The size of the calendar objects, in bytes.
     *
     * Note that the etag is optional, but it's highly encouraged to return for
     * speed reasons.
     *
     * The calendardata is also optional. If it's not returned
     * 'getCalendarObject' will be called later, which *is* expected to return
     * calendardata.
     *
     * If neither etag or size are specified, the calendardata will be
     * used/fetched to determine these numbers. If both are specified the
     * amount of times this is needed is reduced by a great degree.
     *
     * @param string $calendarId
     * @return array
     */
    public function getCalendarObjects($calendarId)
    {
        $model = CalendarObject::select(['id', 'uri', 'lastmodified', 'etag', 'calendarid', 'size']);
        if ($calendarId == 'share-events') {
            $shared = ShareService::getItemsSourceBy(['event'], \Auth::id());
            if ($shared->isEmpty()) {
                return array();
            }
            $share_id = Arr::pluck($shared, 'source_id');
            $rows = $model->whereIn('id', $share_id)->get();
            foreach ($rows as $key => $row) {
                $rows[$key]['calendarid'] = 'share-events';
            }
        } elseif ($calendarId > 0) {
            $rows = $model->where('calendarid', $calendarId)->get();
        }

        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'id' => $row->id,
                'uri' => $row->uri,
                'lastmodified' => $row->lastmodified,
                'etag' => '"' . $row->etag . '"',
                'calendarid' => $row->calendarid,
                'size' => (int)$row->size,
            );
        }
        return $result;
    }

    /**
     * Returns information from a single calendar object, based on it's object
     * uri.
     *
     * The returned array must have the same keys as getCalendarObjects. The
     * 'calendardata' object is required here though, while it's not required
     * for getCalendarObjects.
     *
     * This method must return null if the object did not exist.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @return array|null
     */
    public function getCalendarObject($calendarId, $objectUri)
    {
        $model = CalendarObject::where('uri', $objectUri);
        if ($calendarId == 'share-events') {
            $shared = ShareService::getItemsSourceBy(['event'], \Auth::id());
            if ($shared->isEmpty()) {
                return array();
            }

            $shareId = Arr::pluck($shared, 'source_id');
            $shareUsersList = Arr::pluck($shared, 'name', 'source_id');

            $row = $model->whereIn('id', $shareId)->first();
            $row->calendarid = 'share-events';

            $vcalendar = \Sabre\VObject\Reader::read($row->calendardata);
            $vcalendar->VEvent->SUMMARY->value = '['.$shareUsersList[$row->id].']'.$vcalendar->VEvent->SUMMARY->value;
            $row->calendardata = $vcalendar->serialize();
        } elseif ($calendarId > 0) {
            $row = $model->where('calendarid', $calendarId)->first();
        }

        if (empty($row)) {
            return array();
        }
        return array(
            'id' => $row->id,
            'uri' => $row->uri,
            'lastmodified' => $row->lastmodified,
            'etag' => '"' . $row->etag . '"',
            'calendarid' => $row->calendarid,
            'size' => (int)$row->size,
            'calendardata' => $row->calendardata,
         );
    }

    /**
     * Creates a new calendar object.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $extraData = $this->getDenormalizedData($calendarData);
        $data = array(
            'calendarid'     => $calendarId,
            'uri'            => $objectUri,
            'calendardata'   => $calendarData,
            'lastmodified'   => time(),
            'etag'           => $extraData['etag'],
            'size'           => $extraData['size'],
            'componenttype'  => $extraData['componentType'],
            'firstoccurence' => $extraData['firstOccurence'],
            'lastoccurence'  => $extraData['lastOccurence'],
        );
        CalendarObject::insert($data);
        CalendarService::touchCalendar($calendarId);
        return '"' . $extraData['etag'] . '"';
    }

    /**
     * Updates an existing calendarobject, based on it's uri.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $extraData = $this->getDenormalizedData($calendarData);
        $update = array(
            'calendardata'   => $calendarData,
            'lastmodified'   => time(),
            'etag'           => $extraData['etag'],
            'size'           => $extraData['size'],
            'componenttype'  => $extraData['componentType'],
            'firstoccurence' => $extraData['firstOccurence'],
            'lastoccurence'  => $extraData['lastOccurence'],
        );
        CalendarObject::where('calendarid', $calendarId)->where('uri', $objectUri)->update($update);
        CalendarService::touchCalendar($calendarId);
        return '"' . $extraData['etag'] . '"';
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
    protected function getDenormalizedData($calendarData)
    {
        $vObject = \Sabre\VObject\Reader::read($calendarData);
        $componentType = null;
        $component = null;
        $firstOccurence = null;
        $lastOccurence = null;
        foreach ($vObject->getComponents() as $component) {
            if ($component->name!=='VTIMEZONE') {
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
                    $endDate->add(\Sabre\VObject\DateTimeParser::parse($component->DURATION->getValue()));
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
            }
        }

        return array(
            'etag' => md5($calendarData),
            'size' => strlen($calendarData),
            'componentType' => $componentType,
            'firstOccurence' => $firstOccurence,
            'lastOccurence' => $lastOccurence,
        );
    }

    /**
     * Deletes an existing calendar object.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @return void
     */
    public function deleteCalendarObject($calendarId, $objectUri)
    {
        CalendarObject::where('calendarid', $calendarId)->where('uri', $objectUri)->delete();
        CalendarService::touchCalendar($calendarId);
    }

    /**
     * Performs a calendar-query on the contents of this calendar.
     *
     * The calendar-query is defined in RFC4791 : CalDAV. Using the
     * calendar-query it is possible for a client to request a specific set of
     * object, based on contents of iCalendar properties, date-ranges and
     * iCalendar component types (VTODO, VEVENT).
     *
     * This method should just return a list of (relative) urls that match this
     * query.
     *
     * The list of filters are specified as an array. The exact array is
     * documented by \Sabre\CalDAV\CalendarQueryParser.
     *
     * Note that it is extremely likely that getCalendarObject for every path
     * returned from this method will be called almost immediately after. You
     * may want to anticipate this to speed up these requests.
     *
     * This method provides a default implementation, which parses *all* the
     * iCalendar objects in the specified calendar.
     *
     * This default may well be good enough for personal use, and calendars
     * that aren't very large. But if you anticipate high usage, big calendars
     * or high loads, you are strongly adviced to optimize certain paths.
     *
     * The best way to do so is override this method and to optimize
     * specifically for 'common filters'.
     *
     * Requests that are extremely common are:
     *   * requests for just VEVENTS
     *   * requests for just VTODO
     *   * requests with a time-range-filter on a VEVENT.
     *
     * ..and combinations of these requests. It may not be worth it to try to
     * handle every possible situation and just rely on the (relatively
     * easy to use) CalendarQueryValidator to handle the rest.
     *
     * Note that especially time-range-filters may be difficult to parse. A
     * time-range filter specified on a VEVENT must for instance also handle
     * recurrence rules correctly.
     * A good example of how to interprete all these filters can also simply
     * be found in \Sabre\CalDAV\CalendarQueryFilter. This class is as correct
     * as possible, so it gives you a good idea on what type of stuff you need
     * to think of.
     *
     * This specific implementation (for the PDO) backend optimizes filters on
     * specific components, and VEVENT time-ranges.
     *
     * @param string $calendarId
     * @param array $filters
     * @return array
     */
    public function calendarQuery($calendarId, array $filters)
    {
        $result = array();
        $validator = new \Sabre\CalDAV\CalendarQueryValidator();

        $componentType = null;
        $requirePostFilter = true;
        $timeRange = null;

        // if no filters were specified, we don't need to filter after a query
        if (!$filters['prop-filters'] && !$filters['comp-filters']) {
            $requirePostFilter = false;
        }

        // Figuring out if there's a component filter
        if (count($filters['comp-filters']) > 0 && !$filters['comp-filters'][0]['is-not-defined']) {
            $componentType = $filters['comp-filters'][0]['name'];

            // Checking if we need post-filters
            if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['time-range'] && !$filters['comp-filters'][0]['prop-filters']) {
                $requirePostFilter = false;
            }
            // There was a time-range filter
            if ($componentType == 'VEVENT' && isset($filters['comp-filters'][0]['time-range'])) {
                $timeRange = $filters['comp-filters'][0]['time-range'];

                // If start time OR the end time is not specified, we can do a
                // 100% accurate mysql query.
                if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['prop-filters'] && (!$timeRange['start'] || !$timeRange['end'])) {
                    $requirePostFilter = false;
                }
            }
        }

        if ($requirePostFilter) {
            $model = CalendarObject::select(array('uri', 'calendardata'));
        } else {
            $model = CalendarObject::select(array('uri'));
        }

        if ($calendarId == 'share-events') {
            $shared = ShareService::getItemsSourceBy(['event'], \Auth::id());
            $shareId = Arr::pluck($shared, 'source_id');
            $model->whereIn('id', $shareId);
        } else {
            $model->where('calendarid', $calendarId);
        }

        if ($componentType) {
            $model->where('componenttype', $componentType);
        }

        if ($timeRange && $timeRange['start']) {
            $model->where('lastoccurence', '>', $timeRange['start']->getTimeStamp());
        }
        if ($timeRange && $timeRange['end']) {
            $model->where('firstoccurence', '<', $timeRange['end']->getTimeStamp());
        }
        $rows = $model->get()->toArray();
        foreach ($rows as $row) {
            if ($requirePostFilter) {
                if (!$this->validateFilterForObject($row, $filters)) {
                    continue;
                }
            }
            $result[] = $row['uri'];
        }
        return $result;
    }
}
