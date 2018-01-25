<?php

class Model_DbTable_Events extends Zend_Db_Table_Abstract
{
    protected $_name = 'Event';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_Event';

    public static function getEventsByMonth($month = -1, $year = -1, $isLoggedIn = false)
    {
        $month = (int) $month;
        if ($month == -1) $month = date('n');

        $year = (int) $year;
        if ($year == -1) $year = date('Y');

        if ($month < 1 || $month > 12) throw new Exception('Invalid choice of month');

        $startTime = strtotime($year . '-' . ($month < 10 ? '0' . $month : $month) . '-01');
        $startTime = $startTime + 60*60*(date('I', $startTime) ? 2 : 1);
        $endTime = $startTime + 60*60*24*date('t', $startTime);

        $weekStartTime = $startTime - 60*60*24*(date('N', $startTime)-1);
        $weekEndTime = $endTime + 60*60*24*(8-(date('N', $endTime)));

        $events = new self();
        $resultset = $events->fetchAll(
            $events->select()
                   ->where('(end_time > ?', $weekStartTime)->where('end_time < ?)', $weekEndTime)           // Ends within month
                   ->orWhere('(start_time > ?', $weekStartTime)->where('start_time < ?)', $weekEndTime)     // Starts within month
                   ->orWhere('(start_time < ?', $weekStartTime)->where('end_time > ?)', $weekEndTime)       // Goes through entire month
        );

        $monthArr = array();
        for ($time = $weekStartTime; $time < $weekEndTime; $time = $time + 60*60*24*7) {
            $week = date('W', $time);
            $monthArr[$week] = array();
            for ($time2 = $time; $time2 < ($time+60*60*24*7); $time2 = $time2 + 60*60*24) {
                $monthArr[$week][] = array(
                    'dayOfMonth' => date('j', $time2),
                    'dayStartTime' => $time2 - 60*60*(date('I') ? 2 : 1), // Removing norwegian diff.
                    'isRightMonth' => ($time2 < $startTime || $time2 >= $endTime) ? false : true,
                    'events' => array(),
                );
            }
        }

        // Remove norwegian hours so that we can compare real numbers
        $startTime = strtotime(date('Y', $startTime) . '-' . ($month < 10 ? '0' . $month : $month) . '-01'); // Add one/two hours to get the start and end of the month in Norway.
        $endTime   = $startTime + (60*60*24*date('t', $startTime));

        foreach ($monthArr as $weekIndex => $week) {
            foreach ($week as $dayIndex => $dayArray) {

                $dayStart = $dayArray['dayStartTime'];
                $dayEnd   = $dayStart + 60*60*24;

                foreach ($resultset as $result) {
                    if ($result->hidden_from_public == true && !$isLoggedIn) continue;
                    if ($result->start_time <= $dayStart && $result->end_time >= $dayEnd) {
                        $monthArr[$weekIndex][$dayIndex]['events'][] = $result; //->toArray();
                    }else if ($result->start_time >= $dayStart && $result->start_time < $dayEnd) {
                        $monthArr[$weekIndex][$dayIndex]['events'][] = $result;
                    }else if ($result->end_time > $dayStart && $result->end_time <= $dayEnd) {
                        $monthArr[$weekIndex][$dayIndex]['events'][] = $result;
                    }
                }

            }
        }

        return $monthArr;
    }

    public static function getEvents($week = -1, $year = -1, $isLoggedIn = false)
    {
        $week = (int) $week;
        if ($week == -1) $week = date('W');

        $year = (int) $year;
        if ($year == -1) $year = date('Y');

        if ($week < 1 || $week > 52) throw new Exception('Invalid choice of week');

        $startTime = strtotime($year . 'W' . ($week < 10 ? '0' . $week : $week)) + 60*60*(date('I') ? 2 : 1); // Add one/two hours to get the start and end of the week in Norway.
        $endTime   = $startTime + (60*60*24*7);

        $events = new self();
        $resultset = $events->fetchAll(
            $events->select()
                   ->where('(end_time > ?', $startTime)->where('end_time < ?)', $endTime)           // Ends within week
                   ->orWhere('(start_time > ?', $startTime)->where('start_time < ?)', $endTime)     // Starts within week
                   ->orWhere('(start_time < ?', $startTime)->where('end_time > ?)', $endTime)       // Goes through entire week
        );

        // Remove norwegian hours so that we can compare real numbers
        $startTime = strtotime($year . 'W' . ($week < 10 ? '0' . $week : $week));
        $endTime   = $startTime + (60*60*24*7);

        $week = array_fill_keys(array('time', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag'), array_fill_keys(array(
            'allday',
            '00:00<br />07:00',
            '08:00<br />09:00',
            '10:00<br />11:00',
            '12:00<br />13:00',
            '14:00<br />15:00',
            '16:00<br />17:00',
            '18:00<br />19:00',
            '20:00<br />21:00',
            '22:00<br />23:00',
        ), array()));

