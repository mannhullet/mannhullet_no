<?php

class Model_DbTable_AboutPersons extends Zend_Db_Table_Abstract
{
    protected $_name = 'AboutPerson';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_AboutPerson';

    public static function getPersons($category, $order = null)
    {
        if ($order == null) {
            $order = array('sort ASC', 'name ASC');
        }
        $table = new self();
        $rowset = $table->fetchAll( $table->select()->order($order)->where('category = ?', $category) );
        return $rowset;
    }

    public static function getPerson($pid)
    {
        $table = new self();
        $person = $table->fetchRow( $table->select()->where('id = ?', $pid) );
        if (!$person) throw new Exception('No person with that id: ' . $pid);
        return $person;
    }

    public static function addPerson($category, $image, $name, $title = null, $contact_info = null, $popover = null)
    {
        $table = new self();
        $person = $table->createRow();

        $person->category = $category;
        $person->image = $image;
        $person->name = $name;
        $person->title = $title;
        $person->contact_info = $contact_info;
        $person->popover = $popover;
        $person->save();

        return $person;
    }

    public static function movePerson($pid, $index)
    {
        $person = self::getPerson($pid);

        $i = 0;
        $personToMove = null;
        $rowSet = self::getPersons($person->category);

        foreach ($rowSet as $row) if ($row->id == $pid) {
            $personToMove = $row;
            break;
        }

        if ($personToMove == null) throw new Exception('No person with that id');

        foreach ($rowSet as $row) {
            if ($personToMove->id == $row->id) continue;
            if ($i == $index) {
                $personToMove->sort = $i;
                $personToMove->save();
                $i++;
            }
            $row->sort = $i;
            $row->save();
            $i++;
        }
    }

    public static function removePerson($pid)
    {
        $person = self::getPerson($pid);
        $person->delete();
        return true;
    }
}

