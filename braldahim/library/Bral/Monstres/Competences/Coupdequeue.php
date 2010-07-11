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
class Bral_Monstres_Competences_Coupdequeue extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$malus = $this->monstre["niveau_monstre"] - 3 + Bral_Util_De::get_1d6();
		if ($malus <= 0) {
			$malus = 1;
		}

		$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_vigueur + $this->monstre["vigueur_base_monstre"]);
		$jetMonstre = $jetMonstre + $this->monstre["vigueur_bm_monstre"];

		$jetBraldun = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->cible["force_base_braldun"]);
		$jetBraldun = $jetBraldun + $this->cible["force_bm_braldun"] + $this->cible["force_bbdf_braldun"];

		if ($jetBraldun > $jetMonstre) {
			$malus = 0;
		}

		$armureTotale = $this->cible["armure_naturelle_braldun"] + $this->cible["armure_equipement_braldun"] + $this->cible["armure_bm_braldun"];
		if ($armureTotale < 0) {
			$armureTotale = 0;
		}
		$pvEnMoins = $malus -  $armureTotale;
		if ($pvEnMoins < 0) {
			$pvEnMoins = 0;
		}
		$this->cible["pv_restant_braldun"] = $this->cible["pv_restant_braldun"] - $pvEnMoins;

		if ($this->cible["pv_restant_braldun"] <= 0) {
			if ($this->cible["id_braldun"] == $this->monstre["id_fk_braldun_cible_monstre"]) {
				$koCible = true;
			}
			$details = $this->initKo();
			$detailsBot = $this->getDetailsBot($malus, $pvEnMoins, $jetMonstre, $jetBraldun);
			$detailsBot .= PHP_EOL."Vous êtes KO.";
			$id_type_evenement_cible = self::$config->game->evenements->type->ko;
			Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_braldun"], null, $id_type_evenement_cible, $details, $detailsBot, $this->cible["niveau_braldun"], $this->view);
		} else {
			$this->majEvenement($this->cible, $malus, $pvEnMoins, $jetMonstre, $jetBraldun);
		}

		$this->updateCible();

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($braldun, $malus, $pvEnMoins, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a donné un coup de queue sur [b".$braldun["id_braldun"]."]";
		$detailsBot = $this->getDetailsBot($malus, $pvEnMoins, $jetMonstre, $jetBraldun);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($braldun["id_braldun"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $braldun["niveau_braldun"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $pvEnMoins, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous a donné un Coup de Queue :";
		$retour .= PHP_EOL."Jet du Monstre (jet de vigueur) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet de force) : ".$jetBraldun;
		if ($jetBraldun > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté au coup, vous ne perdez pas de Point de Vie.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté au coup, vous perdez des Points de Vie.";
			$retour .= PHP_EOL."Jet de Dégats du coup : ".$malus;
			$retour .= PHP_EOL."Armure prise en compte, points de vie en moins : ".$pvEnMoins." PV";
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}