<?php

class Model_News extends Zend_Db_Table_Row_Abstract
{
    protected $_cache_group;

    public function getDateTime($created = false)
    {
        $time = $created ? $this->created : ($this->updated == null ? $this->created : $this->updated);
        $month = str_replace(
            array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'),
            array('Januar', 'Februar', 'Mars', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Desember'),
            date('F', $time)
        );
        return date('d. ', $time) . $month . date(' Y H:i', $time);
    }

    public function isGroupArticle()
    {
        return $this->group_id != null;
    }

    public function getGroup()
    {
        if (!$this->isGroupArticle()) return null;

        if (!isset($this->_cache_group)) {
            $this->_cache_group = Model_DbTable_Groups::getGroup($this->group_id);
        }

        return $this->_cache_group;
    }
}

