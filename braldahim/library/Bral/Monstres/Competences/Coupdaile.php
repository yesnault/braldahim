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
class Bral_Monstres_Competences_Coupdaile extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		$malus = 1;
		$nbTours = Bral_Util_De::get_1d3();

		$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->monstre["agilite_base_monstre"]);
		$jetMonstre = $jetMonstre + $this->monstre["agilite_bm_monstre"];

		$jetHobbit = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->cible["agilite_base_hobbit"]);
		$jetHobbit = $jetHobbit + $this->cible["agilite_bm_hobbit"] + $this->cible["agilite_bbdf_hobbit"];

		if ($jetHobbit <= $jetMonstre) {
			Zend_Loader::loadClass("Bral_Util_Effets");
			Bral_Util_Effets::ajouteEtAppliqueEffetHobbit($this->cible["id_hobbit"], Bral_Util_Effets::CARACT_VUE, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, "Coup d'aile sur le nez");
		}
		$this->majEvenement($this->cible, $malus, $nbTours, $jetMonstre, $jetHobbit);

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($hobbit, $malus, $nbTours, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a donné un Coup d'Aile sur le nez du hobbit [h".$hobbit["id_hobbit"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours, $jetMonstre, $jetHobbit);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $hobbit["niveau_hobbit"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous a donné un Coup d'Aile sur le nez :";
		$retour .= PHP_EOL."Jet du Monstre (jet d'agilité) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet d'agilité) : ".$jetHobbit;
		if ($jetHobbit > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté au Coup d'aile, aucun malus n'est appliqué.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté au Coup d'aile, le malus est appliqué.";
			$retour .= PHP_EOL."Malus sur votre vue : -".$malus;
			$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}