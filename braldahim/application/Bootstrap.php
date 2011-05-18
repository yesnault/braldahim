<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public function run()
	{
		Zend_Registry::set('config', new Zend_Config($this->getOptions()));

		$registry = Zend_Registry::getInstance();
		$config = new Zend_Config_Ini('../application/configs/config.ini', 'general');
		$registry->set('config', $config);

		if ($_SERVER['SERVER_NAME'] == "mobile.braldahim.local" || $_SERVER['SERVER_NAME'] == "mobile.braldahim.com" || $_SERVER['SERVER_NAME'] == "iphone.braldahim.com") {
			Zend_Registry::set('estMobile', true);
		} else {
			Zend_Registry::set('estMobile', false);
		}

		if ($_SERVER['SERVER_NAME'] == "iphone.braldahim.com") {
			Zend_Registry::set('estIphone', true);
		} else {
			Zend_Registry::set('estIphone', false);
		}

		if ($_SERVER['SERVER_NAME'] == "work.braldahim.local" || $_SERVER['SERVER_NAME'] == "work.braldahim.com" || $_SERVER['SERVER_NAME'] == "85.17.183.147") {
			Zend_Registry::set('estWork', true);
		} else {
			Zend_Registry::set('estWork', false);
		}

		Bral_Util_Registre::chargement();
		srand((double) microtime() * 1000000);

		parent::run();
	}

	protected function _initDb() {
		$configdb = new Zend_Config_Ini('../application/configs/configdb.ini', 'general');

		try {
			$dbAdapterGame = Zend_Db::factory($configdb->db->game->adapter, $configdb->db->game->config->toArray());
			$dbAdapterGame->query('SET NAMES UTF8');
			$dbAdapterGame->getConnection();
		} catch (Exception $e) {
			exit($e->getMessage());
		}
		Zend_Db_Table::setDefaultAdapter($dbAdapterGame);
		Zend_Registry::set('dbAdapter', $dbAdapterGame);
		return $dbAdapterGame;
	}


}

