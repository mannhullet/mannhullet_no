<?php

class Model_Group extends Zend_Db_Table_Row_Abstract
{
    protected $_cache_administrators;
    protected $_cache_pages;

    public function isExternal()
    {
        return ($this->external != null);
    }

    public function getAdmins()
    {
        if (!isset($this->_cache_administrators)) {
            $this->_cache_administrators = Model_DbTable_GroupAdmins::getGroupAdmins($this);
        }

        return $this->_cache_administrators;
    }

    public function isAdmin(Model_User $user)
    {
        return Model_DbTable_GroupAdmins::isGroupAdmin($user, $this);
    }

    public function getPages()
    {
        if (!isset($this->_cache_pages)) {
            $this->_cache_pages = Model_DbTable_GroupPages::getGroupPages($this->id);
        }

        return $this->_cache_pages;
    }

    // Clean up
    protected function _delete()
    {
        $news = Model_DbTable_News::getAllArticles($this);
        foreach ($news as $article) $article->delete();

        $events = Model_DbTable_Events::getAllEvents($this);
        foreach ($events as $event) $event->delete();

        $admins = $this->getAdmins();
        foreach ($admins as $admin) $admin->delete();

        $pages  = $this->getPages();
        foreach ($pages as $page) $page->delete();
    }
}

