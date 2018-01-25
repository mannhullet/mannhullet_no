<?php

class Model_GroupAdmin extends Zend_Db_Table_Row_Abstract
{
    public function getUser()
    {
        return Model_DbTable_Users::getUserById($this->user_id);
    }

    public function getGroup()
    {
        return Model_DbTable_Groups::getGroup($this->user_id);
    }
}

