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
class Bral_Monstres_Competences_Petnauseabond extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}
	
	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$malus = $this->monstre["niveau_monstre"];
		$nbTours = 1;
		Bral_Util_Effets::ajouteEtAppliqueEffet($this->cible["id_hobbit"], Bral_Util_Effets::CARACT_BBDF, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus);
		$this->majEvenement($this->cible, $malus, $nbTours);

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($hobbit, $malus, $nbTours) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a effectué un pet nauséabond, retournant l'estomac du hobbit [h".$hobbit["id_hobbit"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($hobbit["id_hobbit"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $hobbit["niveau_hobbit"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a effectué un pet nauséabond, vous retournant l'estomac :";
		$retour .= PHP_EOL."Balance de faim : -".$malus."%";
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}