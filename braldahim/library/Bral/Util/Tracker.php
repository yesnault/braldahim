<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Registre.php 2487 2010-03-21 18:19:44Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-03-21 19:19:44 +0100 (Dim, 21 mar 2010) $
 * $LastChangedRevision: 2487 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Util_Tracker {

	private function __construct(){}

	public static function getKey() {
		if ($_SERVER['SERVER_NAME'] == "mobile.braldahim.com")  {
			$key = $this->config->tracker->id->mobile;
		} elseif ($_SERVER['SERVER_NAME'] == "iphone.braldahim.com") {
			$key = $this->config->tracker->id->iphone;
		} elseif ($_SERVER['SERVER_NAME'] == "work.braldahim.com") {
			$key = $this->config->tracker->id->work;
		} else {
			$key = $this->config->tracker->id->jeu;
		}
		return $key;
	}
}
