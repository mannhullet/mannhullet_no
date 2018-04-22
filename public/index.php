<?php

ini_set('max_execution_time', 300);

// Important if you wan't data(..) to work correctly on all systems
date_default_timezone_set('Europe/Oslo');

// Set the root of the project as current dir
chdir(realpath(dirname(__FILE__) . '/../'));

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Config/Ini.php';

// Merge the main config application.ini with the local config environment.ini
// The local config contains database info, and should not be publicised
$config = new Zend_Config_Ini(
    APPLICATION_PATH . '/configs/application.ini',
    APPLICATION_ENV
);
$environment = new Zend_Config_Ini(
    APPLICATION_PATH . '/configs/environment.ini',
    APPLICATION_ENV,
    array('allowModifications' => true)
);
$environment->merge($config);
$environment->setReadOnly();


// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    $environment
);

$application->bootstrap()->run();
