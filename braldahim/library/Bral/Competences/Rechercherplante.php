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
class Bral_Competences_Rechercherplante extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass('Plante');
		Zend_Loader::loadClass('Bral_Util_Plantes');

		// Position précise avec (Vue+BM) de vue *2
		$this->view->rayon_precis =  (Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit ) * 2;

		$this->view->avecChoix = false;
		$tabChoix = array();

		if ($this->hobbit_competence["pourcentage_hcomp"] >= 80) {
			$this->view->avecChoix = true;
			$tabChoix = Bral_Util_Plantes::getTabPlantes();
		}

		$this->view->tabChoix = $tabChoix;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : ".$this->request->get("valeur_1"));
		} else {
			$choix = (int)$this->request->get("valeur_1");
		}

		if ($choix > count($this->view->tabChoix) - 1 || $choix == -1) {
			throw new Zend_Exception(get_class($this)." Valeur invalide  2 : ".$choix);
		}

		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->rechercherPlante($choix);
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function rechercherPlante($choix) {
		
		// La distance max de repérage d'une plante est : jet SAG+BM
		$tirageRayonMax = 0;
		for ($i=1; $i <= ($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_hobbit) ; $i++) {
			$tirageRayonMax = $tirageRayonMax + Bral_Util_De::get_1d6();
		}
		$this->view->rayon_max = $tirageRayonMax + $this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit;

		$idTypePlante = null;
		$idTypePartiePlante = null;
		$this->view->libelleRecherche = "la plante la plus proche";
		
		if ($choix != -3 && $choix != -2) {
			$idTypePlante = $this->view->tabChoix[$choix]["id_type_plante"];
			$idTypePartiePlante = $this->view->tabChoix[$choix]["id_type_partieplante"];
			
			$this->view->libelleRecherche = "des ".$this->view->tabChoix[$choix]["nom_type_partieplante"];
			$this->view->libelleRecherche .= "s ".$this->view->tabChoix[$choix]["nom_type_plante"];
			$this->view->libelleRecherche .= " (".$this->view->tabChoix[$choix]["categorie_type_plante"].")";
		}
		
		$planteTable = new Plante();
		$planteRow = $planteTable->findLaPlusProche($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->rayon_max, $idTypePlante, $idTypePartiePlante);

		if (!empty($planteRow)) {
			$plante = array('categorie' => $planteRow["categorie_type_plante"],'x_plante' => $planteRow["x_plante"], 'y_plante' => $planteRow["y_plante"]);
			$this->view->trouve = true;
			$this->view->plante = $plante;
			if ($planteRow["distance"] <= $this->view->rayon_precis) {
				$this->view->proche = true;
			} else {
				$this->view->proche = false;
			}
		} else {
			$this->view->trouve = false;
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers"));
	}
}