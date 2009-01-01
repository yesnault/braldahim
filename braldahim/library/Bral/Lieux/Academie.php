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
class Bral_Lieux_Academie extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("Bral_Util_Tour");
		
		$coutPIForce =  $this->calculCoutAmelioration(1 + $this->view->user->force_base_hobbit);
		$coutPIAgilite = $this->calculCoutAmelioration(1 + $this->view->user->agilite_base_hobbit);
		$coutPIVigueur = $this->calculCoutAmelioration(1 + $this->view->user->vigueur_base_hobbit);
		$coutPISagesse = $this->calculCoutAmelioration(1 + $this->view->user->sagesse_base_hobbit);

		$this->view->coutPIForce = $coutPIForce;
		$this->view->coutPIAgilite = $coutPIAgilite;
		$this->view->coutPIVigueur = $coutPIVigueur;
		$this->view->coutPISagesse = $coutPISagesse;
		
		$this->view->coutCastarsForce = $this->calculCoutCastars($coutPIForce);
		$this->view->coutCastarsAgilite = $this->calculCoutCastars($coutPIAgilite);
		$this->view->coutCastarsVigueur = $this->calculCoutCastars($coutPIVigueur);
		$this->view->coutCastarsSagesse = $this->calculCoutCastars($coutPISagesse);
		
		$this->view->achatPossibleForce = false;
		$this->view->achatPossibleAgilite = false;
		$this->view->achatPossibleVigueur = false;
		$this->view->achatPossibleSagesse = false;
		$this->view->achatPossible  = false;
		
		if ($coutPIForce <= $this->view->user->pi_hobbit && $this->view->coutCastarsForce <= $this->view->user->castars_hobbit) {
			$this->view->achatPossibleForce = true;
		}
		if ($coutPIAgilite <= $this->view->user->pi_hobbit && $this->view->coutCastarsAgilite <= $this->view->user->castars_hobbit) {
			$this->view->achatPossibleAgilite = true;
		}
		if ($coutPIVigueur <= $this->view->user->pi_hobbit && $this->view->coutCastarsVigueur <= $this->view->user->castars_hobbit) {
			$this->view->achatPossibleVigueur = true;
		}
		if ($coutPISagesse <= $this->view->user->pi_hobbit && $this->view->coutCastarsSagesse <= $this->view->user->castars_hobbit) {
			$this->view->achatPossibleSagesse = true;
		}
		
		if ($this->view->achatPossibleForce || $this->view->achatPossibleAgilite || 
			$this->view->achatPossibleVigueur || $this->view->achatPossibleSagesse) {
			$this->view->achatPossible  = true;
		}
		
		// $this->view->utilisationPaPossible initialisé dans Bral_Lieux_Lieu
	}

	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {

		// verification qu'il a assez de PA
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PA:".$this->view->user->pa_hobbit);
		}

		// verification qu'il a assez de resssource
		if ($this->view->achatPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible (ressources)");
		}

		$this->view->nomCaracteristique = $this->request->get("valeur_1");
		// verification que la valeur recue est bien connue
		switch($this->request->get("valeur_1")) {
			case "FOR":
				if ($this->view->achatPossibleForce == false) {
					throw new Zend_Exception(get_class($this)." Achat FOR invalide : pi=".$this->view->user->pi_hobbit. " coutPI=".$this->view->coutPIForce. " coutCastars=".$this->view->coutCastarsForce. " castars=".$this->view->user->castars_hobbit);
				} else {
					$this->view->user->force_base_hobbit = $this->view->user->force_base_hobbit + 1;
					$this->view->user->pi_hobbit = $this->view->user->pi_hobbit - $this->view->coutForce;
					$this->view->coutPi = $this->view->coutPIForce;
					$this->view->coutCastars = $this->view->coutCastarsForce;
					Zend_Loader::loadClass("Bral_Util_Poids");
					$this->view->user->poids_transportable_hobbit = Bral_Util_Poids::calculPoidsTransportable($this->view->user->force_base_hobbit);
				}
				break;
			case "SAG":
				if ($this->view->achatPossibleSagesse == false) {
					throw new Zend_Exception(get_class($this)." Achat SAG invalide : pi=".$this->view->user->pi_hobbit. " coutPI=".$this->view->coutPISagesse. " coutCastars=".$this->view->coutCastarsSagesse. " castars=".$this->view->user->castars_hobbit);
				} else {
					$this->view->user->sagesse_base_hobbit = $this->view->user->sagesse_base_hobbit + 1;
					$this->view->user->pi_hobbit = $this->view->user->pi_hobbit - $this->view->coutSagesse;
					$this->view->coutPi = $this->view->coutPISagesse;
					$this->view->coutCastars = $this->view->coutCastarsSagesse;
					$this->view->user->duree_prochain_tour_hobbit = Bral_Util_Tour::getDureeBaseProchainTour($this->view->user, $this->view->config); 
				}
				break;
			case "VIG":
				if ($this->view->achatPossibleVigueur == false) {
					throw new Zend_Exception(get_class($this)." Achat VIG invalide : pi=".$this->view->user->pi_hobbit. " coutPI=".$this->view->coutPIVigueur. " coutCastars=".$this->view->coutCastarsVigueur. " castars=".$this->view->user->castars_hobbit);
				} else {
					$this->view->user->vigueur_base_hobbit = $this->view->user->vigueur_base_hobbit + 1;
					$this->view->user->pi_hobbit = $this->view->user->pi_hobbit - $this->view->coutVigueur;
					$this->view->coutPi = $this->view->coutPIVigueur;
					$this->view->coutCastars = $this->view->coutCastarsVigueur;
					// Mise à jour de la regeneration // c'est aussi mis à jour dans l'eujimnasiumne
					$this->view->user->regeneration_hobbit = floor($this->view->user->vigueur_base_hobbit / 4) + 1;
				}
				break;
			case "AGI":
				if ($this->view->achatPossibleAgilite == false) {
					throw new Zend_Exception(get_class($this)." Achat AGI invalide : pi=".$this->view->user->pi_hobbit. " coutPI=".$this->view->coutPIAgilite. " coutCastars=".$this->view->coutCastarsAgilite. " castars=".$this->view->user->castars_hobbit);
				} else {
					$this->view->user->agilite_base_hobbit = $this->view->user->agilite_base_hobbit + 1;
					$this->view->user->pi_hobbit = $this->view->user->pi_hobbit - $this->view->coutAgilite;
					$this->view->coutPi = $this->view->coutPIAgilite;
					$this->view->coutCastars = $this->view->coutCastarsAgilite;
				}
				break;
			default:
				throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$this->request->get("valeur_1"));
		}
		
		// Recalcul de l'armure naturelle
		$this->view->user->armure_naturelle_hobbit = intval(($this->view->user->force_base_hobbit + $this->view->user->vigueur_base_hobbit) / 5) + 1;
		
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		$this->view->user->pi_hobbit = $this->view->user->pi_hobbit - $this->view->coutPi;
		if ($this->view->user->pi_hobbit < 0) {
			$this->view->user->pi_hobbit = 0;
		}
		
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_vue");
	}

	private function calculCoutCastars($pi) {
		if ($pi < 50) {
			return $pi;
		} else {
			return 50;
		}
	}

	private function calculCoutAmelioration($niveau) {
		if ($niveau <= 1) {
			return 1;
		} else {
			return (($niveau - 1) * $niveau);
		}
	}
}