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
class Bral_Monstres_Competences_Picorer extends Bral_Monstres_Competences_Prereperage {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Picorer - enter - (idm:".$this->monstre["id_monstre"].")");

		$retour = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD;

		$this->enchainerAvecReperageStandard = false;

		Zend_Loader::loadClass("Champ");
		$champTable = new Champ();
		$champs = $champTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], null, 'a_recolter');

		if (count($champs) > 0) { // si l'on est sur un champ
			// --> picorer
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Picorer - (idm:".$this->monstre["id_monstre"].") - champ trouve sur la case");
			$this->picorer($champs[0]);
			$retour = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_CASE;
		} else { // recherche de champs
			$vue = $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"];
			if ($vue < 0) {
				$vue = 0;
			}
			$x_min = $this->monstre["x_monstre"] - $vue;
			$y_min = $this->monstre["y_monstre"] - $vue;
			$x_max = $this->monstre["x_monstre"] + $vue;
			$y_max = $this->monstre["y_monstre"] + $vue;
			$champs = $champTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->monstre["z_monstre"], 'a_recolter');
			if (count($champs) > 0) {
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Picorer - (idm:".$this->monstre["id_monstre"].") - champ trouve dans la vue");
				shuffle($champs);
				$champ = $champs[0];
				$this->monstre["x_direction_monstre"] = $champ["x_champ"];
				$this->monstre["y_direction_monstre"] = $champ["y_champ"];
				$retour = Bral_Monstres_Competences_Prereperage::SUITE_DEPLACEMENT;
			} else { // pas de champs trouvés
				$this->enchainerAvecReperageStandard = true;
				$retour = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD;
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Picorer - (idm:".$this->monstre["id_monstre"].") - champ non trouve dans la vue");
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Picorer - exit - (idm:".$this->monstre["id_monstre"].") - retour:".$retour);
		return $retour;
	}

	private function picorer($champ) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Picorer - enter - (idm:".$this->monstre["id_monstre"].")");

		$champTable = new Champ();
		$nbRestant = $champ["quantite_champ"] - Bral_Util_De::get_1d2();
		if ($nbRestant <= 0) {
			$nbRestant = 1;
		}
		$data = array(
			'quantite_champ' =>  $nbRestant,
		);

		$where = 'id_champ='.$champ["id_champ"];
		$champTable->update($data, $where);

		$this->majEvenement();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Picorer - exit - (idm:".$this->monstre["id_monstre"].")");
	}

	public function enchainerAvecReperageStandard() {
		return $this->enchainerAvecReperageStandard;
	}

	private function majEvenement() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->effet;
		$details = "[m".$this->monstre["id_monstre"]."] picore un champ";
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, "", $this->monstre["niveau_monstre"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}
}