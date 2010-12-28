<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Scripts_Coffre extends Bral_Scripts_Conteneur {

	public function calculScriptImpl() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Coffre - calculScriptImpl - enter -");

		$retour = null;

		Zend_Loader::loadClass("Coffre");
		$coffreTable = new Coffre();
		
		$coffre = $coffreTable->findByIdBraldun($this->braldun->id_braldun);
		if ($coffre == null || count($coffre) != 1) {
			throw new Zend_Eception("Erreur Bral_Scripts_Coffre idb:".$this->braldun->id_braldun);
		} 

		$this->calculConteneur("Coffre", $retour, null, $coffre[0]["id_coffre"]);

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Coffre - calculScriptImpl - exit -");
		return $retour;
	}
}