<?php

class Model_PluginMBKReservation extends Zend_Db_Table_Row_Abstract
{
    protected $_cache_user;

    public function getUser()
    {
        if (!isset($this->_cache_user)) {
            $this->_cache_user = Model_DbTable_Users::getUserById($this->user_id);
        }

        return $this->_cache_user;
    }
}

