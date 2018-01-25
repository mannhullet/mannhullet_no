<?php

class Model_File extends Zend_Db_Table_Row_Abstract
{
    public function getPublishedDate()
    {
        return date('j/n Y', $this->created);
    }
}

