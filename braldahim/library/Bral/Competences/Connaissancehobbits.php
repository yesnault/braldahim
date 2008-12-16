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
class Bral_Competences_Connaissancehobbits extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Commun");
		
		/*
		 * Si le hobbit n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa == false) {
			return;
		}
		
		$vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit;
		$this->view->distance = $vue_nb_cases;
		
		$x_min = $this->view->user->x_hobbit - $this->view->distance;
		$x_max = $this->view->user->x_hobbit + $this->view->distance;
		$y_min = $this->view->user->y_hobbit - $this->view->distance;
		$y_max = $this->view->user->y_hobbit + $this->view->distance;
		
		// recuperation des monstres qui sont presents sur la vue
		$tabHobbits = null;
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->id_hobbit);
		
		foreach($hobbits as $h) {
			$tab = array(
				'id_hobbit' => $h["id_hobbit"],
				'nom_hobbit' => $h["nom_hobbit"],
				'prenom_hobbit' => $h["prenom_hobbit"],
				'x_hobbit' => $h["x_hobbit"],
				'y_hobbit' => $h["y_hobbit"],
				'dist_hobbit' => max(abs(abs($h["x_hobbit"]) - abs($this->view->user->x_hobbit)), abs(abs($h["y_hobbit"])-abs($this->view->user->y_hobbit)))
			);
			$tabHobbits[] = $tab;
		}
		
		$this->view->tabHobbits = $tabHobbits;
		$this->view->nHobbits = count($tabHobbits);
		
	}
	
	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
		if ($this->view->nHobbits > 0) {
			foreach ($this->view->tabHobbits as $key => $row) {
    			$dist[$key] = $row['dist_hobbit'];
			}
			array_multisort($dist, SORT_ASC, $this->view->tabHobbits);
		}
	}
	
	function prepareResultat() {
		
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Hobbit invalide : ".$this->request->get("valeur_1"));
		} else {
			$idHobbit = (int)$this->request->get("valeur_1");
		}
		
		$cdmHobbit = false;
		if (isset($this->view->tabHobbits) && count($this->view->tabHobbits) > 0) {
			foreach ($this->view->tabHobbits as $m) {
				if ($m["id_hobbit"] == $idHobbit) {
					$cdmHobbit = true;
					$dist = $m["dist_hobbit"];
					break;
				}
			}
		}
		if ($cdmHobbit === false) {
			throw new Zend_Exception(get_class($this)." Hobbit invalide (".$idHobbit.")");
		}
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculCDM($idHobbit,$dist);
		}
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculCDM($idHobbit,$dist_hobbit) {
		Zend_Loader::loadClass("Bral_Util_Connaissance");
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->findById($idHobbit);
		$hobbit = $hobbitRowset->toArray();
		$tabCDM["id_hobbit"] = $hobbit["id_hobbit"];
		$tabCDM["prenom_hobbit"] = $hobbit["prenom_hobbit"];
		$tabCDM["nom_hobbit"] = $hobbit["nom_hobbit"];
		$tabCDM["niveau_hobbit"] = $hobbit["niveau_hobbit"];
			
		/* Calculs suivant la distance :
		 * 
		 * Pour les carac : FOR, AGI, SAG, VIG, REG, ARM (ARM nat + portée dans le cas des hobbit) on applique le schéma suivant :
		 * Si distance = 0 : +/- nD3-1
		 * Si distance = 1 : +/- nD3
		 * Si distance = 2 : +/- nD3+1
		 * Si distance = 3 : +/- nD3+2
		 * Si distance = 4 ou +  : +/- nD3+3
		 *
		 * Ensuite on a n qui varie suivant le level du Hobbit
		 * cible niveau 1-9 : n=1
		 * cible niveau 10-19 : n=2
		 * cible niveau 20-29 : n=3
		 * cible niveau 21-39 : n=4
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
		
		$n = intval ($hobbit["niveau_hobbit"]/10) + 1;
		
		$dist = $dist_hobbit;
		
		if ($dist > 4) {
			$dist=4;
		}
		
		$tabCDM["min_for_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMin ($hobbit["force_base_hobbit"], $n, $dist);
		$tabCDM["max_for_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMax ($hobbit["force_base_hobbit"], $n, $dist);
		
		$tabCDM["min_agi_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMin ($hobbit["agilite_base_hobbit"], $n, $dist);
		$tabCDM["max_agi_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMax ($hobbit["agilite_base_hobbit"], $n, $dist);
		
		$tabCDM["min_sag_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMin ($hobbit["sagesse_base_hobbit"], $n, $dist);
		$tabCDM["max_sag_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMax ($hobbit["sagesse_base_hobbit"], $n, $dist);
		
		$tabCDM["min_vig_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMin ($hobbit["vigueur_base_hobbit"], $n, $dist);
		$tabCDM["max_vig_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMax ($hobbit["vigueur_base_hobbit"], $n, $dist);
		
		$tabCDM["min_reg_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMin ($hobbit["regeneration_hobbit"], $n, $dist);
		$tabCDM["max_reg_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMax ($hobbit["regeneration_hobbit"], $n, $dist);
		
		$tabCDM["min_arm_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMin ($hobbit["armure_naturelle_hobbit"] + $hobbit["armure_equipement_hobbit"], $n, $dist);
		$tabCDM["max_arm_hobbit"] = Bral_Util_Connaissance::calculConnaissanceMax ($hobbit["armure_naturelle_hobbit"] + $hobbit["armure_equipement_hobbit"], $n, $dist);
		
		$tabCDM["min_pvmax_hobbit"] = floor($hobbit["pv_max_hobbit"] - $hobbit["pv_max_hobbit"] * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100);
		$tabCDM["max_pvmax_hobbit"] = ceil($hobbit["pv_max_hobbit"] + $hobbit["pv_max_hobbit"] * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100);
		
		$tabCDM["min_pvact_hobbit"] = floor($hobbit["pv_restant_hobbit"] - $hobbit["pv_restant_hobbit"] * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100);
		$tabCDM["max_pvact_hobbit"] = ceil($hobbit["pv_restant_hobbit"] + $hobbit["pv_restant_hobbit"] * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100);
		if ($tabCDM["max_pvact_hobbit"] > $tabCDM["max_pvmax_hobbit"]) {
			$tabCDM["max_pvact_hobbit"] = $tabCDM["max_pvmax_hobbit"];
		}
		if ($tabCDM["min_pvact_hobbit"] > $tabCDM["min_pvmax_hobbit"]) {
			$tabCDM["min_pvact_hobbit"] = $tabCDM["min_pvmax_hobbit"];
		}
		
		$duree_base_tour_minute = Bral_Util_ConvertDate::getMinuteFromHeure($hobbit["duree_courant_tour_hobbit"]);
		$tabCDM["min_dla_hobbit"] = Bral_Util_ConvertDate::getHeureFromMinute($duree_base_tour_minute - floor($duree_base_tour_minute * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100));
		$tabCDM["max_dla_hobbit"] = Bral_Util_ConvertDate::getHeureFromMinute($duree_base_tour_minute + ceil($duree_base_tour_minute * (Bral_Util_De::getLanceDeSpecifique(1,0,$dist*3 + 6))/100));
		
		$this->view->tabCDM = $tabCDM;
		
		$id_type = $this->view->config->game->evenements->type->competence;
		$details = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a réussi l'utilisation d'une compétence sur ".$hobbit["prenom_hobbit"]." ".$hobbit["nom_hobbit"]." (".$hobbit["id_hobbit"].")";
		$this->setDetailsEvenement($details, $id_type);
		$this->setDetailsEvenementCible($hobbit["id_hobbit"],"hobbit");
		
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_communes", "box_laban"));
	}
}
