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
Zend_Loader :: loadClass('Zend_Registry');
Zend_Loader :: loadClass('Zend_Date');
Zend_Loader :: loadClass('Zend_Db');
Zend_Loader :: loadClass('Zend_Db_Table');
Zend_Loader :: loadClass("Zend_Auth");

Zend_Loader :: loadClass("Bral_Box_Factory");
Zend_Loader :: loadClass("Bral_Box_Box");
Zend_Loader :: loadClass("Bral_Boutique_Factory");
Zend_Loader :: loadClass("Bral_Batchs_Factory");
Zend_Loader :: loadClass("Bral_Controller_Action");
Zend_Loader :: loadClass("Bral_Competences_Factory");
Zend_Loader :: loadClass("Bral_Echoppe_Factory");
Zend_Loader :: loadClass("Bral_Echoppes_Factory");
Zend_Loader :: loadClass("Bral_Lieux_Factory");

Zend_Loader :: loadClass("Bral_Helper_Affiche");
Zend_Loader :: loadClass("Bral_Helper_Fermer");
Zend_Loader :: loadClass("Bral_Helper_Image");

Zend_Loader :: loadClass("Bral_Util_BBParser");
Zend_Loader :: loadClass("Bral_Util_De");
Zend_Loader :: loadClass("Bral_Util_Exception");
Zend_Loader :: loadClass("Bral_Util_Controle");
Zend_Loader :: loadClass("Bral_Util_ConvertDate");
Zend_Loader :: loadClass("Bral_Util_Commun");
Zend_Loader :: loadClass("Bral_Util_Faim");
Zend_Loader :: loadClass("Bral_Util_JoomlaUser");
Zend_Loader :: loadClass("Bral_Util_Log");
Zend_Loader :: loadClass("Bral_Util_Poids");
Zend_Loader :: loadClass("Bral_Util_Registre");
Zend_Loader :: loadClass("Bral_Util_Securite");
Zend_Loader :: loadClass("Bral_Util_String");
Zend_Loader :: loadClass("Bral_Util_Tour");

Zend_Loader :: loadClass("Bral_Helper_BBBoutons");
Zend_Loader :: loadClass("Bral_Helper_Box");
Zend_Loader :: loadClass("Bral_Helper_Tooltip");
Zend_Loader :: loadClass("Bral_Helper_Messagerie");

Zend_Loader :: loadClass("Bral_Xml_Response");
Zend_Loader :: loadClass("Bral_Xml_Entry");

Zend_Loader :: loadClass("Competence");
Zend_Loader :: loadClass("Hobbit");
Zend_Loader :: loadClass("JosUddeim");

// load configuration
$config = new Zend_Config_Ini('./application/config.ini', 'general');
$registry = Zend_Registry :: getInstance();
$registry->set('config', $config);

// setup database Game
$dbAdapterGame = Zend_Db :: factory($config->db->game->adapter, $config->db->game->config->toArray());
$dbAdapterGame->query('SET NAMES UTF8');

Zend_Db_Table :: setDefaultAdapter($dbAdapterGame);
Zend_Registry :: set('dbAdapter', $dbAdapterGame);

// setup database Site
$dbAdapterSite = Zend_Db :: factory($config->db->site->adapter, $config->db->site->config->toArray());
$dbAdapterSite->query('SET NAMES UTF8');

Zend_Registry :: set('dbSiteAdapter', $dbAdapterSite);

Bral_Util_Registre :: chargement();

// setup controller
$frontController = Zend_Controller_Front :: getInstance();
$frontController->setParam('noViewRenderer', true);
$frontController->throwExceptions(true);
$frontController->setControllerDirectory('./application/controllers');

// run!
try {
    $frontController->dispatch();
} catch (Exception $e) {
    Bral_Util_Exception :: traite($e);
}