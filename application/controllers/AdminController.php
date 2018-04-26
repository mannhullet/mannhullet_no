<?php

class AdminController extends Zend_Controller_Action
{
    protected $user;

    public function init()
    {
        // Check if we're logged in or not
        $userSession = new Zend_Session_Namespace('user');
        $user = $this->user = $this->view->user = (isset($userSession->user) ? $userSession->user : false);

        if (!$user || !$user->admin) throw new Exception('Not logged in or not the site administrator');

        // Used for the groups-menu
        $groups = $this->view->groups = Model_DbTable_Groups::getGroups();

        //$this->view->mainnav = '/admin';
        $this->view->headerNav = '/';

        Model_DbTable_Sessions::persistUserId();

        $this->view->socialIconFacebook = Model_DbTable_Partials::getHtml('/footer:socialIconFacebook');
        $this->view->socialIconInstagram = Model_DbTable_Partials::getHtml('/footer:socialIconInstagram');

        $sponsor = $this->view->sponsor = Model_DbTable_Sponsors::getSponsorRandom();
    }

    public function indexAction()
    {
        $this->view->secondarynav = '/admin';
    }

    public function nyheterAction()
    {
        $this->view->secondarynav = '/admin/nyheter';
        $this->view->tinymce = true;
    }

    public function mediaAction()
    {
        $this->view->secondarynav = '/admin/media';
    }

    public function undergrupperAction()
    {
        $this->view->secondarynav = '/admin/undergrupper';
        $gid = $this->view->gid = $this->_getParam('gid', false);

        $this->view->colorPicker = true;

        if ($gid) {

            $this->view->typeahead = true;
            $members = $this->view->members   = Model_DbTable_Users::getAllUsers();

            $group = $this->view->group = Model_DbTable_Groups::getGroup($gid);

            $action = $this->_getParam('act', false);
            if ($action == 'revokeAdmin') {
                $uid = $this->_getParam('uid', -1);
                $user = Model_DbTable_Users::getUserById($uid);
                Model_DbTable_GroupAdmins::removeAdmin($group, $user);
                return $this->_redirect('/admin/undergrupper/gid/' . $group->id);
            }

            if ($this->getRequest()->isPost()) {
                $submit = $this->_getParam('submit', false);
                if ($submit == 'delete') {
                    $group->delete();
                    return $this->_redirect('/admin/undergrupper');
                }else if ($submit == 'addAdmin') {
                    $email = $this->_getParam('email', '');
                    if ($email != "") {
                        $user = Model_DbTable_Users::getUser($email);
                        Model_DbTable_GroupAdmins::addAdmin($group, $user);
                    }
                    return $this->_redirect('/admin/undergrupper/gid/' . $group->id);
                }
                $title = $this->_getParam('title', null);
                if ($title) $group->title = $title;
                if ($group->external) {
                    $external = $this->_getParam('external', null);
                    $group->external = $external;
                }else{
                    $image = $this->_getParam('image', null);
                    $body = $this->_getParam('body', null);
                    $color = $this->_getParam('color', null);
                    $group->image = $image;
                    $group->color = $color != '' ? $color : null;
                    $group->body  = $body;
                }
                $group->save();
                return $this->_redirect('/admin/undergrupper/gid/' . $group->id);
            }

        }else{

            if ($this->getRequest()->isPost()) {
                $isExternal = $this->_getParam('isExternal', false);
                $title = $this->_getParam('title', null);
                if ($isExternal) {
                    $external = $this->_getParam('external', null);
                    $group = Model_DbTable_Groups::createGroup($title, $external);
                    return $this->_redirect('/admin/undergrupper/gid/' . $group->id);
                }else{
                    $image = $this->_getParam('image', null);
                    $color = $this->_getParam('color', null);
                    $body = $this->_getParam('body', null);
                    $group = Model_DbTable_Groups::createGroup($title, null, ($color != '' ? $color : null), $image, $body);
                    return $this->_redirect('/admin/undergrupper/gid/' . $group->id);
                }
            }

        }
    }

