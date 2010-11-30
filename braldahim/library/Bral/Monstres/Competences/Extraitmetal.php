<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Extraitmetal extends Bral_Monstres_Competences_Postall {

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Extraitmetal - enter - (idm:".$this->monstre["id_monstre"].")");

		Zend_Loader::loadClass("Filon");
		$filonTable = new Filon();
		$filons = $filonTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"]);

		if (count($filons) > 0) { // si l'on est sur un filon
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Extraitmetal - (idm:".$this->monstre["id_monstre"].") - filon trouve sur la case");
			$this->extraitmetal($filons[0]);
			$this->monstre["pa_monstre"] = $this->monstre["pa_monstre"] - $this->competence["pa_utilisation_mcompetence"];
		} else {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Extraitmetal - (idm:".$this->monstre["id_monstre"].") - filon non trouve sur la case");
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Extraitmetal - exit - (idm:".$this->monstre["id_monstre"].")");
		return;
	}

	private function extraitmetal($filon) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Extraitmetal - enter - (idm:".$this->monstre["id_monstre"].")");

		$filonTable = new Filon();
		$nbRestant = $filon["quantite_restante_filon"] - $this->monstre["niveau_monstre"];
		if ($nbRestant <= 0) {
			$where = 'id_filon='.$filon["id_filon"];
			$filonTable->delete($where);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Extraitmetal - Suppression du filon ".$filon["id_filon"]." (idm:".$this->monstre["id_monstre"].")");
		} else {
			$data = array(
				'quantite_restante_filon' =>  $nbRestant,
			);
			$where = 'id_filon='.$filon["id_filon"];
			$filonTable->update($data, $where);
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Extraitmetal - MAJ du filon ".$filon["id_filon"]." nbRestant=".$nbRestant." (idm:".$this->monstre["id_monstre"].")");
		}

		$this->majEvenement();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Extraitmetal - exit - (idm:".$this->monstre["id_monstre"].")");
	}

	private function majEvenement() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->effet;
		$details = "[m".$this->monstre["id_monstre"]."] extrait du métal";
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, "", $this->monstre["niveau_monstre"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}
}