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
class Bral_Monstres_Competences_Petnauseabond extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], -1, false);

		if ($bralduns != null) {
			foreach($bralduns as $h) {
				$malus = $this->monstre["niveau_monstre"] - 3 + Bral_Util_De::get_1d6();
				if ($malus <= 0) {
					$malus = 1;
				}

				$nbTours = 1;

				$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->monstre["force_base_monstre"]);
				$jetMonstre = $jetMonstre + $this->monstre["force_bm_monstre"];

				$jetBraldun = Bral_Util_De::getLanceDe6(self::$config->game->base_sagesse + $h["sagesse_base_braldun"]);
				$jetBraldun = $jetBraldun + $h["sagesse_bm_braldun"] + $h["sagesse_bbdf_braldun"];

				if ($jetBraldun > $jetMonstre) {
					$malus = floor($malus / 2);
				}

				Bral_Util_Effets::ajouteEtAppliqueEffetBraldun($h["id_braldun"], Bral_Util_Effets::CARACT_BBDF, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, 'Pet Nauséabond');
				$this->majEvenement($h, $malus, $nbTours, $jetMonstre, $jetBraldun);
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($braldun, $malus, $nbTours, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a effectué un pet nauséabond, retournant l'estomac du Braldûn [b".$braldun["id_braldun"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($braldun["id_braldun"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $braldun["niveau_braldun"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a effectué un pet nauséabond, vous retournant l'estomac :";
		$retour .= PHP_EOL."Balance de faim : -".$malus."%";
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		$retour .= PHP_EOL."Jet du Monstre (jet de force) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet de sagesse) : ".$jetBraldun;
		if ($jetBraldun > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté au Pet, le malus a été divisé par 2.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté au Pet.";
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}