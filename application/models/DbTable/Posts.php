<?php

class Model_DbTable_Posts extends Zend_Db_Table_Abstract
{
    protected $_name = 'Post';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_Post';
}

