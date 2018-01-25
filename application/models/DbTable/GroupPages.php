<?php

class Model_DbTable_GroupPages extends Zend_Db_Table_Abstract
{
    protected $_name = 'GroupPage';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_GroupPage';

    public static function getGroupPages($gid)
    {
        $groupPages = new self();
        $resultSet = $groupPages->fetchAll( $groupPages->select()->order('order ASC')->where( $groupPages->getAdapter()->quoteInto('group_id = ?', $gid) ) );
        return $resultSet;
    }

    public static function getGroupPage($pid)
    {
        if (!is_numeric($pid)) throw new Exception('No such group page');

        $groupPages = new self();
        $groupPage = $groupPages->fetchRow( $groupPages->getAdapter()->quoteInto('id = ?', $pid) );
        if (!$groupPage) throw new Exception('No such group page');

        return $groupPage;
    }

    public static function createGroupPage(Model_Group $group, $title, $external = null)
    {
        $groupPages = new self();
        $page = $groupPages->createRow();
        $page->group_id     = $group->id;
        $page->title        = $title;
        $page->created      = time();
        $page->external     = $external;
        $page->save();
        return $page;
    }
}

