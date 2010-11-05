<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Bouchertunnel extends Bral_Monstres_Competences_Prereperage {

	// prereperage
	public function actionSpecifique() {

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - enter - (idm:".$this->monstre["id_monstre"].")");

		$retour = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD;

		//abat un tunnel et disparait s'il n'a personne (Braldûn) dans sa vue.
		$braldunTable = new Braldun();
		$vue = $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"];
		if ($vue < 0) {
			$vue = 0;
		}

		$cible = $braldunTable->findBraldunAvecRayon($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], $vue, null, false);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], $this->monstre["id_fk_groupe_monstre"]);
		
		Zend_Loader::loadClass("Nid");
		$nidTable = new Nid();
		$nids = $nidTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"]);

		if ($cible == null || count($cible) < 1) {
				
			if ($nids != null && count($nids) > 0) { // S'il y a des nids sur la case
				$retour = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD;
			} elseif ($monstres == null || count($monstres) < 1) { // S'il n'y a pas d'autres groupes ou monstres hors du groupe sur la case
				Zend_Loader::loadClass("Tunnel");
				$tunnelTable = new Tunnel();
				$tunnelTable->delete("x_tunnel = ".$this->monstre["x_monstre"]." and y_tunnel = ".$this->monstre["y_monstre"]." and z_tunnel = ".$this->monstre["z_monstre"]);
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - (idm:".$this->monstre["id_monstre"].") - braldun non vue, suppression de tunnel x:".$this->monstre["x_monstre"]. " y:".$this->monstre["y_monstre"]." z:".$this->monstre["z_monstre"]);
				$retour = Bral_Monstres_Competences_Prereperage::SUITE_DISPARITION;
			} else {
				// on ne bouche pas le tunnel, il y a d'autres type de monstre sur la case
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - (idm:".$this->monstre["id_monstre"].") - braldun non vue, pas suppression de tunnel. Nb monstre sur case hors groupe :".count($monstres). " x:".$this->monstre["x_monstre"]. " y:".$this->monstre["y_monstre"]." z:".$this->monstre["z_monstre"]);
				$retour = Bral_Monstres_Competences_Prereperage::SUITE_DISPARITION;
			}
			
		} else {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - (idm:".$this->monstre["id_monstre"].") - braldun en vue, pas de suppression de tunnel");
			$retour = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD;
		}
			
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - exit - (idm:".$this->monstre["id_monstre"].")");
		return $retour;
	}

	public function enchainerAvecReperageStandard() {
		return false;
	}
}