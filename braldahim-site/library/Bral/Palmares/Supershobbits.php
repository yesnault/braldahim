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
class Bral_Palmares_Supershobbits extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Supers Hobbits";
	}
	
	function getNomInterne() {
		return "box_onglet_supershobbits";		
	}
	
	function getNomClasse() {
		return "supershobbits";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/supershobbits.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("Hobbit");
		$tabHobbits = null;
		$hobbitTable = new Hobbit();
		$this->view->niveaux = $hobbitTable->findDistinctNiveaux();
		
		$hobbit = $hobbitTable->findByNiveauAndCaracteristique($this->view->filtre, "force");
		$tabHobbits["Force"]["nombre"] = $hobbit[0]["nombre"];
		$tabHobbits["Force"]["base"] = $this->view->config->game->base_force;
		$tabHobbits["Force"]["libelle"] = "D".$this->view->config->game->de_force;
		
		$hobbit = $hobbitTable->findByNiveauAndCaracteristique($this->view->filtre, "agilite");
		$tabHobbits["Agilite"]["nombre"] = $hobbit[0]["nombre"];
		$tabHobbits["Agilite"]["base"] = $this->view->config->game->base_agilite;
		$tabHobbits["Agilite"]["libelle"] = "D".$this->view->config->game->de_agilite;
		
		$hobbit = $hobbitTable->findByNiveauAndCaracteristique($this->view->filtre, "vigueur");
		$tabHobbits["Vigueur"]["nombre"] = $hobbit[0]["nombre"];
		$tabHobbits["Vigueur"]["base"] = $this->view->config->game->base_vigueur;
		$tabHobbits["Vigueur"]["libelle"] = "D".$this->view->config->game->de_vigueur;
		
		$hobbit = $hobbitTable->findByNiveauAndCaracteristique($this->view->filtre, "sagesse");
		$tabHobbits["Sagesse"]["nombre"] = $hobbit[0]["nombre"];
		$tabHobbits["Sagesse"]["base"] = $this->view->config->game->base_sagesse;
		$tabHobbits["Sagesse"]["libelle"] = "D".$this->view->config->game->de_sagesse;
		
		$hobbit = $hobbitTable->findByNiveauAndCaracteristique($this->view->filtre, "armure_naturelle");
		$tabHobbits["Armure Naturelle"]["nombre"] = $hobbit[0]["nombre"];
		$tabHobbits["Armure Naturelle"]["base"] = 0;
		$tabHobbits["Armure Naturelle"]["libelle"] = "";
		
		$hobbit = $hobbitTable->findByNiveauAndCaracteristique($this->view->filtre, "regeneration");
		$tabHobbits["Regénération"]["nombre"] = $hobbit[0]["nombre"];
		$tabHobbits["Regénération"]["base"] = 0;
		$tabHobbits["Regénération"]["libelle"] = "D".$this->view->config->game->de_regeneration;
		
		$hobbit = $hobbitTable->findByNiveauAndCaracteristique($this->view->filtre, "poids_transportable");
		$tabHobbits["Poids Transportable"]["nombre"] = $hobbit[0]["nombre"];
		$tabHobbits["Poids Transportable"]["base"] = 0;
		$tabHobbits["Poids Transportable"]["libelle"] = "Kg";
		
		$hobbit = $hobbitTable->findByNiveauAndCaracteristique($this->view->filtre, "duree_prochain_tour");
		$tabHobbits["Durée du tour"]["nombre"] = $hobbit[0]["nombre"];
		$tabHobbits["Durée du tour"]["base"] = 0;
		$tabHobbits["Durée du tour"]["libelle"] = "";
		
		$this->view->hobbits = $tabHobbits;
	}
}