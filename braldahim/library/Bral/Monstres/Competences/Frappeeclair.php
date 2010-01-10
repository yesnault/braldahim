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
class Bral_Monstres_Competences_Frappeeclair extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$malus = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->monstre["agilite_base_monstre"]);
		$malus = $malus + $this->monstre["agilite_bm_monstre"];
		$malus = floor($malus / 2);
		if ($malus <= 2) {
			$malus = 2;
		}
		$nbTours = Bral_Util_De::get_1d3();

		$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->monstre["agilite_base_monstre"]);
		$jetMonstre = $jetMonstre + $this->monstre["agilite_bm_monstre"];

		$jetHobbit = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->cible["force_base_hobbit"]);
		$jetHobbit = $jetHobbit + $this->cible["force_bm_hobbit"] + $this->cible["force_bbdf_hobbit"];

		if ($jetHobbit > $jetMonstre) {
			$malus = floor($malus / 2);
		}

		$this->majEvenement($this->cible, $malus, $jetMonstre, $jetHobbit);

		$this->cible["pv_restant_hobbit"] = $this->cible["pv_restant_hobbit"] - $malus;
		$koCible = false;
		if ($this->cible["pv_restant_hobbit"] <= 0) {
			if ($this->cible["id_hobbit"] == $this->monstre["id_fk_hobbit_cible_monstre"]) {
				$koCible = true;
			}
			$details = $this->initKo();
			$detailsBot = "Vous avez perdu ".$malus." PV par une frappe éclair, vous êtes KO.";
			$id_type_evenement_cible = self::$config->game->evenements->type->ko;
			Bral_Util_Evenement::majEvenementsFromVieMonstre($this->cible["id_hobbit"], null, $id_type_evenement_cible, $details, $detailsBot, $this->cible["niveau_hobbit"], $this->view);
		}

		$this->updateCible();

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return $koCible;
	}

	private function majEvenement($hobbit, $malus, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] envoie une frappe éclair sur le hobbit [h".$hobbit["id_hobbit"]."]";
		$detailsBot = $this->getDetailsBot($malus, $jetMonstre, $jetHobbit);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $hobbit["niveau_hobbit"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous envoie une frappe éclair :";
		$retour .= PHP_EOL."Jet du Monstre (jet de force) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet d'agilite) : ".$jetHobbit;
		if ($jetHobbit > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté à la frappe, le malus a été divisé par 2.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté à la frappe.";
		}
		$retour .= PHP_EOL."Points de vie en moins : ".$malus." PV";
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}