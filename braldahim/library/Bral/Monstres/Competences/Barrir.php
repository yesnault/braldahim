<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Monstres_Competences_Barrir extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$braldunTable = new Braldun();
		
		$delta = 4;
		if ($this->monstre["vue_monstre"] + $this->monstre["vue_bm_monstre"] < 4) {
			$delta = $this->monstre["vue_monstre"] + $this->monstre["vue_bm_monstre"];
		}
		if ($delta < 0) {
			$delta = 0;
		}
		
		$xMin = $this->monstre["x_monstre"] - $delta;
		$xMax = $this->monstre["x_monstre"] - $delta;
		$yMin = $this->monstre["y_monstre"] + $delta;
		$yMax = $this->monstre["y_monstre"] + $delta;
		
		$bralduns = $braldunTable->selectVue($xMin, $yMin, $xMax, $yMax, $this->monstre["z_monstre"], -1, false);

		if ($bralduns != null) {
			foreach($bralduns as $h) {
				$malus = floor($this->monstre["niveau_monstre"]) - 3 + Bral_Util_De::get_1d6();
				if ($malus < 0) {
					$malus = 1;
				}
				$nbTours = Bral_Util_De::get_1d3();
				Bral_Util_Effets::ajouteEtAppliqueEffet($h["id_braldun"], Bral_Util_Effets::CARACT_SAGESSE, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus);
				$this->majEvenement($h, $malus, $nbTours);
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($braldun, $malus, $nbTours) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a barri sur [b".$braldun["id_braldun"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($braldun["id_braldun"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $braldun["niveau_braldun"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a barri, vous avez été influencé :";
		$retour .= PHP_EOL."Malus sur votre sagesse : -".$malus;
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}