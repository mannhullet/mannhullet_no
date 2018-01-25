<?php

// Password Hashing algorithm for secure password storage (BCrypt)
require_once('library/phpass-0.3/PasswordHash.php');

class Model_User extends Zend_Db_Table_Row_Abstract
{
    protected $_cache_isGroupAdminFor;

    public function setPassword($password)
    {
        $phpass = new PasswordHash(8, false);
        $this->password = $phpass->HashPassword($password);
        $this->save();
    }

    public function checkPassword($password)
    {
        $phpass = new PasswordHash(8, false);
        if ($phpass->CheckPassword($password, $this->password)) {
            return true;
        }else{
            return false;
        }
    }

    public function getGroupsImAdminFor()
    {
        if (!isset($this->_cache_isGroupAdminFor)) {
            $this->_cache_isGroupAdminFor = Model_DbTable_GroupAdmins::isGroupAdmin($this);
        }
        return $this->_cache_isGroupAdminFor;
    }

    public function resetPassword()
    {
        // Not even close to cryptographically secure, but okay for this purpose.
        $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ#%/\()"), 0, 10);

        // Reset password to new temporary password
        $this->setPassword($password);

        // Render reset password email template
        $html = new Zend_View();
        $html->setScriptPath(APPLICATION_PATH . '/views/email/');
        $html->assign('email',    $this->email);
        $html->assign('password', $password);

        // Send the new password to the user in an email
        $mail = new Zend_Mail('utf-8');
        $mail->setMessageId();
        $mail->addTo($this->email);
        //$mail->addBcc('kristoffer.a.iversen@gmail.com');
        //$mail->addTo('kristoffer.a.iversen@gmail.com');
        $mail->setSubject('Ditt passord for Mannhullet.no har blitt nullstilt');
        $mail->setFrom('no-reply@mannhullet.no','Mannhullet.no');

        // Email content, text and HTML alternatives (less spam-like, prevents SpamAssassin from blocking).
        $mail->setBodyText('Logg deg på med det nye passordet, og om ønsket kan du endre det på «Kontoinnstillinger» under brukermenyen. Nytt passord: ' . $password . 'http://' . $_SERVER['HTTP_HOST'] . '/');
        $mail->setBodyHtml($html->render('passwordreset.phtml'));

        $mail->send();

        return true;
    }

    public function reInitiate()
    {
        $table = new Model_DbTable_Users();
        $this->setTable($table);
        return $this;
    }

    // Clean up
    protected function _delete()
    {
        Model_DbTable_EventAttendees::removeAttendeeFromAllEvents($this);
        Model_DbTable_GroupAdmins::removeAdminFromAnyGroup($this);
        Model_DbTable_Logins::removeUserRecords($this);
        Model_DbTable_PluginMBKReservations::removeUserCertificate($this);
    }
}

