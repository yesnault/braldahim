<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public function run()
	{
		Zend_Registry::set('config', new Zend_Config($this->getOptions()));

		$registry = Zend_Registry::getInstance();
		$config = new Zend_Config_Ini('../application/configs/config.ini', 'general');
		$registry->set('config', $config);

		Zend_Layout::startMvc($config->layout);

		parent::run();
	}

	protected function _initDb() {
		$configdb = new Zend_Config_Ini('../application/configs/config.ini', 'general');

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

