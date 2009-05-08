<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Paris');

set_include_path('.' . PATH_SEPARATOR . './library' . PATH_SEPARATOR . './application/models/' . PATH_SEPARATOR . get_include_path());
include "Zend/Loader.php";

Zend_Loader :: loadClass('Zend_Controller_Action');
Zend_Loader :: loadClass('Zend_Controller_Front');
Zend_Loader :: loadClass('Zend_Config_Ini');
Zend_Loader :: loadClass('Zend_Date');
Zend_Loader :: loadClass('Zend_Db');
Zend_Loader :: loadClass('Zend_Db_Table');
Zend_Loader :: loadClass("Zend_Layout");
Zend_Loader :: loadClass("Zend_Registry");

Zend_Loader :: loadClass("Bral_Controller_Box");
Zend_Loader :: loadClass("Bral_Brasserie_Factory");
Zend_Loader :: loadClass("Bral_Palmares_Factory");
Zend_Loader :: loadClass("Bral_Helper_Box");
Zend_Loader :: loadClass("Bral_Util_BBParser");
Zend_Loader :: loadClass("Bral_Util_ConvertDate");
Zend_Loader :: loadClass("Bral_Util_Registre");

// load configuration
$config = new Zend_Config_Ini('./application/config.ini', 'general');
$registry = Zend_Registry :: getInstance();
$registry->set('config', $config);

// setup database
$dbAdapterGame = Zend_Db :: factory($config->db->game->adapter, $config->db->game->config->toArray());
$dbAdapterGame->query('SET NAMES UTF8');

Zend_Db_Table :: setDefaultAdapter($dbAdapterGame);
Zend_Registry :: set('dbAdapter', $dbAdapterGame);

Zend_Layout::startMvc($config->layout);

try {
	// setup controller
	$frontController = Zend_Controller_Front :: getInstance();
	$frontController->throwExceptions(true);
	$frontController->setControllerDirectory('./application/controllers');
	
	$frontController->dispatch();
} catch (Exception $e) {
	print_r($e);
	//header('Location: /');
}