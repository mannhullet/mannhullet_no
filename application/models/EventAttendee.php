<?php

class Model_EventAttendee extends Zend_Db_Table_Row_Abstract
{
    protected $_cache_event;
    protected $_cache_user;
    protected $_cache_spotOnWaitingList;

    public function getEvent()
    {
        if (!isset($this->_cache_event)) {
            $this->_cache_event = Model_DbTable_Events::getEvent($this->event_id);
        }

        return $this->_cache_event;
    }

    public function getUser()
    {
        if (!isset($this->_cache_user)) {
            $this->_cache_user = Model_DbTable_Users::getUserById($this->user_id);
        }

        return $this->_cache_user;
    }

    public function isOnWaitingList()
    {
        return $this->waiting_list == 1;
    }

    public function getSpotOnWaitingList()
    {
        if (!isset($this->_cache_spotOnWaitingList)) {
            $this->_cache_spotOnWaitingList = Model_DbTable_EventAttendees::getAttendeeSpotOnWaitingList($this);
        }

        return $this->_cache_spotOnWaitingList;
    }
}
