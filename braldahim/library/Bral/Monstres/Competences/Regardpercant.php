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
class Bral_Monstres_Competences_Regardpercant extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$nbTours = 1;

		$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_sagesse + $this->monstre["sagesse_base_monstre"]);
		$jetMonstre = $jetMonstre + $this->monstre["sagesse_bm_monstre"];

		$jetHobbit = Bral_Util_De::getLanceDe6(self::$config->game->base_sagesse + $this->cible["sagesse_base_hobbit"]);
		$jetHobbit = $jetHobbit + $this->cible["sagesse_bm_hobbit"] + $this->cible["sagesse_bbdf_hobbit"];

		if ($jetHobbit > $jetMonstre) {
			$coef = 2;
		} else {
			$coef = 5;
		}

		$malus = $this->monstre["niveau_monstre"] * $coef + Bral_Util_De::get_1d10();
		if ($malus <= 0) {
			$malus = 1;
		}

		Bral_Util_Effets::ajouteEtAppliqueEffetHobbit($this->cible["id_hobbit"], Bral_Util_Effets::CARACT_DUREE_TOUR, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, 'Regard Perçant');
		$this->majEvenement($this->cible, $malus, $nbTours, $jetMonstre, $jetHobbit);

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($hobbit, $malus, $nbTours, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a lancé un regard perçant sur le hobbit [h".$hobbit["id_hobbit"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours, $jetMonstre, $jetHobbit);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $hobbit["niveau_hobbit"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetHobbit) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous a lancé un regard perçant:";
		$retour .= PHP_EOL."Jet du Monstre (jet de sagesse) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet de sagesse) : ".$jetHobbit;
		if ($jetHobbit > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté au regard, la malus est diminué.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté au regard.";
		}
		$retour .= PHP_EOL."Malus sur votre tour (en minutes) : ".$malus. ' min.';
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}