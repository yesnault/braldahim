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
class Bral_Monstres_Competences_Frappepoitrine extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$x_min = $this->monstre["x_monstre"] - 1;
		$y_min = $this->monstre["y_monstre"] - 1;
		$x_max = $this->monstre["x_monstre"] + 1;
		$y_max = $this->monstre["y_monstre"] + 1;
		$z = $this->monstre["z_monstre"];

		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($x_min, $y_min, $x_max, $y_max, $z, -1, false);

		$koCible = false;

		if ($hobbits != null) {
			foreach($hobbits as $h) {
				$malus = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->monstre["force_base_monstre"]);
				$malus = $malus + $this->monstre["force_bm_monstre"];
				$malus = floor($malus / 4);

				if ($malus <= 2) {
					$malus = 2;
				}

				$nbTours = Bral_Util_De::get_1d3();

				$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $this->monstre["force_base_monstre"]);
				$jetMonstre = $jetMonstre + $this->monstre["force_bm_monstre"];

				$jetHobbit = Bral_Util_De::getLanceDe6(self::$config->game->base_sagesse + $h["sagesse_base_hobbit"]);
				$jetHobbit = $jetHobbit + $h["sagesse_bm_hobbit"] + $h["sagesse_bbdf_hobbit"];

				if ($jetHobbit > $jetMonstre) {
					$malus = floor($malus / 2);
				}
				
				Bral_Util_Effets::ajouteEtAppliqueEffetHobbit($this->cible["id_hobbit"], Bral_Util_Effets::CARACT_FORCE, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, "Gorille frappant sa poitrine");
				$this->majEvenement($this->cible, $malus, $nbTours, $jetMonstre, $jetHobbit);
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return $koCible;
	}

	private function majEvenement($hobbit, $malus, $nbTours, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] frappe sur sa poitrine, le hobbit [h".$hobbit["id_hobbit"]."] tremble";
		$detailsBot = $this->getDetailsBot($malus, $nbTours, $jetMonstre, $jetHobbit);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $hobbit["niveau_hobbit"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") frappe sur sa poitrine, vous tremblez :";
		$retour .= PHP_EOL."Jet du Monstre (jet de force) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet de sagesse) : ".$jetHobbit;
		if ($jetHobbit > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté au tremblements, le malus a été divisé par 2.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté aux tremblements.";
		}
		$retour .= PHP_EOL."Malus sur votre force : -".$malus;
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}