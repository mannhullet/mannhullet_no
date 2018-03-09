<?php

class Model_DbTable_Users extends Zend_Db_Table_Abstract
{
    protected $_name = 'User';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_User';

    public static function createUser($email, $password, $name)
    {
        $users = new self;
        $user  = $users->fetchRow( $users->getAdapter()->quoteInto('email = ?', $email) );
        if (!$user) throw new Exception('There is no registration right for this email');

        // Generate a verification code
        $verificationCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 20);

        // Set the password using the hashing feature implemented in the row-class
        $user->email = $email;
        $user->name  = $name;
        $user->created = time();
        $user->setPassword($password);
        $user->verificationCode = $verificationCode;
        $user->save();

        // Render new user email template
        $html = new Zend_View();
        $html->setScriptPath(APPLICATION_PATH . '/views/email/');
        $html->assign('verificationCode', $verificationCode);

        $mail = new Zend_Mail('utf-8');
        $mail->setMessageId();
        $mail->addTo($email, $name);
        //$mail->addBcc('kristoffer.a.iversen@gmail.com');
        $mail->setSubject('Ditt Mannhullet medlemskap');
        $mail->setFrom('no-reply@mannhullet.no','Mannhullet.no');

        // Email content, text and HTML alternatives (less spam-like, prevents SpamAssassin from blocking).
        $mail->setBodyText('For å fullføre opprettelsen av medlemskapet ditt må du bruke lenken: http://' . $_SERVER['HTTP_HOST'] . '/registrer/act/verifyemail/code/' . $verificationCode);
        $mail->setBodyHtml($html->render('verifyemail.phtml'));

        $mail->send();

        return $user;
    }

    public static function createUserRegistrationRight($email)
    {
        $users = new self;
        $user  = $users->fetchRow( $users->getAdapter()->quoteInto('email = ?', $email) );
        if ($user) return $user;

        $user = $users->createRow();
        $user->email = $email;
        $user->save();
        return $user;
    }

    public static function sendInvite(Model_User $user)
    {
        if (!$user) throw new Exception('Requires a valid user object');

        $username = reset(explode('@', $user->email));

        // Render new user email template
        $html = new Zend_View();
        $html->setScriptPath(APPLICATION_PATH . '/views/email/');
        $html->assign('username', $username);

        // Send the new password to the user in an email
        $mail = new Zend_Mail('utf-8');
        $mail->setMessageId();
        $mail->addTo($user->email);
        //$mail->addBcc('kristoffer.a.iversen@gmail.com');
        //$mail->addTo('kristoffer@elv.technology');
        $mail->setSubject('Din brukerkonto på Mannhullets nettsider');
        $mail->setFrom('no-reply@mannhullet.no','Mannhullet.no');

        // Email content, text and HTML alternatives (less spam-like, prevents SpamAssassin from blocking).
        $mail->setBodyText('Mannhullet, marins linjeforening, er nå klar for deg. Bli med for å få tilgang til arrangementer, bildegallerier, dokumenter, Marina publikasjoner og mer. http://' . $_SERVER['HTTP_HOST'] . '/registrer/usr/' . $username);
        $mail->setBodyHtml($html->render('inviteuser.phtml'));

        $mail->send();
    }

    public static function loginUser($email, $password, $rememberMe)
    {
        $user = self::getUser($email);

        if ($user->verificationCode != null)  throw new Exception('Email has not been verified yet');
        if ($user->password == null)          throw new Exception('Invalid password');
        if (!$user->checkPassword($password)) throw new Exception('Invalid password');

        // Make a record of the login in the database
        Model_DbTable_Logins::registerLogin($user);

        // Set the cookie expiration date to 2 weeks in the future
        if ($rememberMe) { //Zend_Session::rememberMe(60*60*24*14);
            Zend_Session::rememberMe();
            $saveHandler = Zend_Session::getSaveHandler();
            $saveHandler->setLifetime(60*60*24*14)
                        ->setOverrideLifetime(true);
        }

        // Cache the user-object for future requests
        $userSession = new Zend_Session_Namespace('user');
        $userSession->user = $user;
        $userSession->isUserIdPersisted = false;

        return $user;
    }

    public static function logoutUser(Model_User $user = null)
    {
        if ($user) {
            Model_DbTable_Sessions::removeAllUserSessions($user);
            return true;
        }

        // Delete the session from the database and the cookie from the users browser
        $userSession = new Zend_Session_Namespace('user'); // Ensures session_start() has been called before the following destroy() - required!
        Zend_Session::destroy(true);
        return true;
    }

    public static function getUser($email)
    {
        $users = new self;
        $user = $users->fetchRow($users->getAdapter()->quoteInto('`email` = ?', $email));
        if (!$user) throw new Exception('No such user');
        return $user;
    }

    public static function getUserById($uid)
    {
        $users = new self;
        $user = $users->fetchRow($users->getAdapter()->quoteInto('id = ?', $uid));
        if (!$user) throw new Exception('No such user');
        return $user;
    }

    public static function getAllUsers($regRights = false)
    {
        $users = new self;
        $resultSet = $users->fetchAll( $users->select()->where(($regRights ? 'password IS NULL' : 'password IS NOT NULL')) );
        return $resultSet;
    }

    public static function getSiteAdmins()
    {
        $users = new self;
        $resultSet = $users->fetchAll( $users->select()->where('admin = ?', 1)->order('name ASC') );
        return $resultSet;
    }

    public static function verifyEmail($code)
    {
        $users = new self();
        $user = $users->fetchRow( $users->getAdapter()->quoteInto('verificationCode = ?', $code) );
        if (!$user) return false;
        $user->verificationCode = null;
        $user->save();
        return true;
    }
}
