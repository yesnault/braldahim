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
class Bral_Monstres_Competences_Coupdefense extends Bral_Monstres_Competences_Attaque {
	
	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], -1, false);

		if ($hobbits != null) {
			foreach($hobbits as $h) {
				$malus = floor($this->monstre["niveau_monstre"] / 4);
				$nbTours = 2;
				Bral_Util_Effets::ajouteEtAppliqueEffet($h["id_hobbit"], Bral_Util_Effets::CARACT_AGILITE, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus);
				$this->majEvenement($h, $malus, $nbTours);
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($hobbit, $malus, $nbTours) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a barri sur le hobbit [h".$hobbit["id_hobbit"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $hobbit["niveau_hobbit"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	private function getDetailsBot($malus, $nbTours) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous a donné un coup de défense, vous avez été influencé :";
		$retour .= PHP_EOL."Malus sur votre agilité : ".$malus;
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}