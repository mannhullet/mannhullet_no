<?php

class EndreController extends Zend_Controller_Action
{
    protected $user;

    public function init()
    {
        // Check if we're logged in or not
        $userSession = new Zend_Session_Namespace('user');
        $user = $this->user = $this->view->user = (isset($userSession->user) ? $userSession->user : false);

        if (!$user || (!$user->admin && count($user->getGroupsImAdminFor()) == 0)) throw new Exception('Not logged in or not the site administrator');

        // Used for the groups-menu
        $groups = $this->view->groups = Model_DbTable_Groups::getGroups();

        $this->view->headerNav = '/';

        Model_DbTable_Sessions::persistUserId();

        $this->view->socialIconFacebook = Model_DbTable_Partials::getHtml('/footer:socialIconFacebook'); //'https://www.facebook.com/groups/2217435004/';
        $this->view->socialIconInstagram = Model_DbTable_Partials::getHtml('/footer:socialIconInstagram'); //'https://twitter.com/search?q=mannhullet&src=typd';

        $sponsor = $this->view->sponsor = Model_DbTable_Sponsors::getSponsorRandom();
    }

    public function indexAction()
    {
        return $this->_redirect('/');
    }

    public function nyheterAction()
    {
        $this->view->tinymce = true;

        $aid = $this->_getParam('aid', false);
        if ($aid === false) {

            $gid = $this->_getParam('gid', false);
            $group = $this->view->group = ($gid !== false ? Model_DbTable_Groups::getGroup($gid) : false);

            if (!$this->user->admin && (!$group || !Model_DbTable_GroupAdmins::isGroupAdmin($this->user, $group))) throw new Exception('User not allowed to create news');

            if ($this->getRequest()->isPost()) {

                $title     = $this->_getParam('title', $article->title);
                $image     = $this->_getParam('image', null);
                $frontPage = $this->_getParam('frontPage', false);
                $ingress   = $this->_getParam('ingress', '');
                $html      = $this->_getParam('html', '');

                $article = Model_DbTable_News::createArticle($this->user, $title, $image, $ingress, $html, (!$group ? true : $frontPage == true), ($group ? $group : null));

                return $this->_redirect('/nyhet/' . $article->id);

            }


        }else{

            $article = $this->view->article = Model_DbTable_News::getArticle($aid);
            $group = $this->view->group = ($article->isGroupArticle() ? $article->getGroup() : false);

            if (!$this->user->admin && (!$group || !Model_DbTable_GroupAdmins::isGroupAdmin($this->user, $group))) throw new Exception('User not allowed to edit news');

            if ($this->_getParam('act', false) == 'delete') {
                $article->delete();
                if ($group) return $this->_redirect('/gruppe/' . $group->id);
                else return $this->_redirect('/');
            }

            if ($this->getRequest()->isPost()) {

                $title     = $this->_getParam('title', $article->title);
                $image     = $this->_getParam('image', null);
                $frontPage = (!$group ? 1 : $this->_getParam('frontPage', false));
                $ingress   = $this->_getParam('ingress', '');
                $html      = $this->_getParam('html', '');

                $article->title         = $title;
                $article->image         = ($image == '' ? null : $image);
                $article->on_front_feed = ($frontPage ? 1 : 0);
                $article->ingress       = $ingress;
                $article->body          = $html;
                $article->updated       = time();
                $article->save();
                return $this->_redirect('/nyhet/' . $article->id);

            }

        }
    }

    public function partialAction()
    {
        if (!$this->user->admin) throw new Exception('Only admins are allowed to edit partials');

        $pid = $this->_getParam('pid', -1);
        $partial = $this->view->partial = Model_DbTable_Partials::getPartialById($pid);

        $this->view->tinymce = true;

        if ($this->getRequest()->isPost()) {
            $html = $this->_getParam('html', false);
            if (!$html) return $this->_redirect('/endre/partial/pid/' . $pid);
            $partial->body = $html;
            $partial->updated = time();
            $partial->save();
            return $this->_redirect('/endre/partial/pid/' . $pid);
        }
    }

