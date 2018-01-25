<?php

class Model_GroupPage extends Zend_Db_Table_Row_Abstract
{
    public function isExternal()
    {
        return ($this->external != null);
    }

    public function getFiltered()
    {
        return Model_Plugins::filter($this->body);
    }
}

