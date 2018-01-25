<?php

class Model_Event extends Zend_Db_Table_Row_Abstract
{
    protected $_cache_group;

    public function getDateTime()
    {
        $format = 'H:i';
        $start_time = $this->start_time;
        if (date('Ymd') !== date('Ymd', $start_time)) $format .= ' j/n';
        if (date('Y') !== date('Y', $start_time)) $format .= '/y';

        $format2 = 'H:i';
        $end_time  = $this->end_time;
        if (date('Ymd') !== date('Ymd', $end_time)) $format2 .= ' j/n';
        if (date('Y') !== date('Y', $end_time)) $format2 .= '/y';

        $format3 = 'H:i';
        $signup_open_time  = $this->signup_open_time;
        if (date('Ymd') !== date('Ymd', $signup_open_time)) $format3 .= ' j/n';
        if (date('Y') !== date('Y', $signup_open_time)) $format3 .= '/y';

        $format4 = 'H:i';
        $signup_close_time  = ($this->signup_close_time == null ? $this->start_time : $this->signup_close_time);
        if (date('Ymd') !== date('Ymd', $signup_close_time)) $format4 .= ' j/n';
        if (date('Y') !== date('Y', $signup_close_time)) $format4 .= '/y';

        return "<p>Starter: <span class='pull-right'>" . date($format, $this->start_time) . "</span></p><p>Slutter: <span class='pull-right'>" . date($format2, $this->end_time) . "</span></p><p>Påmelding åpner: <span class='pull-right'>" . date($format3, $this->signup_open_time) . "</span></p><p>Påmelding stenger: <span class='pull-right'>" . date($format4, $signup_close_time) . "</span></p>";
    }

    public function getDateTimeSmall()
    {
        $format = 'H:i';
        $start_time = $this->start_time;
        if (date('Ymd') !== date('Ymd', $start_time)) $format .= ' j/n';
        if (date('Y') !== date('Y', $start_time)) $format .= '/y';

        $format2 = 'H:i';
        $end_time  = $this->end_time;
        if (date('Ymd') !== date('Ymd', $end_time)) $format2 .= ' j/n';
        if (date('Y') !== date('Y', $end_time)) $format2 .= '/y';

        $format3 = 'H:i';
        $signup_open_time  = $this->signup_open_time;
        if (date('Ymd') !== date('Ymd', $signup_open_time)) $format3 .= ' j/n';
        if (date('Y') !== date('Y', $signup_open_time)) $format3 .= '/y';

        $format4 = 'H:i';
        $signup_close_time  = ($this->signup_close_time == null ? $this->start_time : $this->signup_close_time);
        if (date('Ymd') !== date('Ymd', $signup_close_time)) $format4 .= ' j/n';
        if (date('Y') !== date('Y', $signup_close_time)) $format4 .= '/y';

        return "<small>Starter: " . date($format, $this->start_time) . ". Slutter: " . date($format2, $this->end_time) . ".<br />Påmelding åpner: " . date($format3, $this->signup_open_time) . ".<br />Påmelding stenger: " . date($format4, $signup_close_time) . ".</small>";
    }

    public function getOrgedBy()
    {
        if (!$this->isGroupEvent()) return false;
        return '<span style="color:' . ($this->getGroup()->color != null ? $this->getGroup()->color : '') . ';">' . $this->getGroup()->title . '</span>';
    }

    public function getOrgedByLink()
    {
        if (!$this->isGroupEvent()) return false;
        return '<a href="/gruppe/' . $this->getGroup()->id . '" style="color:' . ($this->getGroup()->color != null ? $this->getGroup()->color : '') . ';">' . $this->getGroup()->title . '</a>';
    }

    public function isGroupEvent()
    {
        return $this->group_id != null;
    }

    public function getGroup()
    {
        if (!$this->isGroupEvent()) return null;

        if (!isset($this->_cache_group)) {
            $this->_cache_group = Model_DbTable_Groups::getGroup($this->group_id);
        }

        return $this->_cache_group;
    }

    public function setDateTime($start_date, $start_time, $end_date, $end_time, $signup_date = false, $signup_time = false, $signup_close_date = false, $signup_close_time = false)
    {
        if (!$start_date || !$start_time || !$end_date || !$end_time) throw new Exception('Invalid parameteres, need a title and date/time to create an event');

        // $datePattern = '/[0-9]{0,2}\/[0-9]{0,2}\/[0-9]{0,4}/';
        // $timePattern = '/[0-9]{0,2}:[0-9]{0,2}/';

        $unix_start_time = strtotime($start_date . ' ' . $start_time);
        $unix_end_time = strtotime($end_date . ' ' . $end_time);

        if ($signup_date == false || $signup_time == false) $unix_signup_time = time();
        else $unix_signup_time = strtotime($signup_date . ' ' . $signup_time);

        if ($signup_close_date == false || $signup_close_time == false) $unix_signup_close_time = null;
        else $unix_signup_close_time = strtotime($signup_close_date . ' ' . $signup_close_time);

        $this->start_time        = $unix_start_time;
        $this->end_time          = $unix_end_time;
        $this->signup_open_time  = $unix_signup_time;
        $this->signup_close_time  = $unix_signup_close_time;
        $this->save();

        return true;
    }

    public function setParticipationMax($max_participants)
    {
        $now = $this->participants_current;
        if ($now > $max_participants) throw new Exception('You can\'t reduce the max participants limit bellow current number of attendants');

        $this->participants_max = $max_participants;
        $this->save();

        // Make sure to move the waiting list up if we increased the max participants limit, freeing up space.
        if ($this->participants_max > $this->participants_current) Model_DbTable_EventAttendees::moveUpWaitingList($this);

        return true;
    }

    public function getAttendees()
    {
        return Model_DbTable_EventAttendees::getEventAttendees($this);
    }

    public function getWaitingList()
    {
        return Model_DbTable_EventAttendees::getEventWaitingList($this);
    }

    public function addAttendee(Model_User $user)
    {
        return Model_DbTable_EventAttendees::addAttendee($this, $user);
    }

    public function removeAttendee(Model_User $user)
    {
        return Model_DbTable_EventAttendees::removeAttendee($this, $user);
    }

    public function isAttendee(Model_User $user)
    {
        return Model_DbTable_EventAttendees::isAttendee($this, $user);
    }

    public function isOnWaitingList(Model_User $user)
    {
        return Model_DbTable_EventAttendees::isOnWaitingList($this, $user);
    }

    public function isBeforeSignupHasStarted()
    {
        if ($this->signup_open_time > time()) return true;
        return false;
    }

    public function isSignupOpen()
    {
        # signup <= currentTime <= signupClose if signupClose != NULL else eventStart
        if ($this->signup_open_time <= time() && time() <= ($this->signup_close_time == null ? $this->start_time : $this->signup_close_time)) return true;
        return false;
    }

    // Clean up
    protected function _delete()
    {
        $attendees = Model_DbTable_EventAttendees::getEventAttendees($this);
        foreach ($attendees as $attendee) $attendee->delete();

        $waitingList = Model_DbTable_EventAttendees::getEventWaitingList($this);
        foreach ($waitingList as $attendee) $attendee->delete();
    }
}

