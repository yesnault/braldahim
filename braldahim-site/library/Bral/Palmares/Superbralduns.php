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
class Bral_Palmares_Superbralduns extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Super Braldûns";
	}

	function getNomInterne() {
		return "box_onglet_superbralduns";
	}

	function getNomClasse() {
		return "superbralduns";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/superbralduns.phtml");
	}

	private function prepare() {
		Zend_Loader::loadClass("Braldun");
		$tabBralduns = null;
		$braldunTable = new Braldun();
		
		$tabNiveaux[1] = "0 à 9";
		$tabNiveaux[2] = "10 à 19";
		$tabNiveaux[3] = "20 à 29";
		$tabNiveaux[4] = "30 à 39";
		$tabNiveaux[5] = "40";

		$this->view->niveaux = $tabNiveaux;
		
		if ($this->view->filtre == -1) {
			$this->view->filtre = 1;
		}

		if ($this->view->filtre == 2) {
			$min = 10;
			$max = 19;
		} elseif ($this->view->filtre == 3) {
			$min = 20;
			$max = 29;
		} elseif ($this->view->filtre == 4) {
			$min = 30;
			$max = 39;
		} elseif ($this->view->filtre == 5) {
			$min = 30;
			$max = 39;
		} else {
			$min = 0;
			$max = 10;
		}

		$braldunTable = new Braldun();
		$this->view->nbBralduns = $braldunTable->countByNiveauMinMax($min, $max);

		if ($this->view->nbBralduns > 3) {
				
			$braldun = $braldunTable->findByNiveauxMinMaxAndCaracteristique($min, $max, "force");
			$tabBralduns["Force"]["nombre"] = $braldun[0]["nombre"];
			$tabBralduns["Force"]["base"] = $this->view->config->game->base_force;
			$tabBralduns["Force"]["libelle"] = "D".$this->view->config->game->de_force;
				
			$braldun = $braldunTable->findByNiveauxMinMaxAndCaracteristique($min, $max, "agilite");
			$tabBralduns["Agilite"]["nombre"] = $braldun[0]["nombre"];
			$tabBralduns["Agilite"]["base"] = $this->view->config->game->base_agilite;
			$tabBralduns["Agilite"]["libelle"] = "D".$this->view->config->game->de_agilite;
				
			$braldun = $braldunTable->findByNiveauxMinMaxAndCaracteristique($min, $max, "vigueur");
			$tabBralduns["Vigueur"]["nombre"] = $braldun[0]["nombre"];
			$tabBralduns["Vigueur"]["base"] = $this->view->config->game->base_vigueur;
			$tabBralduns["Vigueur"]["libelle"] = "D".$this->view->config->game->de_vigueur;
				
			$braldun = $braldunTable->findByNiveauxMinMaxAndCaracteristique($min, $max, "sagesse");
			$tabBralduns["Sagesse"]["nombre"] = $braldun[0]["nombre"];
			$tabBralduns["Sagesse"]["base"] = $this->view->config->game->base_sagesse;
			$tabBralduns["Sagesse"]["libelle"] = "D".$this->view->config->game->de_sagesse;
				
			$braldun = $braldunTable->findByNiveauxMinMaxAndCaracteristique($min, $max, "armure_naturelle");
			$tabBralduns["Armure Naturelle"]["nombre"] = $braldun[0]["nombre"];
			$tabBralduns["Armure Naturelle"]["base"] = 0;
			$tabBralduns["Armure Naturelle"]["libelle"] = "";
				
			$braldun = $braldunTable->findByNiveauxMinMaxAndCaracteristique($min, $max, "regeneration");
			$tabBralduns["Regénération"]["nombre"] = $braldun[0]["nombre"];
			$tabBralduns["Regénération"]["base"] = 0;
			$tabBralduns["Regénération"]["libelle"] = "D".$this->view->config->game->de_regeneration;
				
			$braldun = $braldunTable->findByNiveauxMinMaxAndCaracteristique($min, $max, "poids_transportable");
			$tabBralduns["Poids Transportable"]["nombre"] = $braldun[0]["nombre"];
			$tabBralduns["Poids Transportable"]["base"] = 0;
			$tabBralduns["Poids Transportable"]["libelle"] = "Kg";
				
			$braldun = $braldunTable->findByNiveauxMinMaxAndCaracteristique($min, $max, "duree_prochain_tour");
			$tabBralduns["Durée du tour"]["nombre"] = $braldun[0]["nombre"];
			$tabBralduns["Durée du tour"]["base"] = 0;
			$tabBralduns["Durée du tour"]["libelle"] = "";
		}

		$this->view->bralduns = $tabBralduns;
	}
}