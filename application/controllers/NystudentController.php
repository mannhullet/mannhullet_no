<?php

class NystudentController extends Zend_Controller_Action
{
    protected $user;

    public function init()
    {
        $this->view->headerNav = '/nystudent';
        $this->view->headTitle('Ny student');

        // Check if we're logged in or not
        $userSession = new Zend_Session_Namespace('user');
        $user = $this->user = $this->view->user = (isset($userSession->user) ? $userSession->user : false);

        // Used for the groups-menu
        $groups = $this->view->groups = Model_DbTable_Groups::getGroups();

        Model_DbTable_Sessions::persistUserId();

        $this->view->socialIconFacebook = Model_DbTable_Partials::getHtml('/footer:socialIconFacebook');
        $this->view->socialIconInstagram = Model_DbTable_Partials::getHtml('/footer:socialIconInstagram');

        $sponsor = $this->view->sponsor = Model_DbTable_Sponsors::getSponsorRandom();
    }

    public function indexAction()
    {
        $this->view->mainnav = '/nystudent';
    }

    public function kalenderAction()
    {
        $this->view->mainnav = '/nystudent/kalender';
        $this->view->headTitle('Kalender', 'PREPEND');
    }

    public function kontaktAction()
    {
        $this->view->mainnav = '/nystudent/kontakt';
        $this->view->headTitle('Kontakt', 'PREPEND');
    }
}

