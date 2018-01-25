<?php

class Model_DbTable_Logins extends Zend_Db_Table_Abstract
{
    protected $_name = 'Login';

    public static function registerLogin(Model_User $user)
    {
        $logins = new self;
        $logins->insert(array(
            'user_id'   => $user->id,
            'time'      => time(),
            'ip'        => ip2long($_SERVER['REMOTE_ADDR']),
        ));
    }

    public static function removeUserRecords(Model_User $user)
    {
        $logins = new self;
        $logins->delete( $logins->getAdapter()->quoteInto('user_id = ?', $user->id) );
        return true;
    }
}

