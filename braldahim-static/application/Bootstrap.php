<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	public function run() {
		Zend_Registry::set('config', new Zend_Config($this->getOptions()));

		$registry = Zend_Registry::getInstance();
		$config = new Zend_Config_Ini('../application/configs/config.ini', 'general');
		$registry->set('config', $config);
		
		parent::run();
	}

}

