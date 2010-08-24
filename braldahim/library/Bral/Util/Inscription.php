<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Mail.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Util_Inscription {
	
	public static function getLienValidation($idBraldun, $emailBraldun, $md5Prenom, $md5Password) {
		$config = Zend_Registry::get('config');
		
		$urlValidation = $config->general->url;
		$urlValidation .= "/inscription/validation?e=".urlencode($emailBraldun);
		$urlValidation .= "&h=".$md5Prenom;
		$urlValidation .= "&p=".$md5Password;
		return $urlValidation;
	}
}