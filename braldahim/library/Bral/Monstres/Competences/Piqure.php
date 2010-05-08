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
class Bral_Monstres_Competences_Piqure extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$malus = Bral_Util_De::get_1d2();
		if ($malus <= 0) {
			$malus = 1;
		}
		$nbTours = Bral_Util_De::get_1d3() + 1;

		$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->monstre["agilite_base_monstre"]);
		$jetMonstre = $jetMonstre + $this->monstre["agilite_bm_monstre"];
		
		$jetBraldun = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->cible["agilite_base_braldun"]);
		$jetBraldun = $jetBraldun + $this->cible["agilite_bm_braldun"] + $this->cible["agilite_bbdf_braldun"];
		
		if ($jetBraldun > $jetMonstre) {
			$nbTours = 1;
		}

		Bral_Util_Effets::ajouteEtAppliqueEffetBraldun($this->cible["id_braldun"], Bral_Util_Effets::CARACT_AGILITE, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, 'Piqure aux Pieds');
		$this->majEvenement($this->cible, $malus, $nbTours, $jetMonstre, $jetBraldun);

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($braldun, $malus, $nbTours, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a piqué les pieds du Braldûn [b".$braldun["id_braldun"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($braldun["id_braldun"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $braldun["niveau_braldun"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous a piqué les pieds, vous avez été influencé :";
		$retour .= PHP_EOL."Jet du Monstre (jet d'agilité) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet d'agilité) : ".$jetBraldun;
		if ($jetBraldun > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté à la piqure, l'effet porte sur seulement 1 tour.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté à la piqure, l'effet porte sur plusieurs tours.";
		}
		$retour .= PHP_EOL."Malus sur votre agilité : -".$malus;
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}