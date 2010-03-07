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
class Bral_Monstres_Competences_Reperagestandard extends Bral_Monstres_Competences_Reperage {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - reperageCible - enter - (idm:".$this->monstre["id_monstre"].")");
		$cible = null;
			
		// on regarde s'il y a une cible en cours
		if ($this->monstre["id_fk_hobbit_cible_monstre"] != null) {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - cible en cours A");
			$hobbitTable = new Hobbit();
			$vue = $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"];
			if ($vue < 0) {
				$vue = 0;
			}

			$cible = $hobbitTable->findHobbitAvecRayon($this->monstre["x_monstre"], $this->monstre["y_monstre"], $vue, $this->monstre["id_fk_hobbit_cible_monstre"], false);
			if (count($cible) > 0) {
				$cible = $cible[0];
				$this->monstre["x_direction_monstre"] = $cible["x_hobbit"];
				$this->monstre["y_direction_monstre"] = $cible["y_hobbit"];
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
			$cible = self::rechercheNouvelleCible($this->monstre);
		}
			
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - reperageCible - exit - (idm:".$this->monstre["id_monstre"].")");
		return $cible;
	}

	public static function rechercheNouvelleCible(&$monstre, $vueForcee = null, $order = null) {
		Bral_Util_Log::viemonstres()->trace("rechercheNouvelleCible - enter - (idm:".$monstre["id_monstre"].")");
		$hobbitTable = new Hobbit();

		if ($vueForcee == null) {
			$vue = $monstre["vue_monstre"] + $monstre["vue_malus_monstre"];
			if ($vue < 0) {
				$vue = 0;
			}
		} else {
			$vue = $vueForcee;
		}
			
		$cibles = $hobbitTable->findLesPlusProches($monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"], $vue, 1, $monstre["id_fk_type_monstre"], false, $order);
		if ($cibles != null) {
			$cible = $cibles[0];
			Bral_Util_Log::viemonstres()->debug("rechercheNouvelleCible - (idm:".$monstre["id_monstre"].") - nouvelle cible trouvee:".$cible["id_hobbit"]);
			$monstre["id_fk_hobbit_cible_monstre"] = $cible["id_hobbit"];
			$monstre["x_direction_monstre"] = $cible["x_hobbit"];
			$monstre["y_direction_monstre"] = $cible["y_hobbit"];
		} else {
			Bral_Util_Log::viemonstres()->debug("rechercheNouvelleCible - (idm:".$monstre["id_monstre"].") - aucune cible trouvee x=".$monstre["x_monstre"]." y=".$monstre["y_monstre"]." vue=".$monstre["vue_monstre"]);
			$cible = null;
		}
		Bral_Util_Log::viemonstres()->trace("rechercheNouvelleCible - (idm:".$monstre["id_monstre"].") - exit");
		return $cible;
	}
}