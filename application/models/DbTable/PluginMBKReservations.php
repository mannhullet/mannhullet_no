<?php

class Model_DbTable_PluginMBKReservations extends Zend_Db_Table_Abstract
{
    protected $_name = 'PluginMBKReservation';
    protected $_primary = array('certificate_number', 'user_id');
    protected $_rowClass = 'Model_PluginMBKReservation';

    public static function createCertificate(Model_User $user)
    {
        try {
            $certificate = self::getCertificateByUserId($user->id);
            return false;
        }catch(Exception $e) {}

        $reservationTable = new self();
        $certificate = $reservationTable->createRow();
        $certificate->user_id = $user->id;
        $certificate->save();

        return $certificate;
    }

    public static function deleteCertificate($cid)
    {
        $certificate = self::getCertificate($cid);
        $certificate->delete();
        return true;
    }

    public static function getCertificates()
    {
        $reservationTable = new self();
        $result = $reservationTable->fetchAll( $reservationTable->select()->order('certificate_number ASC') );
        return $result;
    }

    public static function getCertificate($cid)
    {
        $reservationTable = new self();
        $row = $reservationTable->fetchRow( $reservationTable->getAdapter()->quoteInto('certificate_number = ?', $cid) );
        if (!$row) throw new Exception('No such certificate number');
        return $row;
    }

    public static function getCertificateByUserId($uid)
    {
        $reservationTable = new self();
        $row = $reservationTable->fetchRow( $reservationTable->getAdapter()->quoteInto('user_id = ?', $uid) );
        if (!$row) throw new Exception('No such certificate');
        return $row;
    }

    public static function verifyCertificateAndEmail($certificate_number, $email)
    {
        try {
            $certificate = self::getCertificate($certificate_number);
            $user = $certificate->getUser();
            if ($user->email == $email) return true;
        }catch(Exception $e) {}
        return false;
    }

    public static function removeUserCertificate(Model_User $user)
    {
        $certificate = self::getCertificateByUserId($user->id);
        return self::deleteCertificate($certificate->certificate_number);
    }
}

