<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */

/*
 * Attaque : 0.5*(jet d'AGI)+BM AGI + bonus arme att
 * dégats : 0.5*(jet FOR)+BM FOR+ bonus arme dégats
 * dégats critiques : (1.5*(0.5*FOR))+BM FOR+bonus arme dégats
 */
class Bral_Competences_Frenesie extends Bral_Competences_Competence
{

	private $_coef = 1;
	private $_1ereAttaqueReussie = false;

	function prepareCommun()
	{
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Attaque');
		Zend_Loader::loadClass("BraldunEquipement");

		$tabBralduns = null;
		$tabMonstres = null;

		$armeTirPortee = false;
		$braldunEquipement = new BraldunEquipement();
		$equipementPorteRowset = $braldunEquipement->findByTypePiece($this->view->user->id_braldun, "arme_tir");

		if (count($equipementPorteRowset) > 0) {
			$armeTirPortee = true;
		} else if ($this->view->user->est_intangible_braldun == "non") {
			$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_braldun, $this->view->user->y_braldun);

			if ($estRegionPvp ||
				$this->view->user->points_gredin_braldun > 0 || $this->view->user->points_redresseur_braldun > 0
			) {
				// recuperation des bralduns qui sont presents sur la vue
				$braldunTable = new Braldun();
				$bralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun, false);
				foreach ($bralduns as $h) {
					$tab = array(
						'id_braldun' => $h["id_braldun"],
						'nom_braldun' => $h["nom_braldun"],
						'prenom_braldun' => $h["prenom_braldun"],
					);
					if (!$estRegionPvp) { // pve
						if ($h["points_gredin_braldun"] > 0 || $h["points_redresseur_braldun"] > 0) {
							$tabBralduns[] = $tab;
						}
					} elseif ($this->view->user->est_soule_braldun == 'non' ||
						($this->view->user->est_soule_braldun == 'oui' && $h["soule_camp_braldun"] != $this->view->user->soule_camp_braldun)
					) {
						$tabBralduns[] = $tab;
					}
				}
			}

