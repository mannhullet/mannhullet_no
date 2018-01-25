<?php

class Model_DbTable_Groups extends Zend_Db_Table_Abstract
{
    protected $_name = 'Group';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_Group';

    public static function getGroups()
    {
        $groups = new self();
        $resultSet = $groups->fetchAll( $groups->select()->order('order ASC') );
        return $resultSet;
    }

    public static function getGroup($gid)
    {
        if (!is_numeric($gid)) throw new Exception('No such group');

        $groups = new self();
        $group = $groups->fetchRow( $groups->getAdapter()->quoteInto('id = ?', $gid) );
        if (!$group) throw new Exception('No such group');

        return $group;
    }

    public static function createGroup($title, $external = null, $color = null, $image = null, $body = null)
    {
        if (!$title) throw new Exception('We need a valid title');

        $groups = new self();
        $group = $groups->createRow();

        $group->title       = $title;
        $group->external    = $external;
        $group->color       = $color;
        $group->image       = $image;
        $group->body        = $body;
        $group->save();

        return $group;
    }
}

