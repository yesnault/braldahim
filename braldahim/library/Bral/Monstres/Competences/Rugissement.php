<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Rugissement extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$bonus = Bral_Util_De::get_1d3();
		$nbTours = Bral_Util_De::get_1d3();

		$x_min = $this->monstre["x_monstre"] - 1;
		$y_min = $this->monstre["y_monstre"] - 1;
		$x_max = $this->monstre["x_monstre"] + 1;
		$y_max = $this->monstre["y_monstre"] + 1;
		$z = $this->monstre["z_monstre"];

		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($x_min, $y_min, $x_max, $y_max, $z);

		$idTypeEvenement = self::$config->game->evenements->type->effet;

		foreach($monstres as $m) {
			Bral_Util_Effets::ajouteEtAppliqueEffetMonstre($m, Bral_Util_Effets::CARACT_AGILITE, Bral_Util_Effets::TYPE_BONUS, $nbTours, $bonus);
				
			if ($m["id_monstre"] != $this->monstre["id_monstre"]) {
				$details = "[m".$m["id_monstre"]."] est attentif au rugissement féroce de [m".$this->monstre["id_monstre"]."]";
				Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $m["id_monstre"], $idTypeEvenement, $details, "", $m["niveau_monstre"], $this->view);
			}
		}

		$this->majEvenement($bonus, $nbTours);

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($bonus, $nbTours) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->effet;
		$details = "[m".$this->monstre["id_monstre"]."] rugit avec férocité";
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, "", $this->monstre["niveau_monstre"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}
}