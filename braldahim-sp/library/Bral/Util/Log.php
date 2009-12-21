<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
/**
 * Ecriture de Log.
 * Les logs sont paramétrés dans le fichier de configuration.
 */
class Bral_Util_Log {
	private static $instance = null;
	private static $scripts = null;
	private static $config = null;

	public static function scripts() {
		if (self::$scripts == null) {
			self::initLogScripts();
		}
		return self::$scripts;
	}

	//______________
	private function __construct() {
	}

	private static function getInstance() {
		if (self::$instance == null) {
			Zend_Loader::loadClass('Zend_Log');
			Zend_Loader::loadClass('Zend_Log_Writer_Stream');
			self::$instance = new self();
			self::$config = Zend_Registry::get('config');
			return self::$instance;
		}
	}

	private static function initLogScripts() {
		if (self::$instance == null) {
			$instance = self::getInstance();
		}
		self::$config = Zend_Registry::get('config');
		self::$scripts = new Zend_Log();
		$redacteur = new Zend_Log_Writer_Stream(self::$config->log->fichier->scripts);
		self::$scripts->addWriter($redacteur);
		$filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->scripts);
		self::$scripts->addFilter($filtre);
		self::$scripts->addPriority('TRACE', 8);

		if (self::$config->log->general->debug_browser == "oui") {
			$redacteur = new Zend_Log_Writer_Stream('php://output');
			self::$scripts->addWriter($redacteur);
		}
	}

}
