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

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter - idm(".$this->monstre["id_monstre"].")");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$malus = floor($this->monstre["niveau_monstre"] / 4) - 3 + Bral_Util_De::get_1d6();
		if ($malus < 0) {
			$malus = 1;
		}
		$nbTours = 2;
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - idm(".$this->monstre["id_monstre"].") malus:".$malus);
		Bral_Util_Effets::ajouteEtAppliqueEffetBraldun($this->cible["id_braldun"], Bral_Util_Effets::CARACT_AGILITE, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, 'Coup de défense');
		$this->majEvenement($this->cible, $malus, $nbTours);

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit - idm(".$this->monstre["id_monstre"].")");
		return null;
	}

	private function majEvenement($braldun, $malus, $nbTours) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a donné un coup de défense sur le Braldûn [b".$braldun["id_braldun"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($braldun["id_braldun"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $braldun["niveau_braldun"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") vous a donné un coup de défense, vous avez été influencé :";
		$retour .= PHP_EOL."Malus sur votre agilité : -".$malus;
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}