			// recuperation des monstres qui sont presents sur la vue
			$monstreTable = new Monstre();
			$monstres = $monstreTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
			foreach ($monstres as $m) {
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

	function prepareFormulaire()
	{
		// rien à faire ici
	}

	function prepareResultat()
	{

		$this->view->cibleVisible = true;
		if ($this->view->nBralduns == 0 && $this->view->nMonstres == 0) {
			$this->setNbPaSurcharge(0);
			$this->view->cibleVisible = false;
			return;
		}

		if ($this->view->assezDePa == true && $this->view->armeTirPortee == false &&
			($this->view->nBralduns > 0 || $this->view->nMonstres > 0) && $this->view->user->est_intangible_braldun == 'non'
		) {
			// OK
		} else {
			throw new Zend_Exception("Erreur Frenesie");
		}

		// calcul des jets
		$this->calculJets();
		$retours = null;

		if (Bral_Util_Commun::isRunePortee($this->view->user->id_braldun, "EM")) {
			$this->view->effetRune = true;
			$coefFrenesie = 0.9;
		} else {
			$coefFrenesie = 0.8;
		}

		if ($this->view->okJet1 === true) {
			$cibles = null;
			if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
				foreach ($this->view->tabBralduns as $b) {
					$cibles[] = $b;
				}
			}

			if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
				foreach ($this->view->tabMonstres as $m) {
					$cibles[] = $m;
				}
			}

			if ($cibles != null && count($cibles) > 0) {
				shuffle($cibles);
				foreach ($cibles as $k => $c) {
					if (array_key_exists("id_braldun", $c)) { // Bradûns
						$this->retourAttaque = $this->attaqueBraldun($this->view->user, $c["id_braldun"], true, false, true);
					} else { // monstres
						$this->retourAttaque = $this->attaqueMonstre($this->view->user, $c["id_monstre"], false, true);
					}
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

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_competences", "box_vue", "box_lieu", "box_titres"));
	}

	protected function calculJetAttaque($braldun)
	{
		$nbDe = $this->view->config->game->base_agilite + $braldun->agilite_base_braldun;
		$jetAttaquant = Bral_Util_De::getLanceDe6($nbDe);
		$jetAttaquantDetails = "(" . $nbDe . "D6";

		$jetAttaquant = $jetAttaquant + $braldun->agilite_bm_braldun + $braldun->agilite_bbdf_braldun + $braldun->bm_attaque_braldun;
		$jetAttaquantDetails .= Bral_Util_String::getSigneValeur($braldun->agilite_bm_braldun);
		$jetAttaquantDetails .= Bral_Util_String::getSigneValeur($braldun->agilite_bbdf_braldun);
		$jetAttaquantDetails .= Bral_Util_String::getSigneValeur($braldun->bm_attaque_braldun);

		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}

		$jetAttaquant = floor($this->_coef * $jetAttaquant);
		$jetAttaquantDetails .= ")x" . $this->_coef;

		$tabJetAttaquant["jet"] = $jetAttaquant;
		$tabJetAttaquant["details"] = $jetAttaquantDetails;
		return $tabJetAttaquant;
	}

	protected function calculDegat($braldun)
	{
		$this->view->effetRune = false;

		$jetsDegat["critique"] = 0;
		$jetsDegat["noncritique"] = 0;
		$jetDegatForce = 0;
		$coefCritique = 1.5;

		$nbDe = $this->view->config->game->base_force + $braldun->force_base_braldun;
		$jetDegatForce = Bral_Util_De::getLanceDe6($nbDe);

		$jetsDegat["critique"] = $coefCritique * $jetDegatForce;
		$jetsDegat["noncritique"] = $jetDegatForce;

		$jetDetailsNonCritique = $nbDe . "D6";
		$jetDetailsCritique = $coefCritique . "x(" . $nbDe . "D6)";

		$jetsDegat["critique"] = floor($this->_coef * ($jetsDegat["critique"] + $braldun->force_bm_braldun + $braldun->force_bbdf_braldun + $braldun->bm_degat_braldun));
		$jetsDegat["noncritique"] = floor($this->_coef * ($jetsDegat["noncritique"] + $braldun->force_bm_braldun + $braldun->force_bbdf_braldun + $braldun->bm_degat_braldun));

		$jetDetails = Bral_Util_String::getSigneValeur($braldun->force_bm_braldun);
		$jetDetails .= Bral_Util_String::getSigneValeur($braldun->force_bbdf_braldun);
		$jetDetails .= Bral_Util_String::getSigneValeur($braldun->bm_degat_braldun);

		$jetsDegat["critiquedetails"] = "(" . $jetDetailsCritique . $jetDetails . ")x" . $this->_coef;
		$jetsDegat["noncritiquedetails"] = "(" . $jetDetailsNonCritique . $jetDetails . ")x" . $this->_coef;

		if ($jetsDegat["critique"] < 0) {
			$jetsDegat["critique"] = 0;
		}

		if ($jetsDegat["noncritique"] < 0) {
			$jetsDegat["noncritique"] = 0;
		}

		return $jetsDegat;
	}

	public function calculPx()
	{

		if ($this->_coef == 1) { // pour la première cible
			Bral_Util_Log::attaque()->trace("Frenesie - idB:" . $this->view->user->id_braldun . " - 1er attaque -");
			$this->view->nb_px_commun = 0;
			parent::calculPx();

			$this->view->calcul_px_generique = false;

			Bral_Util_Log::attaque()->trace("Frenesie - idB:" . $this->view->user->id_braldun . " - 1er attaque - nb_px_perso:" . $this->view->nb_px_perso);
		}

		if ($this->_1ereAttaqueReussie == false && $this->retourAttaque["attaqueReussie"] === true) {
			$this->_1ereAttaqueReussie = true;
			$this->view->nb_px_perso = $this->view->nb_px_perso + 1;
			Bral_Util_Log::attaque()->trace("Frenesie - idB:" . $this->view->user->id_braldun . " - Touche nb_px_perso:" . $this->view->nb_px_perso);
		} else {
			Bral_Util_Log::attaque()->trace("Frenesie - idB:" . $this->view->user->id_braldun . " - Esquive de la cible");
		}

		if ($this->retourAttaque["mort"] === true && $this->retourAttaque["idTypeGroupeMonstre"] != $this->view->config->game->groupe_monstre->type->gibier) {
			// [10+2*(diff de niveau) + Niveau Cible ]
			$this->view->nb_px_commun = $this->view->nb_px_commun + 10 + 2 * ($this->retourAttaque["cible"]["niveau_cible"] - $this->view->user->niveau_braldun) + $this->retourAttaque["cible"]["niveau_cible"];
			if ($this->view->nb_px_commun < 0) {
				$this->view->nb_px_commun = 0;
			}
			Bral_Util_Log::attaque()->trace("Frenesie - idB:" . $this->view->user->id_braldun . " - Mort Cible nivCible:" . $this->retourAttaque["cible"]["niveau_cible"] . " coef:" . $this->_coef . " nb_px_commun:" . $this->view->nb_px_commun);
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
		Bral_Util_Log::attaque()->trace("Frenesie - idB:" . $this->view->user->id_braldun . " - nb_px_perso:" . $this->view->nb_px_perso . " sortie calculPX");
	}
}