    public function arrangementerAction()
    {
        $this->view->tinymce = true;
        $this->view->opprettArrangementDatepicker = true;

        $aid = $this->_getParam('aid', false);
        if ($aid === false) {

            $gid = $this->_getParam('gid', false);
            $group = $this->view->group = ($gid !== false ? Model_DbTable_Groups::getGroup($gid) : false);

            if (!$this->user->admin && (!$group || !Model_DbTable_GroupAdmins::isGroupAdmin($this->user, $group))) throw new Exception('User not allowed to create events');

            if ($this->getRequest()->isPost()) {

                $title              = $this->_getParam('title', false);
                $location           = $this->_getParam('location', '');
                $start_date         = $this->_getParam('start_date', false);
                $end_date           = $this->_getParam('end_date', false);
                $start_time         = $this->_getParam('start_time', false);
                $end_time           = $this->_getParam('end_time', false);
                $signup_date        = $this->_getParam('signup_date', false);
                $signup_close_date  = $this->_getParam('signup_close_date', false);
                $signup_time        = $this->_getparam('signup_time', false);
                $signup_close_time  = $this->_getparam('signup_close_time', false);
                $image              = $this->_getParam('image', null);
                $max_participants   = $this->_getParam('max_participants', 0);
                $ingress            = $this->_getParam('ingress', '');
                $html               = $this->_getParam('html', '');
                $hiddenfrompub      = $this->_getParam('hiddenfrompub', false) == true;

                $event = Model_DbTable_Events::createEvent($this->user, $title, $location, $start_date, $start_time, $end_date, $end_time, $signup_date, $signup_time, $signup_close_date, $signup_close_time, $image, $max_participants, $ingress, $html, $hiddenfrompub, ($group ? $group : null));

                return $this->_redirect('/arrangement/' . $event->id);

            }

        }else{

            $event = $this->view->event = Model_DbTable_Events::getEvent($aid);
            $group = $this->view->group = ($event->isGroupEvent() ? $event->getGroup() : false);

            if (!$this->user->admin && (!$group || !Model_DbTable_GroupAdmins::isGroupAdmin($this->user, $group))) throw new Exception('User not allowed to edit events');

            if ($this->_getParam('act', false) == 'delete') {
                $event->delete();
                if ($group) return $this->_redirect('/gruppe/' . $group->id);
                else return $this->_redirect('/');
            }

            if ($this->getRequest()->isPost()) {

                $title     = $this->_getParam('title', $event->title);
                $location           = $this->_getParam('location', '');
                $start_date         = $this->_getParam('start_date', false);
                $end_date           = $this->_getParam('end_date', false);
                $start_time         = $this->_getParam('start_time', false);
                $end_time           = $this->_getParam('end_time', false);
                $signup_date        = $this->_getParam('signup_date', false);
                $signup_close_date  = $this->_getParam('signup_close_date', false);
                $signup_time        = $this->_getParam('signup_time', false);
                $signup_close_time  = $this->_getparam('signup_close_time', false);
                $image              = $this->_getParam('image', null);
                $max_participants   = $this->_getParam('max_participants', 0);
                $ingress            = $this->_getParam('ingress', '');
                $html               = $this->_getParam('html', '');
                $hiddenfrompub      = $this->_getParam('hiddenfrompub', false) == true;

                $event->title       = $title;
                $event->location    = $location;
                $event->image       = ($image == '' ? null : $image);
                $event->ingress     = $ingress;
                $event->body        = $html;
                $event->updated     = time();
                $event->hidden_from_public = $hiddenfrompub == true ? 1 : 0;
                $event->save();

                $event->setDateTime($start_date, $start_time, $end_date, $end_time, $signup_date, $signup_time, $signup_close_date, $signup_close_time);
                $event->setParticipationMax($max_participants);

                return $this->_redirect('/arrangement/' . $event->id);

            }

        }
    }

    public function gruppebeskrivelseAction()
    {
        $gid = $this->_getParam('gid', -1);
        $group = $this->view->group = Model_DbTable_Groups::getGroup($gid);
        if (!$this->user->admin && !Model_DbTable_GroupAdmins::isGroupAdmin($this->user, $group)) throw new Exception('Only siteadmin and specific group admins can change the group description');

        $this->view->tinymce = true;

        if ($this->getRequest()->isPost()) {
            $image = $this->_getParam('image', null);
            $html = $this->_getParam('html', false);
            $group->image = $image;
            $group->body = $html;
            $group->save();
            return $this->_redirect('/gruppe/' . $gid);
        }
    }

