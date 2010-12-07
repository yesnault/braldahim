<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Cheminement extends Bral_Monstres_Competences_Deplacement {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - enter - (idm:".$this->monstre["id_monstre"].")");
			
		// S'il y a une cible, direction sur la cible ==> Toutes les cases sont forcément creusées autour
		// par le feu des ténèbres

		// Le cheminement passe au dessus des palissades
		
		// Verification de la cible
		$cible = null;
		$verif = $this->verificationCible($cible);

		if ($verif === false) { // Cible sur la même case
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - cible sur même case - exit - (idm:".$this->monstre["id_monstre"].")");
			return;
		} elseif ($verif === true) { // Cible sur une case différente
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - cible sur une case différente - monstre:".$this->monstre["x_monstre"]." y:".$this->monstre["y_monstre"]." cible:".$cible["x_braldun"]." y:".$cible["y_braldun"]." (idm:".$this->monstre["id_monstre"].")");

			if ($this->monstre["x_monstre"] < $cible["x_braldun"]) {
				$this->monstre["x_monstre"] = $this->monstre["x_monstre"] + 1;
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - A - (idm:".$this->monstre["id_monstre"].")");
			} else if ($this->monstre["x_monstre"] > $cible["x_braldun"]) {
				$this->monstre["x_monstre"] = $this->monstre["x_monstre"] - 1;
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - B - (idm:".$this->monstre["id_monstre"].")");
			}

			if ($this->monstre["y_monstre"] < $cible["y_braldun"]) {
				$this->monstre["y_monstre"] = $this->monstre["y_monstre"] + 1;
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - C - (idm:".$this->monstre["id_monstre"].")");
			} else if ($this->monstre["y_monstre"] > $cible["y_braldun"]) {
				$this->monstre["y_monstre"] = $this->monstre["y_monstre"] - 1;
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - D - (idm:".$this->monstre["id_monstre"].")");
			}

		} else { // pas de cible
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - pas de cible - (idm:".$this->monstre["id_monstre"].")");

			$deltaX = Bral_Util_De::get1D3();
			$deltaY = Bral_Util_De::get1D3();

			$this->monstre["x_monstre"] = $this->monstre["x_monstre"] -2 + $deltaX;
			$this->monstre["y_monstre"] = $this->monstre["y_monstre"] -2 + $deltaY;
		}

		$this->monstre["x_direction_monstre"] = $this->monstre["x_monstre"];
		$this->monstre["y_direction_monstre"] = $this->monstre["y_monstre"];
			
		$suppressionPalissade = $this->supprimePalissade();
		
		$this->majEvenement($suppressionPalissade);
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA Monstre (".$this->monstre["id_monstre"].") avant action nb=".$this->monstre["pa_monstre"]);
		$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - $this->competence["pa_utilisation_mcompetence"];
		Bral_Util_Log::viemonstres()->debug(get_class($this)." - PA Monstre (".$this->monstre["id_monstre"].") apres action nb=".$this->monstre["pa_monstre"]);

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - exit - (idm:".$this->monstre["id_monstre"].")");
		return;
	}

	private function supprimePalissade() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - supprimePalissade - enter - (idm:".$this->monstre["id_monstre"].")");
		
		$retour = false;
		
		Zend_Loader::loadClass("Palissade");
		$palissadeTable = new Palissade();
		$palissade = $palissadeTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"]);
		if ($palissade != null && count($palissade) == 1) {
			if ($palissade[0]["est_destructible_palissade"] == "oui") {
				$retour = true;
				$where = "id_palissade=".$palissade[0]["id_palissade"];
				$palissadeTable->delete($where);
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - palissade supprimee - (idm:".$this->monstre["id_monstre"].")");
			} else {
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - palissade indestructible, non supprimée - (idm:".$this->monstre["id_monstre"].")");
			}
		} else {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - aucune palissade trouvee - (idm:".$this->monstre["id_monstre"].")");
		}
		
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - supprimePalissade - exit - (idm:".$this->monstre["id_monstre"].")");
		return $retour;
	}
	
	private function verificationCible(&$cible) {
		if ($this->monstre["id_fk_braldun_cible_monstre"] != null) {
			// S'il y a une cible,
			$braldunTable = new Braldun();
			$cibles = $braldunTable->findBraldunAvecRayon($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], $this->monstre["vue_monstre"], $this->monstre["id_fk_braldun_cible_monstre"], false);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - cible en cours - (idm:".$this->monstre["id_monstre"].")");
			if (count($cibles) == 1) {
				$cible = $cibles[0];
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - cible en cours trouvee:(idh:".$cible["id_braldun"].") - (idm:".$this->monstre["id_monstre"].")");
				if ($cible["x_braldun"] == $this->monstre["x_monstre"] &&
				$cible["y_braldun"] == $this->monstre["y_monstre"]) {
					Bral_Util_Log::viemonstres()->trace(get_class($this)." - Cheminement - cible sur la même case, pas de feu des tenebres - exit - (idm:".$this->monstre["id_monstre"].")");
					return false; // Cible sur la même case
				}
			}

		} else {
			return null; // pas de cible
		}

		return true; // cible sur une case différente
	}

	private function majEvenement($suppressionPalissade) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->effet;
		$details = "[m".$this->monstre["id_monstre"]."] chemine";
		if ($suppressionPalissade) {
			$details .= " et écrase une palissade";
		}
		$details .= "...";
		$detailsBot = "";
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $this->monstre["niveau_monstre"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}
}