    public function medlemmerAction()
    {
        $this->view->secondarynav = '/admin/medlemmer';
        $users = $this->view->users = Model_DbTable_Users::getAllUsers();
        $registrationTokens = $this->view->registrationTokens = array();

        $siteAdmins = $this->view->siteAdmins = Model_DbTable_Users::getSiteAdmins();

        $this->view->typeahead = true;
        $this->view->adminMembers = true;

        $members = $this->view->members = Model_DbTable_Users::getAllUsers();
        $registrationTokens = $this->view->registrationTokens = Model_DbTable_Users::getAllUsers(true);

        $act = $this->_getParam('act', '');
        if ($act == 'revokeSiteAdmin') {
            if (count($siteAdmins) == 1) throw new Exception('There must always be at least one administrator');
            $user_id = $this->_getParam('uid', -1);
            $user = Model_DbTable_Users::getUserById($user_id);
            if (!$user) throw new Exception('User does not exist');
            $user->admin = 0;
            $user->save();
            Model_DbTable_Users::logoutUser($user);
            return $this->_redirect('/admin/medlemmer');
        }else if ($act == 'delete') {
            $user_id = $this->_getParam('uid', -1);
            if ($user_id == $this->user->id) throw new Exception('It\'s not allowed to delete yourself.');
            $user = Model_DbTable_Users::getUserById($user_id);
            if (!$user) throw new Exception('User does not exist');
            Model_DbTable_Users::logoutUser($user);
            $user->delete();
            return $this->_redirect('/admin/medlemmer');
        }

        if ($this->getRequest()->isPost()) {

            if ($act == 'promoteSiteAdmin') {
                $user_id = $this->_getparam('email', '');
                $user = Model_DbTable_Users::getUser($user_id);
                if (!$user) throw new Exception('User does not exist');
                $user->admin = 1;
                $user->save();
                return $this->_redirect('/admin/medlemmer');
            }else if ($act == 'registrationRight') {
                $emails   = $this->_getparam('emails', '');
                $sendMail = $this->_getparam('sendMail', '');
                if (preg_match_all('/([a-z0-9._-]+)@stud\.ntnu\.no/i', $emails, $matches, PREG_SET_ORDER) < 1) throw new Exception('No emails found');
                $user = null;
                foreach ($matches as $match) {
                    $studmail = $match[1] . '@stud.ntnu.no';
                    $user = Model_DbTable_Users::createUserRegistrationRight($studmail);
                }
                if ($sendMail && $user) {
                    Model_DbTable_Users::sendInvite($user);
                }
                return $this->_redirect('/admin/medlemmer');
            }

        }
    }

