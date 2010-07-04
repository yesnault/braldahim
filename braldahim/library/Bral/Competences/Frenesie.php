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

/*
 * Attaque : 0.5*(jet d'AGI)+BM AGI + bonus arme att
 * dégats : 0.5*(jet FOR)+BM FOR+ bonus arme dégats
 * dégats critiques : (1.5*(0.5*FOR))+BM FOR+bonus arme dégats
 */
class Bral_Competences_Frenesie extends Bral_Competences_Competence {

	private $_coef = 1;

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Attaque');
		Zend_Loader::loadClass("BraldunEquipement");

		$tabBralduns = null;
		$tabMonstres = null;

		$armeTirPortee = false;
		$braldunEquipement = new BraldunEquipement();
		$equipementPorteRowset = $braldunEquipement->findByTypePiece($this->view->user->id_braldun,"arme_tir");

		if (count($equipementPorteRowset) > 0){
			$armeTirPortee = true;
		} else if ($this->view->user->est_intangible_braldun == "non") {
			$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_braldun, $this->view->user->y_braldun);

			if ($estRegionPvp) {
				// recuperation des bralduns qui sont presents sur la vue
				$braldunTable = new Braldun();
				$bralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun, false);
				foreach($bralduns as $h) {
					$tab = array(
						'id_braldun' => $h["id_braldun"],
						'nom_braldun' => $h["nom_braldun"],
						'prenom_braldun' => $h["prenom_braldun"],
					);
					if ($this->view->user->est_soule_braldun == 'non' ||
					($this->view->user->est_soule_braldun == 'oui' && $h["soule_camp_braldun"] != $this->view->user->soule_camp_braldun)) {
						$tabBralduns[] = $tab;
					}
				}
			}

			// recuperation des monstres qui sont presents sur la vue
			$monstreTable = new Monstre();
			$monstres = $monstreTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
			foreach($monstres as $m) {
				if ($m["genre_type_monstre"] == 'feminin') {
					$m_taille = $m["nom_taille_f_monstre"];
				} else {
					$m_taille = $m["nom_taille_m_monstre"];
				}
				if ($m["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
					$estGibier = true;
				} else {
					$estGibier = false;
				}
				$tabMonstres[] = array("id_monstre" => $m["id_monstre"], "nom_monstre" => $m["nom_type_monstre"], 'taille_monstre' => $m_taille, 'niveau_monstre' => $m["niveau_monstre"], 'est_gibier' => $estGibier);
			}

			$this->view->nBralduns = count($tabBralduns);
			if ($this->view->nBralduns > 0) shuffle($tabBralduns);
			$this->view->tabBralduns = $tabBralduns;

			$this->view->nMonstres = count($tabMonstres);
			if ($this->view->nMonstres > 0) shuffle($tabMonstres);
			$this->view->tabMonstres = $tabMonstres;

			$this->view->estRegionPvp = $estRegionPvp;
		}
		$this->view->armeTirPortee = $armeTirPortee;
	}

	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {

		$this->view->cibleVisible = true;
		if ($this->view->nBralduns == 0 && $this->view->nMonstres == 0) {
			$this->setNbPaSurcharge(0);
			$this->view->cibleVisible = false;
			return;
		}

		if ($this->view->assezDePa == true && $this->view->armeTirPortee == false &&
		($this->view->nBralduns > 0 || $this->view->nMonstres > 0)  && $this->view->user->est_intangible_braldun == 'non') {
			// OK
		} else {
			throw new Zend_Exception("Erreur Frenesie");
		}

		// calcul des jets
		$this->calculJets();
		$retours = null;

		if (Bral_Util_Commun::isRunePortee($braldun->id_braldun, "EM")) {
			$this->view->effetRune = true;
			$coefFrenesie = 0.9;
		} else {
			$coefFrenesie = 0.8;
		}

		if ($this->view->okJet1 === true) {
			if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
				foreach ($this->view->tabBralduns as $h) {
					$this->retourAttaque = $this->attaqueBraldun($this->view->user, $h["id_braldun"], true, false, true);
					$this->calculPx();
					$retours[] = $this->retourAttaque;
					$this->_coef = $this->_coef * $coefFrenesie;
				}
			}

			if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
				foreach ($this->view->tabMonstres as $m) {
					$this->retourAttaque = $this->attaqueMonstre($this->view->user, $m["id_monstre"], false, true);
					$this->calculPx();
					$retours[] = $this->retourAttaque;
					$this->_coef = $this->_coef * $coefFrenesie;
				}
			}
		}

		$this->view->retoursAttaques = $retours;
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue", "box_lieu"));
	}

	protected function calculJetAttaque($braldun) {
		//Attaque : 0.5*(jet d'AGI)+BM AGI + bonus arme att
		$jetAttaquant = Bral_Util_De::getLanceDe6($this->view->config->game->base_agilite + $braldun->agilite_base_braldun);
		$jetAttaquant = $jetAttaquant + $braldun->agilite_bm_braldun + $braldun->agilite_bbdf_braldun + $braldun->bm_attaque_braldun;

		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}
		return floor($this->_coef * $jetAttaquant);
	}

	protected function calculDegat($braldun) {
		$this->view->effetRune = false;

		$jetsDegat["critique"] = 0;
		$jetsDegat["noncritique"] = 0;
		$jetDegatForce = 0;
		$coefCritique = 1.5;
			
		$jetDegatForce = Bral_Util_De::getLanceDe6($this->view->config->game->base_force + $braldun->force_base_braldun);

		$jetsDegat["critique"] = $coefCritique * $jetDegatForce;
		$jetsDegat["noncritique"] = $jetDegatForce;
		$jetsDegat["critique"] = floor($this->_coef * ($jetsDegat["critique"] + $braldun->force_bm_braldun + $braldun->force_bbdf_braldun + $braldun->bm_degat_braldun));
		$jetsDegat["noncritique"] = floor($this->_coef * ($jetsDegat["noncritique"] + $braldun->force_bm_braldun + $braldun->force_bbdf_braldun + $braldun->bm_degat_braldun));

		return $jetsDegat;
	}

	public function calculPx() {

		if ($this->_coef == 1) { // pour la première cible
			parent::calculPx();

			$this->view->nb_px_commun = 0;
			$this->view->calcul_px_generique = false;

			if ($this->view->retourAttaque["attaqueReussie"] === true) {
				$this->view->nb_px_perso = $this->view->nb_px_perso + 1;
			}
		}

		if ($this->retourAttaque["mort"] === true && $this->view->retourAttaque["idTypeGroupeMonstre"] != $this->view->config->game->groupe_monstre->type->gibier) {
			// [10+2*(diff de niveau) + Niveau Cible ]
			$this->view->nb_px_commun = $this->view->nb_px_commun + 10+2*($this->view->retourAttaque["cible"]["niveau_cible"] - $this->view->user->niveau_braldun) + $this->view->retourAttaque["cible"]["niveau_cible"];
			if ($this->view->nb_px_commun < $this->view->nb_px_perso) {
				$this->view->nb_px_commun = $this->view->nb_px_perso;
			}
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}
}