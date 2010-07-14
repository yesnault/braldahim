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
class Bral_Monstres_Competences_Souffledefeu extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$x_min = $this->monstre["x_monstre"] - 1;
		$y_min = $this->monstre["y_monstre"] - 1;
		$x_max = $this->monstre["x_monstre"] + 1;
		$y_max = $this->monstre["y_monstre"] + 1;
		$z = $this->monstre["z_monstre"];

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->selectVue($x_min, $y_min, $x_max, $y_max, $z, -1, false);

		$koCible = false;

		if ($bralduns != null) {
			foreach($bralduns as $h) {
				$malus = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->monstre["force_base_monstre"]);
				$malus = $malus + $this->monstre["force_bm_monstre"];
				$malus = floor($malus / 2);

				if ($malus <= 2) {
					$malus = 2;
				}

				$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->monstre["force_base_monstre"]);
				$jetMonstre = $jetMonstre + $this->monstre["force_bm_monstre"];

				$jetBraldun = Bral_Util_De::getLanceDe6(self::$config->game->base_vigueur + $h["vigueur_base_braldun"]);
				$jetBraldun = $jetBraldun + $h["vigueur_bm_braldun"] + $h["vigueur_bbdf_braldun"];

				if ($jetBraldun > $jetMonstre) {
					$malus = floor($malus / 2);
				}

				$armureTotale = $h["armure_naturelle_braldun"] + $h["armure_equipement_braldun"] + $h["armure_bm_braldun"];
				if ($armureTotale < 0) {
					$armureTotale = 0;
				}
				$pvEnMoins = $malus -  $armureTotale;
				if ($pvEnMoins < 1) {
					$pvEnMoins = 1;
				}
				$h["pv_restant_braldun"] = $h["pv_restant_braldun"] - $pvEnMoins;
				$this->cible = $h;
				if ($h["pv_restant_braldun"] <= 0) {
					if ($this->cible["id_braldun"] == $this->monstre["id_fk_braldun_cible_monstre"]) {
						$koCible = true;
					}
					$details = $this->initKo();
					$detailsBot = $this->getDetailsBot($malus, $pvEnMoins, $jetMonstre, $jetBraldun);
					$detailsBot .= PHP_EOL."Vous êtes KO.";
					$id_type_evenement_cible = self::$config->game->evenements->type->ko;
					Bral_Util_Evenement::majEvenementsFromVieMonstre($h["id_braldun"], null, $id_type_evenement_cible, $details, $detailsBot, $h["niveau_braldun"], $this->view);
				} else {
					$this->majEvenement($h, $malus, $pvEnMoins, $jetMonstre, $jetBraldun);
				}

				$this->updateCible();
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return $koCible;
	}

	private function majEvenement($braldun, $malus, $pvEnMoins, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a effectué un souffle de feu, touchant [b".$braldun["id_braldun"]."]";
		$detailsBot = $this->getDetailsBot($malus, $pvEnMoins, $jetMonstre, $jetBraldun);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($braldun["id_braldun"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $braldun["niveau_braldun"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $pvEnMoins, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a effectué un souffle de feu vous grillant quelques poils :";
		$retour .= PHP_EOL."Jet du Monstre (jet de force) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet de vigueur) : ".$jetBraldun;
		if ($jetBraldun > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté au Souffle, le malus a été divisé par 2.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté au Souffle.";
		}
		$retour .= PHP_EOL."Jet de Dégats du souffle : ".$malus;
		$retour .= PHP_EOL."Armure prise en compte, points de vie en moins : ".$pvEnMoins." PV";
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}