<?php

class Model_DbTable_Files extends Zend_Db_Table_Abstract
{
    protected $_name = 'File';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_File';

    public static function getFiles(Model_FileCollection $collection, $order = 'title ASC')
    {
        $files = new self();
        $select = $files->select()->order($order)->where('collection_id = ?', $collection->id);
        $resultSet = $files->fetchAll($select);
        return $resultSet;
    }

    public static function getFile(Model_FileCollection $collection, $src)
    {
        $files = new self();
        $file = $files->fetchRow( $files->select()->where('collection_id = ?', $collection->id)->where('src = ?', $src) );
        return $file;
    }

    public static function getFileById($fid)
    {
        $files = new self();
        $file = $files->fetchRow( $files->select()->where('id = ?', $fid) );
        return $file;
    }

    public static function addFile(Model_FileCollection $collection, $filename, $title = null)
    {
        if (!is_file($filename)) throw new Exception('No file supplied');

        $src = '/uploads' . implode('/uploads', array_slice(explode('/uploads', $filename), 1));

        $files = new self();
        $file = $files->createRow();

        $file->collection_id    = $collection->id;
        $file->src              = $src;
        $file->title            = $title;
        $file->created          = time();
        $file->save();

        return $file;
    }

    public static function downloadFile($fid)
    {
        $file = self::getFileById($fid);
        $filename = APPLICATION_PATH . '/../public/' . $file->src;
        $ext = pathinfo($file->src, PATHINFO_EXTENSION);
        $ext = $ext ? $ext : '';
        $basename = $file->title . '.' . $ext;

        // var_dump($file->src);
        // echo '<pre>';
        // echo 'Filename: ' . $filename . '<br/>';
        // echo 'Basename: ' . $basename;
        // echo '</pre>';
        // return;

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
}