    public function gruppesiderAction()
    {
        $gid = $this->_getParam('gid', -1);
        $group = $this->view->group = Model_DbTable_Groups::getGroup($gid);
        if (!$this->user->admin && !Model_DbTable_GroupAdmins::isGroupAdmin($this->user, $group)) throw new Exception('Only siteadmin and specific group admins can change the group description');

        $pid = $this->_getParam('pid', -1);
        if ($pid == -1) {

            $this->view->gruppesiderSortableJS = true;

            $pages = $this->view->pages = Model_DbTable_GroupPages::getGroupPages($group->id);

            if ($this->getRequest()->isPost()) {
                $act = $this->_getParam('act', 'newPage');
                if ($act == 'reorder') {
                    $pageOrder = $this->_getParam('pageOrder', false);
                    if (!$pageOrder) die('Page order required');
                    $pageOrder = json_decode($pageOrder);
                    if (!is_array($pageOrder)) $pageOrder = array();
                    foreach ($pages as $page) {
                        $search = array_search($page->id, $pageOrder);
                        $page->order = ($search === false ? 999 : $search);
                        $page->save();
                    }
                    die('Success');
                }else{
                    $title = $this->_getParam('title', false);
                    if (!$title) throw new Exception('Title required to create subpage of group');
                    $isExternal = $this->_getParam('isExternal', false);
                    $external = $this->_getParam('external', false);
                    $page = Model_DbTable_GroupPages::createGroupPage($group, $title, ($isExternal ? $external : null));
                    return $this->_redirect('/endre/gruppesider/gid/'.$group->id.'/pid/'.$page->id);
                }
            }

        }else{

            $this->view->tinymce = true;

            $page = $this->view->page = Model_DbTable_GroupPages::getGroupPage($pid);
            if (!$page->group_id == $group->id) throw new Exception('Group and page-parent IDs don\'t match');

            $act = $this->_getParam('act', false);
            if ($act == 'delete') {
                $page->delete();
                return $this->_redirect('/endre/gruppesider/gid/'.$group->id);
            }

            if ($this->getRequest()->isPost()) {
                $title = $this->_getParam('title', $page->title);
                $page->title   = $title;
                if ($page->isExternal()) {
                    $external = $this->_getParam('external', $page->external);
                    $page->external = $external;
                }else{
                    $html  = $this->_getParam('html', $page->body);
                    $page->body = $html;
                }
                $page->updated = time();
                $page->save();
                return $this->_redirect('/gruppe/'.$group->id.'/'.$page->id);
            }

        }
    }

    public function gallerierAction()
    {
        if (!$this->user || $this->user->admin == 0) throw new Exception('Must be site administrator to edit / create albums');

        $gid = $this->_getParam('gid', -1);
        if ($gid == -1) {

            if ($this->getRequest()->isPost()) {
                $title = $this->_getParam('title', false);
                if ($title == false) return $this->_redirect('/endre/gallerier');
                $category = $this->_getParam('category', false);
                $album = Model_DbTable_FileCollections::createAlbum($title, $category);
                return $this->_redirect('/endre/gallerier/gid/' . $album->id);
            }

        }else{

            $album = $this->view->album = Model_DbTable_FileCollections::getCollection($gid);

            $this->view->uploadWidget = true;
            $this->view->uploadWidgetDestination = '/bilder/' . $album->id . '/act/uploader';
            $this->view->uploadWidgetFiletypes = array(
                'images' => true,
            );

            $act = $this->_getParam('act', false);
            if ($act == 'delete') {
                foreach ($album->getFiles() as $file) {
                    $filename = APPLICATION_PATH . '/../public/' . $file->src;
                    if (is_file($filename)) @unlink($filename);
                    if (is_file($filename.'.thumb.jpg')) @unlink($filename.'.thumb.jpg');
                }
                @rmdir(APPLICATION_PATH . '/../public/uploads/albums/' . $album->id);
                $album->delete();
                return $this->_redirect('/bilder');
            }

            if ($this->getRequest()->isPost()) {
                $title = $this->_getParam('title', false);
                if ($title == false) return $this->_redirect('/endre/gallerier');
                $category = $this->_getParam('category', false);
                $album->title = $title;
                $album->category = ($category ? $category : date('Y'));
                $album->save();
                return $this->_redirect('/endre/gallerier/gid/' . $album->id);
            }

        }
    }

    public function marinaAction()
    {
        if (!$this->user->admin) throw new Exception('Only site administrator allowed');

        $year = $this->view->year = $this->_getParam('year', false);

        $this->view->uploadWidget = true;
        $this->view->uploadWidgetDestination = '/marina' . ($year ? '?year=' . $year : '');
    }

    public function dokumenterAction()
    {
        if (!$this->user->admin) throw new Exception('Only site administrator allowed');

        $year = $this->view->year = $this->_getParam('year', false);

        $this->view->uploadWidget = true;
        $this->view->uploadWidgetDestination = '/dokumenter' . ($year ? '?year=' . $year : '');
    }
}
