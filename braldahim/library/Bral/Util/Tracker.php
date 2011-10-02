<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Tracker
{

	private function __construct()
	{
	}

	public static function getKey()
	{
		$config = Zend_Registry::get('config');
		if ($_SERVER['SERVER_NAME'] == "mobile.braldahim.com") {
			$key = $config->tracker->id->mobile;
		} elseif ($_SERVER['SERVER_NAME'] == "iphone.braldahim.com") {
			$key = $config->tracker->id->iphone;
		} elseif ($_SERVER['SERVER_NAME'] == "work.braldahim.com") {
			$key = $config->tracker->id->work;
		} else {
			$key = $config->tracker->id->jeu;
		}
		return $key;
	}
}
