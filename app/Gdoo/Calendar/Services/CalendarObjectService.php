<?php namespace Gdoo\Calendar\Services;

class CalendarObjectService
{
    public $table = 'calendar_object';

    public static function getEventRepeat($data, $interval = '1D', $format = 'Y-m-d')
    {
        $start = new \DateTime($data['start']);
        $end = new \DateTime($data['end']);

        $interval = new \DateInterval('P'.$interval);
        $period = new \DatePeriod($start, $interval, $end);

        $ranges = iterator_to_array($period);

        $items = [];

        $item = [
            'id' => $data['id'],
            'title' => $data['title'],
            'allday' => $data['allday'],
            'calendar' => $data['calendar'],
        ];

        if ($data['allday']) {
            foreach ($ranges as $date) {
                $item['date'] = $date->format($format);
                $item['_start'] = $start->format($format);
                $item['_end'] = $end->format($format);
                $items[] = $item;
            }
        } else {
            $_start = $start->format('H:i');
            $_end = $end->format('H:i');

            $count = count($ranges);

            foreach ($ranges as $i => $date) {
                $now = $date->format($format);

                $item['date'] = $now;

                if ($i == 0) {
                    $item['start'] = $_start;
                    $item['end'] = $count == 1 ? $_end : '23:59';
                } else {
                    $item['start'] = '00:00';
                    $item['end'] = $count -1 == $i ? $_end : '23:59';
                }
                $items[] = $item;
            }
        }
        return $items;
    }
}
