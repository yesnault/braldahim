<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Fichier {

	public static function ecrire($fichier, $contenu, $mode = 'w') {
		Bral_Util_Log::batchs()->trace("Bral_Util_Fichier - ecrire - enter -");

		if (is_writable($fichier)) {
			if (!$handle = fopen($fichier, $mode)) {
				throw new Zend_Exception("Impossible d'ouvrir le fichier ($fichier)");
			}
			if (fwrite($handle, $contenu) === FALSE) {
				throw new Zend_Exception("Impossible d'écrire dans le fichier ($fichier)");
			}
			fclose($handle);
		} else {
			throw new Zend_Exception("Le fichier $fichier n'est pas accessible en écriture.");
		}
		Bral_Util_Log::batchs()->trace("Bral_Util_Fichier - ecrire - exit -");
	}
}