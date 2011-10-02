<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Attaquer extends Bral_Competences_Competence
{

	function prepareCommun()
	{
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Commun');
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
				// recuperation des bralduns qui sont presents sur la case
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

			// recuperation des monstres qui sont presents sur la case
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
			$this->view->tabBralduns = $tabBralduns;
			$this->view->nBralduns = count($tabBralduns);
			$this->view->tabMonstres = $tabMonstres;
			$this->view->nMonstres = count($tabMonstres);
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

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
		}

		if (((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "")) {
			throw new Zend_Exception(get_class($this) . " Monstre invalide : " . $this->request->get("valeur_1"));
		} else {
			$idMonstre = (int)$this->request->get("valeur_1");
		}
		if (((int)$this->request->get("valeur_2") . "" != $this->request->get("valeur_2") . "")) {
			throw new Zend_Exception(get_class($this) . " Braldûn invalide : " . $this->request->get("valeur_2"));
		} else {
			$idBraldun = (int)$this->request->get("valeur_2");
		}

		if ($idMonstre == -1 && $idBraldun == -1) {
			throw new Zend_Exception(get_class($this) . " Monstre ou Braldûn invalide (==-1)");
		}

		$attaqueMonstre = false;
		$attaqueBraldun = false;
		$this->view->cibleVisible == false;
		if ($idBraldun != -1) {
			if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
				foreach ($this->view->tabBralduns as $h) {
					if ($h["id_braldun"] == $idBraldun) {
						$attaqueBraldun = true;
						break;
					}
				}
			}
			if ($attaqueBraldun === false) {
				$this->view->cibleVisible = false;
			} else {
				$this->view->cibleVisible = true;
			}
		} else {
			if (isset($this->view->tabMonstres) && count($this->view->tabMonstres) > 0) {
				foreach ($this->view->tabMonstres as $m) {
					if ($m["id_monstre"] == $idMonstre) {
						$attaqueMonstre = true;
						break;
					}
				}
			}
			if ($attaqueMonstre === false) {
				$this->view->cibleVisible = false;
			} else {
				$this->view->cibleVisible = true;
			}
		}

		if ($this->view->cibleVisible == false) {
			$this->setNbPaSurcharge(0);
			return;
		}

		if ($attaqueBraldun === true) {
			$this->view->retourAttaque = $this->attaqueBraldun($this->view->user, $idBraldun);
		} elseif ($attaqueMonstre === true) {
			$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $idMonstre);
		}

		$this->setEvenementQueSurOkJet1(false);
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();

	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_vue", "box_laban", "box_titres"));
	}

	protected function calculJetAttaque($braldun)
	{
		return Bral_Util_Attaque::calculJetAttaqueNormale($braldun);
	}

	protected function calculDegat($braldun)
	{
		return Bral_Util_Attaque::calculDegatAttaqueNormale($braldun);
	}

	public function calculPx()
	{
		parent::calculPx();
		$this->view->calcul_px_generique = false;

		if ($this->view->retourAttaque["attaqueReussie"] === true) {
			$this->view->nb_px_perso = $this->view->nb_px_perso + 1;
		}

		if ($this->view->retourAttaque["mort"] === true && $this->view->retourAttaque["idTypeGroupeMonstre"] != $this->view->config->game->groupe_monstre->type->gibier) {
			// [10+2*(diff de niveau) + Niveau Cible ]
			$this->view->nb_px_commun = floor(10 + 2 * ($this->view->retourAttaque["cible"]["niveau_cible"] - $this->view->user->niveau_braldun) + $this->view->retourAttaque["cible"]["niveau_cible"]);
			if ($this->view->nb_px_commun < $this->view->nb_px_perso) {
				$this->view->nb_px_commun = $this->view->nb_px_perso;
			}
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}
}