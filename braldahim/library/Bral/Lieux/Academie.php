<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Academie extends Bral_Lieux_Lieu
{

	private $_utilisationPossible = false;
	private $_coutCastars = null;

	function prepareCommun()
	{
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("Bral_Util_Tour");
		Zend_Loader::loadClass("Bral_Util_Niveau");

		$coutPIForce = $this->calculCoutAmelioration(1 + $this->view->user->force_base_braldun);
		$coutPIAgilite = $this->calculCoutAmelioration(1 + $this->view->user->agilite_base_braldun);
		$coutPIVigueur = $this->calculCoutAmelioration(1 + $this->view->user->vigueur_base_braldun);
		$coutPISagesse = $this->calculCoutAmelioration(1 + $this->view->user->sagesse_base_braldun);

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
		$this->view->achatPossible = false;
		$this->view->achatPossibleCumul = false;

		if ($coutPIForce <= $this->view->user->pi_braldun && $this->view->coutCastarsForce <= $this->view->user->castars_braldun) {
			$this->view->achatPossibleForce = true;
		}
		if ($coutPIAgilite <= $this->view->user->pi_braldun && $this->view->coutCastarsAgilite <= $this->view->user->castars_braldun) {
			$this->view->achatPossibleAgilite = true;
		}
		if ($coutPIVigueur <= $this->view->user->pi_braldun && $this->view->coutCastarsVigueur <= $this->view->user->castars_braldun) {
			$this->view->achatPossibleVigueur = true;
		}
		if ($coutPISagesse <= $this->view->user->pi_braldun && $this->view->coutCastarsSagesse <= $this->view->user->castars_braldun) {
			$this->view->achatPossibleSagesse = true;
		}

		if ($this->view->achatPossibleForce || $this->view->achatPossibleAgilite ||
			$this->view->achatPossibleVigueur || $this->view->achatPossibleSagesse
		) {
			$this->view->achatPossible = true;
		}

		if ($this->view->user->pi_academie_braldun < Bral_Util_Niveau::NB_PI_NIVEAU_MAX) {
			$this->view->achatPossibleCumul = true;
		}
		// $this->view->utilisationPaPossible initialisé dans Bral_Lieux_Lieu
	}

	function prepareFormulaire()
	{
		// rien à faire ici
	}

	function prepareResultat()
	{

		// verification qu'il a assez de PA
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this) . " Utilisation impossible : PA:" . $this->view->user->pa_braldun);
		}

		// verification qu'il a assez de resssource
		if ($this->view->achatPossible == false) {
			throw new Zend_Exception(get_class($this) . " Utilisation impossible (ressources)");
		}

		if ($this->view->achatPossibleCumul == false) {
			throw new Zend_Exception(get_class($this) . " Utilisation impossible (ressources 4100)");
		}

		$this->view->nomCaracteristique = $this->request->get("valeur_1");
		// verification que la valeur recue est bien connue
		switch ($this->request->get("valeur_1")) {
			case "FOR":
				if ($this->view->achatPossibleForce == false) {
					throw new Zend_Exception(get_class($this) . " Achat FOR invalide : pi=" . $this->view->user->pi_braldun . " coutPI=" . $this->view->coutPIForce . " coutCastars=" . $this->view->coutCastarsForce . " castars=" . $this->view->user->castars_braldun);
				} else {
					$this->view->user->force_base_braldun = $this->view->user->force_base_braldun + 1;
					$this->view->user->pi_braldun = $this->view->user->pi_braldun - $this->view->coutForce;
					$this->view->coutPi = $this->view->coutPIForce;
					$this->view->coutCastars = $this->view->coutCastarsForce;
					Zend_Loader::loadClass("Bral_Util_Poids");
					$this->view->user->poids_transportable_braldun = Bral_Util_Poids::calculPoidsTransportable($this->view->user->force_base_braldun);

					Zend_Loader::loadClass("BraldunEquipement");
					Zend_Loader::loadClass("EquipementRune");

					// on va chercher l'equipement porte et les runes
					$braldunEquipementTable = new BraldunEquipement();
					$equipementPorteRowset = $braldunEquipementTable->findByIdBraldun($this->view->user->id_braldun);
					Zend_Loader::loadClass("Bral_Util_Equipement");
					$tabEquipementPorte = Bral_Util_Equipement::prepareTabEquipements($equipementPorteRowset, false, $this->view->user->niveau_braldun);
					if (count($tabEquipementPorte) > 0) {
						$idEquipements = null;

						foreach ($tabEquipementPorte as $e) {
							$idEquipements[] = $e["id_equipement"];
						}

						$equipementRuneTable = new EquipementRune();
						$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);

						if (count($equipementRunes) > 0) {
							foreach ($equipementRunes as $r) {
								if ($r["nom_type_rune"] == "OX") {
									// OX Poids maximum porte augmente de Niveau du Braldun/10 arrondi inferieur
									$this->view->user->poids_transportable_braldun = $this->view->user->poids_transportable_braldun + floor($this->view->user->niveau_braldun / 10);
								}
							}
						}
					}
				}

				break;
			case "SAG":
				if ($this->view->achatPossibleSagesse == false) {
					throw new Zend_Exception(get_class($this) . " Achat SAG invalide : pi=" . $this->view->user->pi_braldun . " coutPI=" . $this->view->coutPISagesse . " coutCastars=" . $this->view->coutCastarsSagesse . " castars=" . $this->view->user->castars_braldun);
				} else {
					$this->view->user->sagesse_base_braldun = $this->view->user->sagesse_base_braldun + 1;
					$this->view->user->pi_braldun = $this->view->user->pi_braldun - $this->view->coutSagesse;
					$this->view->coutPi = $this->view->coutPISagesse;
					$this->view->coutCastars = $this->view->coutCastarsSagesse;
					$this->view->user->duree_prochain_tour_braldun = Bral_Util_Tour::getDureeBaseProchainTour($this->view->user, $this->view->config);
				}
				break;
			case "VIG":
				if ($this->view->achatPossibleVigueur == false) {
					throw new Zend_Exception(get_class($this) . " Achat VIG invalide : pi=" . $this->view->user->pi_braldun . " coutPI=" . $this->view->coutPIVigueur . " coutCastars=" . $this->view->coutCastarsVigueur . " castars=" . $this->view->user->castars_braldun);
				} else {
					$this->view->user->vigueur_base_braldun = $this->view->user->vigueur_base_braldun + 1;
					$this->view->user->pi_braldun = $this->view->user->pi_braldun - $this->view->coutVigueur;
					$this->view->coutPi = $this->view->coutPIVigueur;
					$this->view->coutCastars = $this->view->coutCastarsVigueur;
					// Mise à jour de la regeneration // c'est aussi mis à jour dans l'eujimnasiumne
					$this->view->user->regeneration_braldun = floor($this->view->user->vigueur_base_braldun / 4) + 1;
					$pvAvant = $this->view->user->pv_max_braldun;
					$this->view->user->pv_max_braldun = Bral_Util_Commun::calculPvMaxBaseSansEffetMotE($this->view->config, $this->view->user->vigueur_base_braldun);
					$this->view->user->pv_restant_braldun = $this->view->user->pv_restant_braldun + ($this->view->user->pv_max_braldun - $pvAvant);
					if ($this->view->user->pv_restant_braldun > $this->view->user->pv_max_braldun + $this->view->user->pv_max_bm_braldun) {
						$this->view->user->pv_restant_braldun = $this->view->user->pv_max_braldun + $this->view->user->pv_max_bm_braldun;
					}
				}
				break;
			case "AGI":
				if ($this->view->achatPossibleAgilite == false) {
					throw new Zend_Exception(get_class($this) . " Achat AGI invalide : pi=" . $this->view->user->pi_braldun . " coutPI=" . $this->view->coutPIAgilite . " coutCastars=" . $this->view->coutCastarsAgilite . " castars=" . $this->view->user->castars_braldun);
				} else {
					$this->view->user->agilite_base_braldun = $this->view->user->agilite_base_braldun + 1;
					$this->view->user->pi_braldun = $this->view->user->pi_braldun - $this->view->coutAgilite;
					$this->view->coutPi = $this->view->coutPIAgilite;
					$this->view->coutCastars = $this->view->coutCastarsAgilite;
				}
				break;
			default:
				throw new Zend_Exception(get_class($this) . " Valeur invalide : val=" . $this->request->get("valeur_1"));
		}

		// compteur PI académie
		$this->view->user->pi_academie_braldun = $this->view->user->pi_academie_braldun + $this->view->coutPi;

		// Recalcul de l'armure naturelle
		$this->view->user->armure_naturelle_braldun = Bral_Util_Commun::calculArmureNaturelle($this->view->user->force_base_braldun, $this->view->user->vigueur_base_braldun);

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->coutCastars;
		$this->view->user->pi_braldun = $this->view->user->pi_braldun - $this->view->coutPi;
		if ($this->view->user->pi_braldun < 0) {
			$this->view->user->pi_braldun = 0;
		}

		Zend_Loader::loadClass("Bral_Util_Quete");
		$this->view->estQueteEvenement = Bral_Util_Quete::etapeAmeliorerCaracteristique($this->view->user);

		$this->majBraldun();
	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_laban", "box_vue"));
	}

	private function calculCoutCastars($pi)
	{
		return $pi;
	}

	private function calculCoutAmelioration($niveau)
	{
		if ($niveau <= 1) {
			return 1;
		} else {
			return (($niveau - 1) * $niveau);
		}
	}
}