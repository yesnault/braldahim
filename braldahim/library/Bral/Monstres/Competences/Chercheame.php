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
class Bral_Monstres_Competences_Chercheame extends Bral_Monstres_Competences_Reperage {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Chercheame - enter - (idm:".$this->monstre["id_monstre"].")");
		$cible = null;

		$order = array("pv_restant_hobbit ASC");
			
		// on regarde s'il y a une cible en cours
		if ($this->monstre["id_fk_hobbit_cible_monstre"] != null) {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - cible en cours A");
			$hobbitTable = new Hobbit();
			$vue = $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"];
			if ($vue < 0) {
				$vue = 0;
			}

			$cible = $hobbitTable->findHobbitAvecRayon($this->monstre["x_monstre"], $this->monstre["y_monstre"], $vue, $this->monstre["id_fk_hobbit_cible_monstre"], false, $order);
			if (count($cible) > 0) {
				$cible = $cible[0];
				$this->monstre["x_direction_monstre"] = $cible["x_hobbit"];
				$this->monstre["y_direction_monstre"] = $cible["y_hobbit"];
				// et l'on se positionne directement sur la cible
				$this->monstre["x_monstre"] = $cible["x_hobbit"];
				$this->monstre["y_monstre"] = $cible["y_hobbit"];
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - cible trouvee:".$cible["id_hobbit"]. " x=".$this->monstre["x_direction_monstre"]. " y=".$this->monstre["y_direction_monstre"]);
			} else {
				$this->monstre["id_fk_hobbit_cible_monstre"] = null;
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - cible non trouvee x=".$this->monstre["x_direction_monstre"]. " y=".$this->monstre["y_direction_monstre"]);
			}
		} else { // pas de cible en cours
			$cible = null;
		}

		// si la cible n'est pas dans la vue, on en recherche une autre ou l'on se deplace
		if ($cible == null) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - pas de cible en cours B");
			$this->monstre["id_fk_hobbit_cible_monstre"] = null;
			Zend_Loader::loadclass("Bral_Monstres_Competences_Reperagestandard");
			$cible = Bral_Monstres_Competences_Reperagestandard::rechercheNouvelleCible($this->monstre, null, $order);
			// et l'on se positionne directement sur la cible
			if ($cible != null) {
				$this->monstre["x_monstre"] = $cible["x_hobbit"];
				$this->monstre["y_monstre"] = $cible["y_hobbit"];
			}
		}
			
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Chercheame - exit - (idm:".$this->monstre["id_monstre"].")");
		return $cible;
	}

}