<?php

class Model_DbTable_GroupAdmins extends Zend_Db_Table_Abstract
{
    protected $_name = 'GroupAdmin';
    protected $_primary = array('group_id', 'user_id');
    protected $_rowClass = 'Model_GroupAdmin';

    public static function getGroupAdmins(Model_Group $group)
    {
        $groupAdmins = new self();
        $admins = $groupAdmins->fetchAll( $groupAdmins->select()->where('group_id = ?', $group->id) );
        return $admins;
    }

    public static function isGroupAdmin(Model_User $user, Model_Group $group = null)
    {
        $groupAdmins = new self();

        if ($group) {
            $groupAdmin = $groupAdmins->fetchRow( $groupAdmins->select()->where('group_id = ?', $group->id)->where('user_id = ?', $user->id) );
            return $groupAdmin == true;
        }else{
            $groups = $groupAdmins->fetchAll( $groupAdmins->select()->where('user_id = ?', $user->id) );
            return $groups;
        }
    }

    public static function addAdmin(Model_Group $group, Model_User $user)
    {
        $groupAdmins = new self();
        $groupAdmin = $groupAdmins->fetchRow( $groupAdmins->select()->where('group_id = ?', $group->id)->where('user_id = ?', $user->id) );
        if (!$groupAdmin) $groupAdmins->insert(array(
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ));
        return true;
    }

    public static function removeAdmin(Model_Group $group, Model_User $user)
    {
        $groupAdmins = new self();
        $groupAdmin = $groupAdmins->fetchRow( $groupAdmins->select()->where('group_id = ?', $group->id)->where('user_id = ?', $user->id) );
        if ($groupAdmin) $groupAdmin->delete();
        return true;
    }

    public static function removeAdminFromAnyGroup(Model_User $user)
    {
        $groupAdmins = new self();
        $groupAdmins->delete( $groupAdmins->getAdapter()->quoteInto('user_id = ?', $user->id) );
        return true;
    }
}

