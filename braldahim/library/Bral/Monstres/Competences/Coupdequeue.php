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

		$jetHobbit = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->cible["force_base_hobbit"]);
		$jetHobbit = $jetHobbit + $this->cible["force_bm_hobbit"] + $this->cible["force_bbdf_hobbit"];

		if ($jetHobbit > $jetMonstre) {
			$malus = 0;
		}

		$pvEnMoins = $malus -  $this->cible["armure_naturelle_hobbit"] - $this->cible["armure_equipement_hobbit"];
		if ($pvEnMoins < 1) {
			$pvEnMoins = 1;
		}
		$this->cible["pv_restant_hobbit"] = $this->cible["pv_restant_hobbit"] - $pvEnMoins;

		if ($this->cible["pv_restant_hobbit"] <= 0) {
			if ($this->cible["id_hobbit"] == $this->monstre["id_fk_hobbit_cible_monstre"]) {
				$koCible = true;
			}
			$details = $this->initKo();
			$detailsBot = $this->getDetailsBot($malus, $pvEnMoins, $jetMonstre, $jetHobbit);
			$detailsBot .= PHP_EOL."Vous êtes KO.";
			$id_type_evenement_cible = self::$config->game->evenements->type->ko;
			Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_hobbit"], null, $id_type_evenement_cible, $details, $detailsBot, $this->cible["niveau_hobbit"], $this->view);
		} else {
			$this->majEvenement($this->cible, $malus, $pvEnMoins, $jetMonstre, $jetHobbit);
		}

		$this->updateCible();

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($hobbit, $malus, $pvEnMoins, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a donné un coup de queue sur le hobbit [h".$hobbit["id_hobbit"]."]";
		$detailsBot = $this->getDetailsBot($malus, $pvEnMoins, $jetMonstre, $jetHobbit);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $hobbit["niveau_hobbit"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $pvEnMoins, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous a donné un Coup de Queue :";
		$retour .= PHP_EOL."Jet du Monstre (jet de vigueur) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet de force) : ".$jetHobbit;
		if ($jetHobbit > $jetMonstre) {
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