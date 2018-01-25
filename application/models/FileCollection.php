<?php

class Model_FileCollection extends Zend_Db_Table_Row_Abstract
{
    public function getFiles($order = 'title ASC')
    {
        $resultSet = Model_DbTable_Files::getFiles($this, $order);
        return $resultSet;
    }

    public function getPublishedDate()
    {
        return date('j/n Y', $this->created);
    }

    public function getFilename()
    {
        $filename = APPLICATION_PATH . '/../public/' . $this->src;
        return $filename;
    }

    public function getBasename()
    {
        $ext = ($this->file_extension ? $this->file_extension : '');
        $basename = $this->title . '.' . $ext;
        return $basename;
    }

    public function addFile($filename, $title = null)
    {
        $file = Model_DbTable_Files::addFile($this, $filename, $title);
        return $file;
    }

    public function removeFile(Model_File $file)
    {
        $filename = APPLICATION_PATH . '/../public/' . $file->src;
        if (is_file($filename)) @unlink($filename);
        $file->delete();
        return true;
    }

    protected function _delete()
    {
        $files = $this->getFiles();
        foreach ($files as $file) $file->delete();
    }
}

