<?php

class Model_DbTable_Carousel extends Zend_Db_Table_Abstract
{
    protected $_name = 'Carousel';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_Slide';

    public static function getSlides()
    {
        $slides = new self();
        $rowSet = $slides->fetchAll( $slides->select()->order('order ASC') );
        return $rowSet;
    }

    public static function getSlide($sid)
    {
        $slides = new self();
        $row = $slides->fetchRow( $slides->select()->where('id = ?', $sid) );
        if (!$row) throw new Exception('No slide with that id');
        return $row;
    }

    public static function createSlide($title, $image, $body, $index = 999)
    {
        $slides = new self();
        $newSlide = $slides->createRow();
        $newSlide->title = $title;
        $newSlide->image = $image;
        $newSlide->body  = $body;

        $i = 0;
        $rowSet = self::getSlides();
        foreach ($rowSet as $row) {
            if ($i == $index) {
                $newSlide->order = $i;
                $newSlide->save();
                $i++;
            }
            $row->order = $i;
            $row->save();
            $i++;
        }
        if ($newSlide->id == null) $newSlide->save();
    }

    public static function moveSlide($sid, $index)
    {
        $i = 0;
        $slideToMove = null;
        $rowSet = self::getSlides();

        foreach ($rowSet as $row) if ($row->id == $sid) {
            $slideToMove = $row;
            break;
        }

        if ($slideToMove == null) throw new Exception('No slide with that id');

        foreach ($rowSet as $row) {
            if ($slideToMove->id == $row->id) continue;
            if ($i == $index) {
                $slideToMove->order = $i;
                $slideToMove->save();
                $i++;
            }
            $row->order = $i;
            $row->save();
            $i++;
        }
    }

    public static function removeSlide($sid)
    {
        $slides = new self();
        $slides->delete( $slides->getAdapter()->quoteInto('id = ?', $sid) );
    }
}

