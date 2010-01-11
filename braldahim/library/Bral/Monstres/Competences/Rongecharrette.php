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
class Bral_Monstres_Competences_Rongecharrette extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();

		$x = $this->monstre["x_monstre"];
		$y = $this->monstre["y_monstre"];
		$z = $this->monstre["z_monstre"];
		$charrettes = $charretteTable->findByCase($x, $y, $z);
		$charrettesavecHobbit = $charretteTable->findByPositionAvecHobbit($x, $y, $z);

		if (count($charrettes) > 0 && count($charrettesavecHobbit) > 0) {
			if (Bral_Util_De::get_1d2() == 1) {
				shuffle($charrettes);
				$this->updateCharrette($charrettes[0]);
			} else {
				shuffle($charrettesavecHobbit);
				$this->updateCharrette($charrettesavecHobbit[0]);
			}
		} elseif (count($charrettes) > 0) {
			shuffle($charrettes);
			$this->updateCharrette($charrettes[0]);
		} elseif (count($charrettesavecHobbit) > 0) {
			shuffle($charrettesavecHobbit);
			$this->updateCharrette($charrettesavecHobbit[0]);
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function updateCharrette($charrette) {
		$charretteTable = new Charrette();
		$data = array("durabilite_actuelle_charrette" => $charrette["durabilite_actuelle_charrette"] - 1);
		$where = "id_charrette = ".$charrette["id_charrette"];
		$charretteTable->update($data, $where);

		$this->majEvenement($charrette["id_charrette"], $charrette["id_fk_hobbit_charrette"]);
	}

	private function majEvenement($idCharrette, $idHobbit = null) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;

		$details = "[m".$this->monstre["id_monstre"]."] ronge la [t.".$idCharrette."]";
		$detailsBot = "";
		if ($idHobbit != null) {
			$details .= " portée par [h".$idHobbit."].";
			$detailsBot = "Le Rat a rongé votre charrette, elle perd 1 point en durabilité."; 
		}

		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, "", $this->monstre["niveau_monstre"], $this->view);
		Zend_loader::loadClass("Bral_Util_Materiel");
		Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_ATTAQUER_ID, $details, $details);

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}
}