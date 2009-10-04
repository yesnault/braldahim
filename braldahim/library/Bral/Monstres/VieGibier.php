<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Monstres_VieGibier {

	public function __construct($view, $villes) {
		$this->config = Zend_Registry::get('config');
		$this->view = $view;
		$this->villes = $villes;
	}

	public function action() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGibierAction - enter");
		try {
			// recuperation des monstres a jouer
			$monstreTable = new Monstre();
			$monstres = $monstreTable->findMonstresAJouerSansGroupe(true, $this->config->game->monstre->nombre_groupe_a_jouer, true);
			$this->traiteGibiers($monstres, true);
			$monstres = $monstreTable->findMonstresAJouerSansGroupe(false, $this->config->game->monstre->nombre_groupe_a_jouer, true);
			$this->traiteGibiers($monstres, false);
		} catch (Exception $e) {
			Bral_Util_Log::erreur()->err(get_class($this)." - vieGibierAction - Erreur:".$e->getTraceAsString());
			throw new Zend_Exception($e);
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGibierAction - exit");
	}

	private function traiteGibiers($gibiers, $aleatoire1D2) {
		foreach($gibiers as $s) {
			if ($aleatoire1D2 == false || ($aleatoire1D2 == true && Bral_Util_De::get_1d2() == 1)) {
				$this->vieGibierAction($s);
			}
		}
	}

	private function vieGibierAction(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGibierAction - enter (id=".$monstre["id_monstre"].")");
		
		$dateCourante = date("Y-m-d H:i:s");
		if ($dateCourante > $monstre["date_suppression_monstre"]) {
			$this->suppressionGibier($monstre);
		} else {
			$this->deplacementGibier($monstre);
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGibierAction - exit");
	}

	private function suppressionGibier(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - suppressionGibier - enter");
		$monstreTable = new Monstre();
		$where = "id_monstre = ".(int)$monstre["id_monstre"]; 
		$monstreTable->delete($where);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - suppressionGibier - exit");
	}
	
	/**
	 * Deplacement du Gibier.
	 */
	protected function deplacementGibier(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementGibier - enter");

		if (($monstre["x_monstre"] == $monstre["x_direction_monstre"]) && //
		($monstre["y_monstre"] == $monstre["y_direction_monstre"])) {
			
			$ajoutFuite = 5;

			$dx = Bral_Util_De::get_1d20() + $ajoutFuite;
			$dy = Bral_Util_De::get_1d20() + $ajoutFuite;

			$plusMoinsX = Bral_Util_De::get_1d2();
			$plusMoinsY = Bral_Util_De::get_1d2();

			if ($plusMoinsX == 1) {
				$monstre["x_direction_monstre"] = $monstre["x_direction_monstre"] - $dx;
			} else {
				$monstre["x_direction_monstre"] = $monstre["x_direction_monstre"] + $dx;
			}

			if ($plusMoinsY == 1) {
				$monstre["y_direction_monstre"] = $monstre["y_direction_monstre"] - $dy;
			} else {
				$monstre["y_direction_monstre"] = $monstre["y_direction_monstre"] + $dy;
			}

			$tab = Bral_Monstres_VieMonstre::getTabXYRayon($monstre["id_fk_zone_nid_monstre"], $monstre["niveau_monstre"], $monstre["x_direction_monstre"], $monstre["y_direction_monstre"], $monstre["x_min_monstre"], $monstre["x_max_monstre"], $monstre["y_min_monstre"], $monstre["y_max_monstre"]);
			$monstre["x_direction_monstre"] = $tab["x_direction"];
			$monstre["y_direction_monstre"] = $tab["y_direction"];

			Bral_Util_Log::viemonstres()->debug(get_class($this)." monstre (".$monstre["id_monstre"].")- calcul nouvelle valeur direction x=".$monstre["x_direction_monstre"]." y=".$monstre["y_direction_monstre"]." ");
		}

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($monstre);
		$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementGibier - exit");
	}
}