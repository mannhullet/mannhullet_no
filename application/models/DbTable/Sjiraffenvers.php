<?php

class Model_DbTable_Sjiraffenvers extends Zend_Db_Table_Abstract
{
  protected $_name = 'sjiraffenvers';

  public static function addVerse($text, $author = 'Ukjent', $comment = null) {
    $verses = new self();
    $verse = $verses->createRow();

    $verse->tekst = $text;
    $verse->forfatter = $author;
    $verse->kommentar = $comment;
    $verse->dato = date('d.m.y');
    $verse->save();

    return $verse;
  }

  public static function getVerses() {
    $verses = new self();
    $resultSet = $verses->fetchAll( $verses->select() );
    //$arr =  self::object_to_array($resultSet);
    //$result =json_encode($arr);

    return $resultSet;
  }



}
