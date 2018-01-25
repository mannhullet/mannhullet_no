<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initTemporaryMailTransport()
    {/*
        $config = array(
            'ssl' => 'tls',
            'port' => 587,
            'auth' => 'login',
            'username' => 'no-reply@mannhullet.no',
            'password' => 'noreply'
        );

        $transport = new Zend_Mail_Transport_Smtp('send.one.com', $config);
        Zend_Mail::setDefaultTransport($transport);*/
    }
}

