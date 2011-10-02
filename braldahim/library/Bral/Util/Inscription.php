<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Inscription
{

	public static function getLienValidation($idBraldun, $emailBraldun, $md5Prenom, $md5Password)
	{
		$config = Zend_Registry::get('config');

		$urlValidation = $config->general->url;
		$urlValidation .= "/inscription/validation?e=" . urlencode($emailBraldun);
		$urlValidation .= "&h=" . $md5Prenom;
		$urlValidation .= "&p=" . $md5Password;
		return $urlValidation;
	}
}