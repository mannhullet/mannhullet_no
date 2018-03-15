<?php

class Model_DbTable_FileCollections extends Zend_Db_Table_Abstract
{
    protected $_name = 'FileCollection';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_FileCollection';

    public static function getCollectionMembers($collection, $order = 'category DESC', $count = -1, $offset = -1)
    {
        $fileCollections = new self();
        $select = $fileCollections->select()->order($order)->where('collection = ?', $collection);
        if ($count >= 0 && $offset >= 0) $select->limit($count, $offset);
        $resultSet = $fileCollections->fetchAll($select);
        return $resultSet;
    }

    public static function getCollection($cid)
    {
        if (!is_numeric($cid)) throw new Exception('No such collection');

        $fileCollections = new self();
        $collection = $fileCollections->fetchRow( $fileCollections->getAdapter()->quoteInto('id = ?', $cid) );
        if (!$collection) throw new Exception('No such collection');

        return $collection;
    }

    public static function downloadCollectionFile($cid)
    {
        $collection = self::getCollection($cid);
        $filename = $collection->getFilename();
        $basename = $collection->getBasename();

        header('Content-Type: application/octet-stream');

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename='.$basename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        ob_end_flush();
        readfile($filename);
        exit;
    }

    public static function getAlbums()
    {
        $result = array();
        $albums = self::getCollectionMembers('albums');
        foreach ($albums as $album) {
            if (!isset($result[$album->category])) $result[$album->category] = array();
            $result[$album->category][] = $album;
        }
        return $result;
    }

    public static function createAlbum($title, $year = false)
    {
        if (!$title || $title == '') throw new Exception('You must supply a title to create an album');

        if (!$year) $year = date('Y');

        $fileCollections = new self();
        $fileCollection = $fileCollections->createRow();

        $fileCollection->title = $title;
        $fileCollection->category = $year;
        $fileCollection->collection = 'albums';
        $fileCollection->created = time();
        $fileCollection->save();

        return $fileCollection;
    }

    public static function addMarina($title, $ext, $filename, $year = false)
    {
        if (!$title || $title == '' || !$ext || $ext == '' || !is_file($filename)) throw new Exception('Invalid parameters');

        $src = '/uploads' . implode('/uploads', array_slice(explode('/uploads', $filename), 1));

        $fileCollections = new self();
        $fileCollection = $fileCollections->createRow();

        $fileCollection->title = $title;
        $fileCollection->category = ($year ? $year : (date('M') >= 7 ? date('Y') . '/' . date('Y')+1 : date('Y')-1 . '/' . date('Y')));
        $fileCollection->collection = 'marinas';
        $fileCollection->created = time();
        $fileCollection->src = $src;
        $fileCollection->file_extension = $ext;
        $fileCollection->save();

        return $fileCollection;
    }

    public static function getMarinas()
    {
        $result = array();
        $marinas = self::getCollectionMembers('marinas');
        foreach ($marinas as $marina) {
            if (!isset($result[$marina->category])) $result[$marina->category] = array();
            $result[$marina->category][] = $marina;
        }
        return $result;
    }

    public static function addDocument($title, $ext, $filename, $year = false)
    {
        if (!$title || $title == '' || !$ext || $ext == '' || !is_file($filename)) throw new Exception('Invalid parameters');

        $src = '/uploads' . implode('/uploads', array_slice(explode('/uploads', $filename), 1));

        $fileCollections = new self();
        $fileCollection = $fileCollections->createRow();

        $fileCollection->title = $title;
        $fileCollection->category = ($year ? $year : date('Y'));
        $fileCollection->collection = 'documents';
        $fileCollection->created = time();
        $fileCollection->src = $src;
        $fileCollection->file_extension = $ext;
        $fileCollection->save();

        return $fileCollection;
    }

    public static function getDocs()
    {
        $result = array();
        $docs = self::getCollectionMembers('documents');
        foreach ($docs as $doc) {
            if (!isset($result[$doc->category])) $result[$doc->category] = array();
            $result[$doc->category][] = $doc;
        }
        return $result;
    }



}
