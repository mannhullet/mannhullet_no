<?php

class Model_DbTable_Sponsors extends Zend_Db_Table_Abstract
{
    protected $_name = 'Sponsor';
    protected $_primary = 'id';
    /*
    protected $_rowClass = 'Model_Event';
    */

    public static function getSponsor($sid)
    {
        $sponsors = new self();
        $sponsor = $sponsors->fetchRow( $sponsors->getAdapter()->quoteInto('id = ?', $sid) );
        if (!$sponsor) throw new Exception('No sponsor with that id (' . $sid . ')');
        return $sponsor;
    }

    public static function getAllSponsors()
    {
        $sponsors = new self();
        $rowset = $sponsors->fetchAll();
        return $rowset;
    }

    public static function getSponsorRandom()
    {
        $rowset = self::getAllSponsors();
        if (count($rowset) == 0) return false;
        return $rowset[rand(0, count($rowset)-1)];
    }

    public static function removeSponsor($sid)
    {
        $sponsor = self::getSponsor($sid);
        $sponsor->delete();
        return true;
    }

    public static function createSponsor($title, $image, $href = null)
    {
        if (!$title || $title == '' || !$image || $image == '') throw new Exception('Title and Image required for all sponsors');
        $sponsors = new self();
        $sponsor = $sponsors->createRow();
        $sponsor->title = $title;
        $sponsor->image = $image;
        $sponsor->href  = $href;
        $sponsor->save();
        return $sponsor;
    }
}

