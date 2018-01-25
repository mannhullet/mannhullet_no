<?php

class ErrorController extends Zend_Controller_Action
{
    protected $user;

    public function init()
    {
        // Check if we're logged in or not
        $userSession = new Zend_Session_Namespace('user');
        $user = $this->user = $this->view->user = (isset($userSession->user) ? $userSession->user : false);

        Model_DbTable_Sessions::persistUserId();

        $this->view->socialIconFacebook = Model_DbTable_Partials::getHtml('/footer:socialIconFacebook'); //'https://www.facebook.com/groups/2217435004/';
        $this->view->socialIconTwitter = Model_DbTable_Partials::getHtml('/footer:socialIconTwitter'); //'https://twitter.com/search?q=mannhullet&src=typd';

        $sponsor = $this->view->sponsor = Model_DbTable_Sponsors::getSponsorRandom();
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }
}

