<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Hash {

	public static function getHashString($salt, $chaine) {
		return hash('sha256', $salt.'::'.$chaine);
	}
	
	public static function getSalt() {
		return time().'::'.rand();
	}

}
