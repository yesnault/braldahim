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
		if ($this->monstre["id_fk_braldun_cible_monstre"] != null) {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - cible en cours A");
			$braldunTable = new Braldun();
			$vue = $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"];
			if ($vue < 0) {
				$vue = 0;
			}

			$cibles = $braldunTable->findBraldunAvecRayon($this->monstre["x_monstre"], $this->monstre["y_monstre"], $vue, $this->monstre["id_fk_braldun_cible_monstre"], false);
			if (count($cibles) > 0) {
				foreach($cibles as $c) {
					if (Bral_Monstres_Competences_Reperage::peutAttaquer($c, $this->monstre)) {
						$cible = $c;
						break;
					}
				}

				Bral_Util_Log::viemonstres()->debug(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - cible trouvee:".$cible["id_braldun"]. " x=".$this->monstre["x_direction_monstre"]. " y=".$this->monstre["y_direction_monstre"]);
			} else {
				$this->monstre["id_fk_braldun_cible_monstre"] = null;
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - cible non trouvee x=".$this->monstre["x_direction_monstre"]. " y=".$this->monstre["y_direction_monstre"]);
			}
		} else { // pas de cible en cours
			$cible = null;
		}

		// si la cible n'est pas dans la vue, on en recherche une autre ou l'on se deplace
		if ($cible == null) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - (idm:".$this->monstre["id_monstre"].") - pas de cible en cours B");
			$this->monstre["id_fk_braldun_cible_monstre"] = null;
			$cible = self::rechercheNouvelleCible($this->monstre);
		} else {
			$this->monstre["x_direction_monstre"] = $cible["x_braldun"];
			$this->monstre["y_direction_monstre"] = $cible["y_braldun"];
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - reperageCible - exit - (idm:".$this->monstre["id_monstre"].")");
		return $cible;
	}

	public static function rechercheNouvelleCible(&$monstre, $vueForcee = null, $order = null) {
		Bral_Util_Log::viemonstres()->trace("rechercheNouvelleCible - enter - (idm:".$monstre["id_monstre"].")");
		$braldunTable = new Braldun();

		if ($vueForcee == null) {
			$vue = $monstre["vue_monstre"] + $monstre["vue_malus_monstre"];
			if ($vue < 0) {
				$vue = 0;
			}
		} else {
			$vue = $vueForcee;
		}

		Zend_Loader::loadClass("Bral_Util_Dijkstra");
		$dijkstra = new Bral_Util_Dijkstra();
		$dijkstra->calcul($vue, $monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"]);

		$x_min = $monstre["x_monstre"] - $vue;
		$x_max = $monstre["x_monstre"] + $vue;
		$y_min = $monstre["y_monstre"] - $vue;
		$y_max = $monstre["y_monstre"] + $vue;

		$tabValide = null;
		$numero = -1;
		for ($j = $y_max ; $j >= $y_min ; $j--) {
			for ($i = $x_min ; $i <= $x_max ; $i++) {
				$numero++;
				$tabValide[$i][$j] = true;
				if ($dijkstra->getDistance($numero) > $vue) {
					$tabValide[$i][$j] = false;
				}
			}
		}

		$tabValide[$monstre["x_monstre"]][$monstre["y_monstre"]] = false;

		$cible = null;
		$cibles = $braldunTable->findLesPlusProches($monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"], $vue, 10, $monstre["id_fk_type_monstre"], false, $order);

		if ($cibles != null) {
			shuffle($cibles);
			foreach($cibles as $c) {
				// on ne charge pas sur la case
				if (array_key_exists($c["x_braldun"], $tabValide) &&
				array_key_exists($c["y_braldun"], $tabValide[$c["x_braldun"]]) &&
				$tabValide[$c["x_braldun"]][$c["y_braldun"]] === true
				&& Bral_Monstres_Competences_Reperage::peutAttaquer($c, $monstre)) {
					$cible = $c; // controle cible OK
					break;
				} else {
					Bral_Util_Log::viemonstres()->debug("rechercheNouvelleCible - (idm:".$monstre["id_monstre"].") - cible non valide:".$c["id_braldun"]);
				}
			}
		}

		if ($cible != null) {
			Bral_Util_Log::viemonstres()->debug("rechercheNouvelleCible - (idm:".$monstre["id_monstre"].") - nouvelle cible trouvee:".$cible["id_braldun"]);
			$monstre["id_fk_braldun_cible_monstre"] = $cible["id_braldun"];
			$monstre["x_direction_monstre"] = $cible["x_braldun"];
			$monstre["y_direction_monstre"] = $cible["y_braldun"];
		} else {
			Bral_Util_Log::viemonstres()->debug("rechercheNouvelleCible - (idm:".$monstre["id_monstre"].") - aucune cible trouvee x=".$monstre["x_monstre"]." y=".$monstre["y_monstre"]." vue=".$monstre["vue_monstre"]);
			$cible = null;
		}
		Bral_Util_Log::viemonstres()->trace("rechercheNouvelleCible - (idm:".$monstre["id_monstre"].") - exit");
		return $cible;
	}
}