        foreach ($resultset as $result) {

            if ($result->hidden_from_public == true && !$isLoggedIn) continue;

            $weekKeys = array_keys($week);
            $day = next($weekKeys);
            for ($startOfDay = $startTime, $endOfDay = $startTime+60*60*24; $endOfDay <= $endTime; $startOfDay+=60*60*24, $endOfDay+=60*60*24) { // Foreach day of week

                if ($result->start_time <= $startOfDay && $result->end_time >= $endOfDay) {
                    $week[$day]['allday'][] = $result;
                    $day = next($weekKeys);
                    continue;
                }

                $dayKeys = array_keys($week[$day]);
                $hour = next($dayKeys);
                for ($startOfDblHour = $startOfDay, $endOfDblHour = $startOfDay+60*60*8; $endOfDblHour <= $endOfDay; $endOfDblHour+=60*60*2, $startOfDblHour=$endOfDblHour-60*60*2) {

                    if ($result->start_time <= $startOfDblHour && $result->end_time >= $endOfDblHour) {
                        $week[$day][$hour][] = $result;
                    }else if ($result->start_time >= $startOfDblHour && $result->start_time < $endOfDblHour) {
                        $week[$day][$hour][] = $result;
                    }else if ($result->end_time > $startOfDblHour && $result->end_time <= $endOfDblHour) {
                        $week[$day][$hour][] = $result;
                    }

                    $hour = next($dayKeys);

                }

                $day = next($weekKeys);
            }

        }

        //print_r($week);die();

        // Transform week
        $week2 = array_fill_keys(array('allday', '00:00<br />07:00', '08:00<br />09:00', '10:00<br />11:00', '12:00<br />13:00', '14:00<br />15:00', '16:00<br />17:00', '18:00<br />19:00', '20:00<br />21:00', '22:00<br />23:00'), array_fill_keys(array('time', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag'), array()));
        foreach ($week as $dayKey => $day) {
            foreach ($day as $timeKey => $time) {
                $week2[$timeKey][$dayKey] = $week[$dayKey][$timeKey];
            }
        }

        return $week2;
    }

    public static function getEventsList($fromTime = false, $limit = 15, Model_Group $group = null, $isLoggedIn = false)
    {
        if ($fromTime === false) $fromTime = time();

        $events = new self();
        $select = $events->select()
                         ->order('start_time ASC')
                         ->where('(start_time > ?', $fromTime)
                         ->orWhere('end_time > ?)', $fromTime)
                         ->limit($limit, 0);
        if ($group != null) $select->where('group_id = ?', $group->id);
        if (!$isLoggedIn) $select->where('hidden_from_public = 0');
        $resultset = $events->fetchAll($select);
        return $resultset;
    }

    public static function getAllEvents(Model_Group $group, $isLoggedIn = false)
    {
        $events = new self();
        $select = $events->select()->where('group_id = ?', $group->id);
        if (!$isLoggedIn) $select->where('hidden_from_public = 0');
        $rowset = $events->fetchAll( $select );
        return $rowset;
    }

    public static function getEvent($aid)
    {
        if (!is_numeric($aid)) throw new \Exception('No such event');

        $events = new self();
        $event = $events->fetchRow( $events->getAdapter()->quoteInto('id = ?', $aid) );
        if (!$event) throw new Exception('No such event');

        return $event;
    }

    public static function createEvent(Model_User $author, $title, $location = '', $start_date, $start_time, $end_date, $end_time, $signup_date, $signup_time, $signup_close_date, $signup_close_time, $image = null, $max_participants = 0, $ingress = '', $body = '', $hiddenFromPublic = false, Model_Group $group = null)
    {
        if (!$start_date || !$start_time || !$end_date || !$end_time || !$title) throw new Exception('Invalid parameteres, need a title and date/time to create an event');

        $events = new self();
        $event = $events->createRow();
        $event->author              = $author->id;
        $event->created             = time();
        $event->title               = $title;
        $event->image               = $image;
        $event->ingress             = $ingress;
        $event->body                = $body;
        $event->participants_max    = $max_participants;
        $event->location            = $location;
        $event->group_id            = ($group ? $group->id : null);
        $event->hidden_from_public  = $hiddenFromPublic == true ? 1 : 0;
        // $event->save(); // Taken care of in "setDateTime" bellow.

        $event->setDateTime($start_date, $start_time, $end_date, $end_time, $signup_date, $signup_time, $signup_close_date, $signup_close_time);

        return $event;
    }
}
