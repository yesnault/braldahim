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
class Bral_Scripts_Charrette extends Bral_Scripts_Conteneur {

	public function calculScriptImpl() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Charrette - calculScriptImpl - enter -");

		$retour = null;
		
		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($this->braldun->id_braldun);
		if ($charrettes != null && count($charrettes) == 1) {
			$idCharrette = $charrettes[0]["id_charrette"];
			$retour = "CHARRETTE;".$idCharrette.";".$charrettes[0]["durabilite_max_charrette"].';'.$charrettes[0]["durabilite_actuelle_charrette"].';'.$charrettes[0]["poids_transportable_charrette"].';'. $charrettes[0]["poids_transporte_charrette"].PHP_EOL;
			$this->calculConteneur("Charrette", $retour, $idCharrette);	
		} else {
			$retour = "AUCUNE_CHARRETTE";
		}

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Charrette - calculScriptImpl - exit -");
		return $retour;
	}
}