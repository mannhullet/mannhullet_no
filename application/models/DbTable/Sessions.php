<?php

class Model_DbTable_Sessions extends Zend_Db_Table_Abstract
{
    protected $_name = 'Session';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_Session';

    public static function persistUserId()
    {
        $userSession = new Zend_Session_Namespace('user');
        if (isset($userSession->user) && !$userSession->isUserIdPersisted) {
            $sessionId = Zend_Session::getId();
            self::storeUserId($sessionId, $userSession->user);
            $userSession->isUserIdPersisted = true;
        }
    }

    public static function storeUserId($sessionId, Model_User $user)
    {
        $sessions = new self();

        $session = $sessions->fetchRow( $sessions->getAdapter()->quoteInto('id = ?', $sessionId) );
        if (!$session) throw new Exception('No such session ID');

        $session->user_id = $user->id;
        $session->save();

        return true;
    }

    public static function removeAllUserSessions(Model_User $user)
    {
        if (!$user) throw new Exception('No user supplied');

        Zend_Session::writeClose(true);
        $sessions = new self();
        $sessions->delete( $sessions->getAdapter()->quoteInto('user_id = ?', $user->id) );

        return true;
    }
}

