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
class Bral_Competences_Connaissancebralduns extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Commun");
		
		/*
		 * Si le braldun n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa == false) {
			return;
		}
		
		$vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun) + $this->view->user->vue_bm_braldun;
		$this->view->distance = $vue_nb_cases;
		
		$x_min = $this->view->user->x_braldun - $this->view->distance;
		$x_max = $this->view->user->x_braldun + $this->view->distance;
		$y_min = $this->view->user->y_braldun - $this->view->distance;
		$y_max = $this->view->user->y_braldun + $this->view->distance;
		
		// recuperation des monstres qui sont presents sur la vue
		$tabBralduns = null;
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_braldun, $this->view->user->id_braldun, false);
		
		foreach($bralduns as $h) {
			$tab = array(
				'id_braldun' => $h["id_braldun"],
				'nom_braldun' => $h["nom_braldun"],
				'prenom_braldun' => $h["prenom_braldun"],
				'x_braldun' => $h["x_braldun"],
				'y_braldun' => $h["y_braldun"],
				'dist_braldun' => max(abs($h["x_braldun"] - $this->view->user->x_braldun), abs($h["y_braldun"] - $this->view->user->y_braldun))
			);
			$tabBralduns[] = $tab;
		}
		
		$this->view->tabBralduns = $tabBralduns;
		$this->view->nBralduns = count($tabBralduns);
		
	}
	
	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
		if ($this->view->nBralduns > 0) {
			foreach ($this->view->tabBralduns as $key => $row) {
    			$dist[$key] = $row['dist_braldun'];
			}
			array_multisort($dist, SORT_ASC, $this->view->tabBralduns);
		}
	}
	
	function prepareResultat() {
		
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Braldun invalide : ".$this->request->get("valeur_1"));
		} else {
			$idBraldun = (int)$this->request->get("valeur_1");
		}
		
		$cdmBraldun = false;
		if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
			foreach ($this->view->tabBralduns as $m) {
				if ($m["id_braldun"] == $idBraldun) {
					$cdmBraldun = true;
					$dist = $m["dist_braldun"];
					$this->view->distance = $dist;
					break;
				}
			}
		}
		if ($cdmBraldun === false) {
			throw new Zend_Exception(get_class($this)." Braldun invalide (".$idBraldun.")");
		}
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculCDM($idBraldun,$dist);
		}
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}
	
	private function calculCDM($idBraldun,$dist_braldun) {
		Zend_Loader::loadClass("Bral_Util_Connaissance");
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->findById($idBraldun);
		$braldun = $braldunRowset->toArray();
		$tabCDM["id_braldun"] = $braldun["id_braldun"];
		$tabCDM["prenom_braldun"] = $braldun["prenom_braldun"];
		$tabCDM["nom_braldun"] = $braldun["nom_braldun"];
		$tabCDM["niveau_braldun"] = $braldun["niveau_braldun"];
			
		/* Calculs suivant la distance :
		 * 
		 * Pour les carac : FOR, AGI, SAG, VIG, REG, ARM (ARM nat + portée dans le cas des braldun) on applique le schéma suivant :
		 * Si distance = 0 : +/- nD3-1
		 * Si distance = 1 : +/- nD3
		 * Si distance = 2 : +/- nD3+1
		 * Si distance = 3 : +/- nD3+2
		 * Si distance = 4 ou +  : +/- nD3+3
		 *
		 * Ensuite on a n qui varie suivant la différence de niveau entre les 2 bralduns :
		 * de négatif à 4 : n=1
		 * de 5-9 : n=2
		 * de 10-14 : n=3
		 * etc ..
		 *
		 * Au minimum on borne à 0 (pas de négatif).
		 * 
		 * Ensuite pour la DLA, les PV actuels et max on fait un % tout simple (et on affiche en HH:MM pour la DLA) :
		 * Si distance = 0 : +/- [0;6]%
		 * Si distance = 1 : +/- [0;9]%
		 * Si distance = 2 : +/- [0;12]%
		 * Si distance = 3 : +/- [0;15]%
		 * Si distance = 4 ou +  : +/- [0;18]%
		 * 
		 * Attention pour les PV : il faut que cela reste cohérent : pas de PV actuels max supérieur au PV min.
		 * Genre :
		 * PV actuel : de 25 à 36
		 * PV max : de 30 à 42
		 * 
		 * et pour terminer le niveau :
		 * Si distance = 0 : +/- 1D2
		 * Si distance = 1 : +/- 1D2+1
		 * Si distance = 2 : +/- 1D2+2
		 * Si distance = 3 : +/- 1D2+3
		 * Si distance = 4 ou +  : +/- 1D2+4
		 * 
		 */
		
		$n = $braldun["niveau_braldun"] - $this->view->user->niveau_braldun;
		if ($n < 0){
			$n = 1;
		}
		else {
			$n = intval ($n/5) + 1;
		}
		
		$dist = $dist_braldun;
		
		if ($dist > 4) {
			$dist=4;
		}
		
		$tabCDM["min_for_braldun"] = Bral_Util_Connaissance::calculConnaissanceMin ($braldun["force_base_braldun"], $n, $dist) + $this->view->config->game->base_force;
		$tabCDM["max_for_braldun"] = Bral_Util_Connaissance::calculConnaissanceMax ($braldun["force_base_braldun"], $n, $dist) + $this->view->config->game->base_force;
		
		$tabCDM["min_agi_braldun"] = Bral_Util_Connaissance::calculConnaissanceMin ($braldun["agilite_base_braldun"], $n, $dist) + $this->view->config->game->base_agilite;
		$tabCDM["max_agi_braldun"] = Bral_Util_Connaissance::calculConnaissanceMax ($braldun["agilite_base_braldun"], $n, $dist) + $this->view->config->game->base_agilite;
		
		$tabCDM["min_sag_braldun"] = Bral_Util_Connaissance::calculConnaissanceMin ($braldun["sagesse_base_braldun"], $n, $dist) + $this->view->config->game->base_sagesse;
		$tabCDM["max_sag_braldun"] = Bral_Util_Connaissance::calculConnaissanceMax ($braldun["sagesse_base_braldun"], $n, $dist) + $this->view->config->game->base_sagesse;
		
		$tabCDM["min_vig_braldun"] = Bral_Util_Connaissance::calculConnaissanceMin ($braldun["vigueur_base_braldun"], $n, $dist) + $this->view->config->game->base_vigueur;
		$tabCDM["max_vig_braldun"] = Bral_Util_Connaissance::calculConnaissanceMax ($braldun["vigueur_base_braldun"], $n, $dist) + $this->view->config->game->base_vigueur;
		
		$tabCDM["min_reg_braldun"] = Bral_Util_Connaissance::calculConnaissanceMin ($braldun["regeneration_braldun"], $n, $dist);
		$tabCDM["max_reg_braldun"] = Bral_Util_Connaissance::calculConnaissanceMax ($braldun["regeneration_braldun"], $n, $dist);
		
		$armureTotale = $braldun["armure_naturelle_braldun"] + $braldun["armure_equipement_braldun"] + $braldun["armure_bm_braldun"];
		if ($armureTotale < 0) {
			$armureTotale = 0;
		}
		$tabCDM["min_arm_braldun"] = Bral_Util_Connaissance::calculConnaissanceMin ($armureTotale, $n, $dist);
		$tabCDM["max_arm_braldun"] = Bral_Util_Connaissance::calculConnaissanceMax ($armureTotale, $n, $dist);
		
		$tabCDM["min_pvmax_braldun"] = floor(floor($braldun["pv_max_braldun"] - $braldun["pv_max_braldun"] * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100)/5)*5;
		$tabCDM["max_pvmax_braldun"] = ceil(ceil($braldun["pv_max_braldun"] + $braldun["pv_max_braldun"] * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100)/5)*5;
		
		$tabCDM["min_pvact_braldun"] = floor($braldun["pv_restant_braldun"] - $braldun["pv_restant_braldun"] * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100);
		$tabCDM["max_pvact_braldun"] = ceil($braldun["pv_restant_braldun"] + $braldun["pv_restant_braldun"] * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100);
		if ($tabCDM["max_pvact_braldun"] > $tabCDM["max_pvmax_braldun"]) {
			$tabCDM["max_pvact_braldun"] = $tabCDM["max_pvmax_braldun"];
		}
		if ($tabCDM["min_pvact_braldun"] > $tabCDM["min_pvmax_braldun"]) {
			$tabCDM["min_pvact_braldun"] = $tabCDM["min_pvmax_braldun"];
		}
		
		$duree_base_tour_minute = Bral_Util_ConvertDate::getMinuteFromHeure($braldun["duree_courant_tour_braldun"]);
		$tabCDM["min_dla_braldun"] = Bral_Util_ConvertDate::getHeureFromMinute($duree_base_tour_minute - floor($duree_base_tour_minute * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100));
		$tabCDM["max_dla_braldun"] = Bral_Util_ConvertDate::getHeureFromMinute($duree_base_tour_minute + ceil($duree_base_tour_minute * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100));
		
		$this->view->tabCDM = $tabCDM;
		
		$id_type = $this->view->config->game->evenements->type->competence;
		$details = "[h".$this->view->user->id_braldun."] a réussi l'utilisation d'une compétence sur [h".$braldun["id_braldun"]."]";
		$this->setDetailsEvenement($details, $id_type);
		$this->setDetailsEvenementCible($braldun["id_braldun"], "braldun", $braldun["niveau_braldun"]);
		
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_communes", "box_laban"));
	}
}
