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
class Bral_Monstres_Competences_Froidglacial extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], -1, false);

		if ($hobbits != null) {
			foreach($hobbits as $h) {
				$malus = Bral_Util_De::getLanceDe6(self::$config->game->base_sagesse + $this->monstre["sagesse_base_monstre"]);
				$malus = floor(($malus + $this->monstre["sagesse_bm_monstre"]) / 2);

				if ($malus <= 0) {
					$malus = 1;
				}

				$nbTours = Bral_Util_De::get_1d3();

				$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_sagesse + $this->monstre["sagesse_base_monstre"]);
				$jetMonstre = $jetMonstre + $this->monstre["sagesse_bm_monstre"];

				$jetHobbit = Bral_Util_De::getLanceDe6(self::$config->game->base_force + $h["force_base_hobbit"]);
				$jetHobbit = $jetHobbit + $h["force_bm_hobbit"] + $h["force_bbdf_hobbit"];

				if ($jetHobbit > $jetMonstre) {
					$malus = floor($malus / 2);
				}

				Bral_Util_Effets::ajouteEtAppliqueEffetHobbit($h["id_hobbit"], Bral_Util_Effets::CARACT_ARMURE, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, 'Froid glacial');
				$this->majEvenement($h, $malus, $nbTours, $jetMonstre, $jetHobbit);
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($hobbit, $malus, $nbTours, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] lance un froid glacial sur le hobbit [h".$hobbit["id_hobbit"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours, $jetMonstre, $jetHobbit);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $hobbit["niveau_hobbit"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous lance un froid glacial :";
		$retour .= PHP_EOL."Jet du Monstre (jet de sagesse) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet de force) : ".$jetHobbit;
		if ($jetHobbit > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté au froid, le malus est divisé par 2.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté au froid.";
		}
		$retour .= PHP_EOL."Malus sur votre armure : -".$malus;
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}