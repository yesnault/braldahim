<?php
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(), APPLICATION_PATH . '/../application/models/'
)));

/** Zend_Application */
require_once 'Zend/Application.php';
require_once "Zend/Loader.php";

Zend_Loader::loadClass('Zend_Controller_Action');
Zend_Loader::loadClass('Zend_Controller_Front');
Zend_Loader::loadClass('Zend_Controller_Plugin_ErrorHandler');
Zend_Loader::loadClass('Zend_Config_Ini');
Zend_Loader::loadClass('Zend_Registry');
Zend_Loader::loadClass('Zend_Date');
Zend_Loader::loadClass('Zend_Db');
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass("Zend_Auth");
Zend_Loader::loadClass("Zend_Exception");
Zend_Loader::loadClass("Zend_Session");

Zend_Loader::loadClass("Bral_Box_Factory");
Zend_Loader::loadClass("Bral_Box_Box");
Zend_Loader::loadClass("Bral_Controller_Action");
Zend_Loader::loadClass("Bral_Controller_InfoJeu");

Zend_Loader::loadClass("Bral_Helper_Affiche");
Zend_Loader::loadClass("Bral_Helper_Calendrier");
Zend_Loader::loadClass("Bral_Helper_Fermer");
Zend_Loader::loadClass("Bral_Helper_Image");

Zend_Loader::loadClass("Bral_Util_BBParser");
Zend_Loader::loadClass("Bral_Util_De");
Zend_Loader::loadClass("Bral_Util_Controle");
Zend_Loader::loadClass("Bral_Util_ConvertDate");
Zend_Loader::loadClass("Bral_Util_Commun");
Zend_Loader::loadClass("Bral_Util_Messagerie");
Zend_Loader::loadClass("Bral_Util_Log");
Zend_Loader::loadClass("Bral_Util_Poids");
Zend_Loader::loadClass("Bral_Util_Registre");
Zend_Loader::loadClass("Bral_Util_String");

Zend_Loader::loadClass("Bral_Helper_BBBoutons");
Zend_Loader::loadClass("Bral_Helper_Tooltip");

Zend_Loader::loadClass("Bral_Xml_Response");
Zend_Loader::loadClass("Bral_Xml_Entry");

Zend_Loader::loadClass("Braldun");
Zend_Loader::loadClass("Message");
Zend_Loader::loadClass("Session");

Zend_Session::start();

$debut2 = microtime(true);

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();