    public function innstillingerAction()
    {
        $this->view->secondarynav = '/admin/innstillinger';

        $this->view->slides = Model_DbTable_Carousel::getSlides();

        $personsStyret       = $this->view->personsStyret = Model_DbTable_AboutPersons::getPersons('styret');
        $personsGruppesjefer = $this->view->personsGruppesjefer = Model_DbTable_AboutPersons::getPersons('gruppesjefer');
        $personsSpr          = $this->view->personsSpr = Model_DbTable_AboutPersons::getPersons('spr');
        $personsKtr          = $this->view->personsKtr = Model_DbTable_AboutPersons::getPersons('ktr');

        $sponsors = $this->view->sponsors = Model_DbTable_Sponsors::getAllSponsors();

        $mbkCertificates = $this->view->mbkCertificates = Model_DbTable_PluginMBKReservations::getCertificates();
        $members = $this->view->members = Model_DbTable_Users::getAllUsers();
        $this->view->typeahead = true;

        $this->view->adminInnstillingerSortableJS = true;

        $act = $this->_getParam('act', '');
        if ($act == 'deleteslide') {
            $sid = $this->_getParam('sid', '');
            Model_DbTable_Carousel::removeSlide($sid);
            return $this->_redirect('/admin/innstillinger');
        }else if ($act == 'deleteperson') {
            $pid = $this->_getParam('pid', '');
            Model_DbTable_AboutPersons::removePerson($pid);
            return $this->_redirect('/admin/innstillinger');
        }else if ($act == 'deletesponsor') {
            $sid = $this->_getParam('sid', '');
            Model_DbTable_Sponsors::removeSponsor($sid);
            return $this->_redirect('/admin/innstillinger');
        }else if ($act == 'deleteMBKCertificate') {
            $cid = $this->_getParam('cid', '');
            Model_DbTable_PluginMBKReservations::deleteCertificate($cid);
            return $this->_redirect('/admin/innstillinger');
        }

        if ($this->getRequest()->isPost()) {

            if ($act == 'createSlide') {
                $title = $this->_getParam('title', false);
                $image = $this->_getParam('image', false);
                $body  = $this->_getParam('body',  '');
                $index = $this->_getParam('index', 999);
                if (!$image || !$title || $image == '' || $title == '') {
                    $this->view->errorCreateSlide = 'Bilde og tittel er ikke valgfritt, prøv igjen!';
                    return;
                }
                Model_DbTable_Carousel::createSlide($title, $image, $body, $index);
                return $this->_redirect('/admin/innstillinger');
            }else if ($act == 'editSlide') {
                $sid = $this->_getParam('sid', -1);
                $slide = Model_DbTable_Carousel::getSlide($sid);
                $title = $this->_getParam('title', false);
                $image = $this->_getParam('image', false);
                $body  = $this->_getParam('body',  '');
                if (!$image || !$title || $image == '' || $title == '') {
                    $this->view->errorEditSlide = array('id' => $slide->id, 'error' => 'Tittel og bakgrunnsbilde er ikke valgfritt, prøv igjen!');
                    return;
                }
                $slide->title = $title;
                $slide->image = $image;
                $slide->body  = $body;
                $slide->save();
                return $this->_redirect('/admin/innstillinger');
            }else if ($act == 'reorderSlide') {
                $slideOrder = $this->_getParam('slideOrder', false);
                if (!$slideOrder) die('Slide order required');
                $slideOrder = json_decode($slideOrder);
                if (!is_array($slideOrder)) $slideOrder = array();
                foreach ($slideOrder as $index => $sid) Model_DbTable_Carousel::moveSlide($sid, $index);
                die('Success');
            }else if ($act == 'reorderPeople') {
                $peopleOrder = $this->_getParam('peopleOrder', false);
                if (!$peopleOrder) die('People order required');
                $peopleOrder = json_decode($peopleOrder);
                if (!is_array($peopleOrder)) $peopleOrder = array();
                foreach ($peopleOrder as $index => $pid) Model_DbTable_AboutPersons::movePerson($pid, $index);
                die('Success');
            }else if ($act == 'createPerson') {
                $category = $this->_getParam('category', 'styret');
                $image = $this->_getParam('image', false);
                $name = $this->_getParam('name', false);
                $title = $this->_getParam('title', null);
                $contact_info = $this->_getParam('contact_info', null);
                $popover = $this->_getParam('popover',  null);
                if (!$image || !$name || $image == '' || $name == '') {
                    $this->view->errorCreatePerson = 'Bilde og navn er ikke valgfritt, prøv igjen!';
                    return;
                }
                Model_DbTable_AboutPersons::addPerson($category, $image, $name, $title, $contact_info, $popover);
                return $this->_redirect('/admin/innstillinger');
            }else if ($act == 'editPerson') {
                $pid = $this->_getParam('pid', -1);
                $person = Model_DbTable_AboutPersons::getPerson($pid);
                $image = $this->_getParam('image', false);
                $name = $this->_getParam('name', false);
                $title = $this->_getParam('title', null);
                $contact_info = $this->_getParam('contact_info', null);
                $popover = $this->_getParam('popover',  null);
                if (!$image || !$name || $image == '' || $name == '') {
                    $this->view->errorEditPerson = array('id' => $person->id, 'error' => 'Bilde og navn er ikke valgfritt, prøv igjen!');
                    return;
                }
                $person->image = $image;
                $person->name = $name;
                $person->title = $title;
                $person->contact_info = $contact_info;
                $person->popover = $popover;
                $person->save();
                return $this->_redirect('/admin/innstillinger');
            }else if ($act == 'createSponsor') {
                $title = $this->_getParam('title', false);
                $image = $this->_getParam('image', false);
                $href = $this->_getParam('href',  null);
                if (!$image || !$title || $image == '' || $title == '') {
                    $this->view->errorCreateSponsor = 'Tittel og bilde er ikke valgfritt, prøv igjen!';
                    return;
                }
                Model_DbTable_Sponsors::createSponsor($title, $image, $href);
                return $this->_redirect('/admin/innstillinger');
            }else if ($act == 'createMBKCertificate') {
                $user_id = $this->_getparam('email', '');
                $user = Model_DbTable_Users::getUser($user_id);
                if (!$user) throw new Exception('User does not exist');
                Model_DbTable_PluginMBKReservations::createCertificate($user);
                return $this->_redirect('/admin/innstillinger');
            }else{
                $socialFb = $this->_getParam('social_fb', '');
                $socialInsta = $this->_getParam('social_insta', '');
                Model_DbTable_Partials::setHtml('/footer:socialIconFacebook', $socialFb);
                Model_DbTable_Partials::setHtml('/footer:socialIconInstagram', $socialInsta);
                return $this->_redirect('/admin/innstillinger');
            }

        }
    }
}
