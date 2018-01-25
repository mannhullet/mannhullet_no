<?php

class Model_DbTable_News extends Zend_Db_Table_Abstract
{
    protected $_name = 'News';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_News';

    public static function getNews($page = 1, $count = 5, Model_Group $group = null)
    {
        $news = new self();
        if ($group == null) {
            return $news->fetchAll( $news->select()->order('created DESC')->where('group_id IS NULL')->orWhere('on_front_feed = 1')->limitPage($page, $count) );
        }else{
            return $news->fetchAll( $news->select()->order('created DESC')->where('group_id = ?', $group->id)->limitPage($page, $count) );
        }
    }

    public static function getPageCount($count = 5, Model_Group $group = null)
    {
        $news = new self();
        if ($group == null) {
            $articles = $news->fetchRow( $news->select()->from($news, 'count(*) as CNT')->where('group_id IS NULL')->orWhere('on_front_feed = 1') )->CNT;
        }else{
            $articles = $news->fetchRow( $news->select()->from($news, 'count(*) as CNT')->where('group_id = ?', $group->id) )->CNT;
        }

        return ceil($articles/$count);
    }

    public static function getAllArticles(Model_Group $group = null)
    {
        $news = new self();
        if ($group == null) {
            return $news->fetchAll( $news->select()->order('created DESC') );
        }else{
            return $news->fetchAll( $news->select()->order('created DESC')->where('group_id = ?', $group->id) );
        }
    }

    public static function getArticle($aid)
    {
        if (!is_numeric($aid)) throw new \Exception('No such article');

        $news = new self();
        $article = $news->fetchRow( $news->getAdapter()->quoteInto('id = ?', $aid) );
        if (!$article) throw new Exception('No such article');

        return $article;
    }

    public static function createArticle(Model_User $author, $title, $image = null, $ingress = '', $body = '', $onFrontFeed = true, Model_Group $group = null)
    {
        if (!$author || !isset($author->id)) throw new Exception('News articles require a valid author');
        if (!$title || $title == '') throw new Exception('News articles require titles');

        $news = new self();
        $article = $news->createRow();
        $article->group_id      = ($group ? $group->id : null);
        $article->on_front_feed = ($onFrontFeed ? 1 : 0);
        $article->author        = $author->id;
        $article->title         = $title;
        $article->created       = time();
        $article->image         = ($image == '' ? null : $image);
        $article->ingress       = $ingress;
        $article->body          = $body;
        $article->save();

        return $article;
    }
}

