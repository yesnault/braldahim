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
class Bral_Monstres_Competences_Crisstridents extends Bral_Monstres_Competences_Attaque {

	public function calculJetAttaque(){}
	public function calculDegat($estCritique){}

	public function actionSpecifique() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - enter");

		Zend_Loader::loadClass("Bral_Util_Effets");

		$nbTours = 1;

		$x_min = $this->monstre["x_monstre"];
		$y_min = $this->monstre["y_monstre"];
		$x_max = $this->monstre["x_monstre"];
		$y_max = $this->monstre["y_monstre"];
		$z = $this->monstre["z_monstre"];

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->selectVue($x_min, $y_min, $x_max, $y_max, $z, -1, false);

		$koCible = false;

		if ($bralduns != null) {
			foreach($bralduns as $h) {
				$jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_sagesse + $this->monstre["sagesse_base_monstre"]);
				$jetMonstre = $jetMonstre + $this->monstre["sagesse_bm_monstre"];

				$jetBraldun = Bral_Util_De::getLanceDe6(self::$config->game->base_sagesse + $this->cible["sagesse_base_braldun"]);
				$jetBraldun = $jetBraldun + $this->cible["sagesse_bm_braldun"] + $this->cible["sagesse_bbdf_braldun"];

				$malus = $this->monstre["niveau_monstre"] / 2 + Bral_Util_De::get_1d10();
				if ($malus <= 0) {
					$malus = 1;
				}
				
				$nbTours = 1;

				Bral_Util_Effets::ajouteEtAppliqueEffetBraldun($this->cible["id_braldun"], Bral_Util_Effets::CARACT_DUREE_TOUR, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, 'Cris stridents');
				if ($jetBraldun <= $jetMonstre) {
					$malusVue = 1;
					Bral_Util_Effets::ajouteEtAppliqueEffetBraldun($this->cible["id_braldun"], Bral_Util_Effets::CARACT_VUE, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malusVue, 'Cris stridents');
				}
				$this->majEvenement($this->cible, $malus, $nbTours, $jetMonstre, $jetBraldun);
			}
		}

		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - actionSpecifique - exit");
		return null;
	}

	private function majEvenement($braldun, $malus, $nbTours, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - enter");
		$idTypeEvenement = self::$config->game->evenements->type->attaquer;
		$details = "[m".$this->monstre["id_monstre"]."] a effectué un Cris Strident, influant [b".$braldun["id_braldun"]."]";
		$detailsBot = $this->getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun);
		Bral_Util_Evenement::majEvenementsFromVieMonstre($braldun["id_braldun"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $braldun["niveau_braldun"], $this->view);
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - majEvenement - exit");
	}

	protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - enter");
		$retour = "";
		$retour .= $this->monstre["nom_type_monstre"] ." (".$this->monstre["id_monstre"].") a effectué un Cris Strident vous influançant :";
		$retour .= PHP_EOL."Jet du Monstre (jet de sagesse) : ".$jetMonstre;
		$retour .= PHP_EOL."Jet de résistance (jet de sagesse) : ".$jetBraldun;
		if ($jetBraldun > $jetMonstre) {
			$retour .= PHP_EOL."Vous avez résisté au cris, vous n'avez pas de malus en vue.";
		} else {
			$retour .= PHP_EOL."Vous n'avez pas résisté au cris, vous avez 1 de malus en vue.";
		}
		$retour .= PHP_EOL."Malus sur votre tour (en minutes) : ".$malus. ' min.';
		$retour .= PHP_EOL."Nombre de tours : ".$nbTours;
		Bral_Util_Log::viemonstres()->trace(get_class($this)."  - getDetailsBot - exit");
		return $retour;
	}
}