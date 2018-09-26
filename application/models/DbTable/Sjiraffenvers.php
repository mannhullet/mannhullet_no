<?php

class Model_DbTable_Sjiraffenvers extends Zend_Db_Table_Abstract
{
  protected $_name = 'sjiraffenvers';

  public static function addVerse($text, $author = 'Ukjent', $comment = null) {
    $verses = new self();
    $verse = $verses->createRow();

    $verse->text = $text;
    $verse->author = $author;
    $verse->comment = $comment;
    $verse->save();

    return $verse;
  }

  public static function object_to_array($data)
  {
    if(is_array($data) || is_object($data))
    {
        $result = array();

        foreach($data as $key => $value) {
            $result[$key] = self::object_to_array($value);
        }

        return $result;
    }

    return $data;
  }

  public static function getVerses() {
    $verses = new self();
    $resultSet = $verses->fetchAll( $verses->select() );
    $arr =  self::object_to_array($resultSet);
    $result =json_encode($arr);

    return $result;
  }


  public static function getVerse () {



    if ($availableindexes == 'undefined'):
      $availableindexes = array();
    endif;
    if ($availableindexes.length == 0):
      for ($i=0; $i < numberOfVerses; $i++) {
        $availableindexes[i] = i;
      }
    endif;

    $index = mt_rand(1, count($availableindexes));

    $verses = new Self();
    $verse = $verses->fetchRow( $news->getAdapter()->quoteIntro('id = ?', $index) );

    return $verse;
  }


}
