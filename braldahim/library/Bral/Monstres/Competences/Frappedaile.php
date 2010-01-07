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
class Bral_Monstres_Competences_Frappedaile extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$x_min = $this->monstre["x_monstre"];
		$y_min = $this->monstre["y_monstre"];
		$x_max = $this->monstre["x_monstre"];
		$y_max = $this->monstre["y_monstre"];
		$z = $this->monstre["z_monstre"];

		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($x_min, $y_min, $x_max, $y_max, $z, -1, false);

		$koCible = false;

		if ($hobbits != null) {
			foreach($hobbits as $h) {
				$malus = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->monstre["force_base_monstre"]);
				$malus = $malus + $this->monstre["force_bm_monstre"];
				$malus = floor($malus / 2);

				if ($malus <= 2) {
					$malus = 2;
				}

				$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->monstre["force_base_monstre"]);
				$jetMonstre = $jetMonstre + $this->monstre["force_bm_monstre"];

				$jetHobbit = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $h["agilite_base_hobbit"]);
				$jetHobbit = $jetHobbit + $h["agilite_bm_hobbit"] + $h["agilite_bbdf_hobbit"];

				if ($jetHobbit > $jetMonstre) {
					$malus = floor($malus / 2);
				}

				$this->majEvenement($h, $malus, $jetMonstre, $jetHobbit);

				$h["pv_restant_hobbit"] = $h["pv_restant_hobbit"] - $malus;
				$this->cible = $h;
				if ($h["pv_restant_hobbit"] <= 0) {
					if ($this->cible["id_hobbit"] == $this->monstre["id_fk_hobbit_cible_monstre"]) {
						$koCible = true;
					}
					$details = $this->initKo();
					$detailsBot = "Vous avez perdu ".$malus." PV par les frappes des ailes, vous êtes KO.";
					$id_type_evenement_cible = self::$config->game->evenements->type->ko;
					Bral_Util_Evenement::majEvenementsFromVieMonstre($h["id_hobbit"], null, $id_type_evenement_cible, $details, $detailsBot, $h["niveau_hobbit"], $this->view);
				}

				$this->updateCible();
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return $koCible;
	}

	private function majEvenement($hobbit, $malus, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] frappe avec ses ailes le hobbit [h".$hobbit["id_hobbit"]."]";
		$detailsBot = $this->getDetailsBot($malus, $jetMonstre, $jetHobbit);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $hobbit["niveau_hobbit"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous frappe avec ses ailes :";
		$retour .= PHP_EOL."Jet du Monstre (jet de force) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet d'agilite) : ".$jetHobbit;
		if ($jetHobbit > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté aux frappes, le malus a été divisé par 2.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté aux frappes.";
		}
		$retour .= PHP_EOL."Points de vie en moins : ".$malus." PV";
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}