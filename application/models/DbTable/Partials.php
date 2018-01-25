<?php

class Model_DbTable_Partials extends Zend_Db_Table_Abstract
{
    protected $_name = 'Partial';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_Partial';

    public static function getPartialById($pid)
    {
        $partials = new self();
        $partial = $partials->fetchRow( $partials->getAdapter()->quoteInto('id = ?', $pid) );
        if (!$partial) throw new Exception('No such partial');
        return $partial;
    }

    public static function getHtml($uri)
    {
        $partials = new self();
        $partial = $partials->fetchRow( $partials->select()->where('uri = ?', $uri) );

        if (!$partial) {
            $partials->insert(array(
                'uri'   => $uri,
            ));
            return null;
        }

        return $partial->body;
    }

    public static function setHtml($uri, $body)
    {
        $partials = new self();
        $partial = $partials->fetchRow( $partials->select()->where('uri = ?', $uri) );

        if (!$partial) {
            $partials->insert(array(
                'uri'       => $uri,
                'body'      => $body,
                'updated'   => time(),
            ));
            return $body;
        }

        $partial->body = $body;
        $partial->save();

        return $partial->body;
    }
}

