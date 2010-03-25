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
class Bral_Monstres_Competences_Voletresor extends Bral_Monstres_Competences_Reperage {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Voletresor - enter - (idm:".$this->monstre["id_monstre"].")");
			
		Zend_Loader::loadClass("ElementRune");
		$elementRuneTable = new ElementRune();
		$runes = $elementRuneTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"]);

		Zend_Loader::loadClass("Element");
		$elementTable = new Element();
		$castars = $elementTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], "quantite_castar_element > 0");

		if (count($runes) > 0 && count($castars) > 0) {
			$choix = Bral_Util_De::get_1d2();
			if ($choix == 1) {
				$this->voleRune($runes);
			} else {
				$this->voleCastars();
			}
		} elseif (count($runes) > 0) {
			$this->voleRune($runes);
		} elseif (count($castars) > 0) {
			$this->voleCastars();
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Voletresor - exit - (idm:".$this->monstre["id_monstre"].")");
		return;
	}

	private function voleRune($runes) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Voletresor - voleRune - enter - (idm:".$this->monstre["id_monstre"].")");
		shuffle($runes);

		$rune = $runes[0];
		$where = "id_rune_element_rune=".$rune["id_rune"];
		$departRuneTable = new ElementRune();
		$departRuneTable->delete($where);

		$this->majEvenement();

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Voletresor - voleRune - exit - (idm:".$this->monstre["id_monstre"].")");
	}

	private function voleCastars() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Voletresor - voleCastars - enter - (idm:".$this->monstre["id_monstre"].")");

		$nbCastars = $this->monstre["niveau_monstre"] * 2 + Bral_Util_De::get_1d10(); //NivM*2+1D10

		$elementTable = new Element();
		$data = array(
			"quantite_castar_element" => -$nbCastars,
			"x_element" => $this->monstre["x_monstre"],
			"y_element" => $this->monstre["y_monstre"],
			"z_element" => $this->monstre["z_monstre"],
		);
		$elementTable->insertOrUpdate($data);

		$this->majEvenement();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Voletresor - voleCastars - exit - (idm:".$this->monstre["id_monstre"].")");
	}


	private function majEvenement() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->effet;
		$details = "[m".$this->monstre["id_monstre"]."] mange un trésor resté au sol";
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, "", $this->monstre["niveau_monstre"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}
}