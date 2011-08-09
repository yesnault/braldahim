<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
/*
 * La distance de charge est basé sur la vigueur avec une réduction suivant le terrain de départ :
 * En plaine :
 * VIG 0-2 -> 1 case
 * 3-5 -> 2 cases
 * 6-8 -> 3 cases
 * 9-11 -> 4 cases
 * 12-14 -> 5 cases
 * 15+  -> 6 cases
 * En forêt un malus de -1, ne marais et montagne un malus de -2 sur la distance est apliqué (minimum 1).
 * La distance de charge est borné par la vue.
 *
 * Le jet d'attaque d'une charge est différent : (0.5 jet AGI) + BM + bonus arme
 *
 * Le jet de dégats diffère aussi : jet FOR + BM FOR + bonus arme + jet VIG + BM VIG
 * cas du critique :
 * 1.5(jet FOR) + BM FOR + bonus arme + jet VIG + BM VIG
 *
 * On ne peut pas charger sur une cible qui est sur sa propre case.
 *
 * On ne peut pas charger si l'une des cases entre le chargeur et le charger est une palissade.
 *
 */
class Bral_Competences_Charger extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass('Bral_Util_Attaque');
		Zend_Loader::loadClass("BraldunEquipement");
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Eau");

		$charretteTable = new Charrette();
		$nombreCharrette = $charretteTable->countByIdBraldun($this->view->user->id_braldun);

		$armeTirPortee = false;
		$braldunEquipement = new BraldunEquipement();
		$equipementPorteRowset = $braldunEquipement->findByTypePiece($this->view->user->id_braldun, "arme_tir");

		$this->view->possedeCharrette = false;
		$this->view->chargerPossible = false;

		if ($nombreCharrette > 0) {
			$this->view->possedeCharrette = true;
			Zend_Loader::loadClass("Bral_Util_Charrette");
			$this->view->chargerPossible = Bral_Util_Charrette::calculCourrirChargerPossible($this->view->user->id_braldun);
			if ($this->view->chargerPossible == false) {
				return;
			}
		} else if ($nombreCharrette <= 0) {
			$this->view->chargerPossible = true;
		} else if ($nombreCharrette > 1) {
			throw new Zend_Exception(get_class($this) . " NB Charrette invalide idh:" . $this->view->user->id_braldun);
		}

		if (count($equipementPorteRowset) > 0) {
			$armeTirPortee = true;
		} else if ($this->view->user->est_intangible_braldun == "non") {
			$this->view->charge_nb_cases = floor($this->view->user->vigueur_base_braldun / 3) + 1;
			if ($this->view->charge_nb_cases > 6) {
				$this->view->charge_nb_cases = 6;
			}

			Zend_Loader::loadClass("Bosquet");
			$bosquetTable = new Bosquet();
			$nombreBosquets = $bosquetTable->countByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

			//En bosquet un malus de -1 en distance, en marais et montagne un malus de -2 sur la distance est appliqué
			$environnement = Bral_Util_Commun::getEnvironnement($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
			if ($environnement == "montagne" || $environnement == "marais") {
				$this->view->charge_nb_cases = $this->view->charge_nb_cases - 2;
			} elseif ($nombreBosquets > 1) {
				$this->view->charge_nb_cases = $this->view->charge_nb_cases - 1;
			}

			$eauTable = new Eau();
			$eaux = $eauTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
			if (count($eaux) >= 1) {
				$this->view->charge_nb_cases = $this->view->charge_nb_cases - 3;
			}

			//minimum de distance de charge à 1 case dans tous les cas
			if ($this->view->charge_nb_cases < 1) {
				$this->view->charge_nb_cases = 1;
			}

			//La distance de charge est bornée par la VUE
			$vue = Bral_Util_Commun::getVueBase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun) + $this->view->user->vue_bm_braldun;
			if ($vue < $this->view->charge_nb_cases) {
				$this->view->charge_nb_cases = $vue;
			}

			Zend_Loader::loadClass("Bral_Util_Dijkstra");
			$dijkstra = new Bral_Util_Dijkstra();
			$dijkstra->calcul($this->view->charge_nb_cases, $this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

			$x_min = $this->view->user->x_braldun - $this->view->charge_nb_cases;
			$x_max = $this->view->user->x_braldun + $this->view->charge_nb_cases;
			$y_min = $this->view->user->y_braldun - $this->view->charge_nb_cases;
			$y_max = $this->view->user->y_braldun + $this->view->charge_nb_cases;

			$tabValide = null;
			$numero = -1;
			for ($j = $y_max; $j >= $y_min; $j--) {
				for ($i = $x_min; $i <= $x_max; $i++) {
					$numero++;
					$tabValide[$i][$j] = true;
					if ($dijkstra->getDistance($numero) > $this->view->charge_nb_cases) {
						$tabValide[$i][$j] = false;
					}
				}
			}

			// On ne peut pas charger sur une cible qui est sur sa propre case.
			$tabValide[$this->view->user->x_braldun][$this->view->user->y_braldun] = false;

			$tabBralduns = null;
			$tabMonstres = null;

			$estRegionPvp = Bral_Util_Attaque::estRegionPvp($this->view->user->x_braldun, $this->view->user->y_braldun);

			if ($estRegionPvp ||
				$this->view->user->points_gredin_braldun > 0 || $this->view->user->points_redresseur_braldun > 0
			) {
				// recuperation des bralduns qui sont presents sur la vue
				$braldunTable = new Braldun();
				$bralduns = $braldunTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_braldun, $this->view->user->id_braldun, false);

				foreach ($bralduns as $h) {
					if ($tabValide[$h["x_braldun"]][$h["y_braldun"]] === true) {
						$tab = array(
							'id_braldun' => $h["id_braldun"],
							'nom_braldun' => $h["nom_braldun"],
							'prenom_braldun' => $h["prenom_braldun"],
							'x_braldun' => $h["x_braldun"],
							'y_braldun' => $h["y_braldun"],
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
			}

			// recuperation des monstres qui sont presents sur la vue
			$monstreTable = new Monstre();
			$monstres = $monstreTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_braldun);
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
				if ($tabValide[$m["x_monstre"]][$m["y_monstre"]] === true) {
					$tabMonstres[] = array(
						'id_monstre' => $m["id_monstre"],
						'nom_monstre' => $m["nom_type_monstre"],
						'taille_monstre' => $m_taille,
						'niveau_monstre' => $m["niveau_monstre"],
						'x_monstre' => $m["x_monstre"],
						'y_monstre' => $m["y_monstre"],
						'est_gibier' => $estGibier,
					);
				}
			}

			$this->view->tabBralduns = $tabBralduns;
			$this->view->nBralduns = count($tabBralduns);
			$this->view->tabMonstres = $tabMonstres;
			$this->view->nMonstres = count($tabMonstres);
			$this->view->estRegionPvp = $estRegionPvp;
		}
		$this->view->armeTirPortee = $armeTirPortee;
	}

	function prepareFormulaire() {
		// rien à faire ici
	}

	function prepareResultat() {

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

		if ($idMonstre != -1 && $idBraldun != -1) {
			throw new Zend_Exception(get_class($this) . " Monstre ou Braldûn invalide (!=-1)");
		}

		$attaqueMonstre = false;
		$attaqueBraldun = false;
		$this->view->cibleVisible = false;
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

		$this->calculJets();
		if ($this->view->okJet1 === true) {
			if ($attaqueBraldun === true) {
				$this->view->retourAttaque = $this->attaqueBraldun($this->view->user, $idBraldun);
			} elseif ($attaqueMonstre === true) {
				$this->view->retourAttaque = $this->attaqueMonstre($this->view->user, $idMonstre);
			} else {
				throw new Zend_Exception(get_class($this) . " Erreur inconnue");
			}
			/* on va à la position de la cible. */
			$this->view->user->x_braldun = $this->view->retourAttaque["cible"]["x_cible"];
			$this->view->user->y_braldun = $this->view->retourAttaque["cible"]["y_cible"];
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculFinMatchSoule();
		$this->majBraldun();

		Zend_Loader::loadClass("Bral_Util_Filature");
		Bral_Util_Filature::action($this->view->user, $this->view);
	}

	function getListBoxRefresh() {
		$tab = array("box_vue", "box_competences", "box_laban", "box_lieu", "box_titres", "box_blabla");
		if ($this->view->user->est_soule_braldun == "oui") {
			$tab[] = "box_soule";
		}
		return $this->constructListBoxRefresh($tab);
	}

	/*
	 * Le jet d'attaque d'une charge est différent : (0.5 jet AGI) + BM + bonus arme
	 */
	protected function calculJetAttaque($braldun) {
		$jetAttaquant = 0;

		$nbDe = $this->view->config->game->base_agilite + $braldun->agilite_base_braldun;
		$jetAttaquant = Bral_Util_De::getLanceDe6($nbDe);
		$jetAttaquantDetails = "0.5x(" . $nbDe . "D6)";

		$jetAttaquant = floor(0.5 * $jetAttaquant + $braldun->agilite_bm_braldun + $braldun->agilite_bbdf_braldun + $braldun->bm_attaque_braldun);
		$jetAttaquantDetails .= Bral_Util_String::getSigneValeur($braldun->agilite_bm_braldun);
		$jetAttaquantDetails .= Bral_Util_String::getSigneValeur($braldun->agilite_bbdf_braldun);
		$jetAttaquantDetails .= Bral_Util_String::getSigneValeur($braldun->bm_attaque_braldun);
		if ($jetAttaquant < 0) {
			$jetAttaquant = 0;
		}
		$tabJetAttaquant["jet"] = $jetAttaquant;
		$tabJetAttaquant["details"] = $jetAttaquantDetails;
		return $tabJetAttaquant;
	}

	/*
	 * Le jet de dégats diffère aussi :
	 * jet FOR + BM FOR + bonus arme + jet VIG + BM VIG
	 * cas du critique :
	 * 1.5(jet FOR) + BM FOR + bonus arme + jet VIG + BM VIG
	 */
	protected function calculDegat($braldun) {
		$jetDegat["critique"] = 0;
		$jetDegat["noncritique"] = 0;
		$coefCritique = 1.5;

		$nbDeForce = $this->view->config->game->base_force + $braldun->force_base_braldun;
		$jetDetailsNonCritiqueForce = $nbDeForce . "D6";
		$jetDetailsCritiqueForce = $coefCritique . "x(" . $nbDeForce . "D6)";

		$nbDeVigueur = $this->view->config->game->base_vigueur + $braldun->vigueur_base_braldun;
		$jetDetailsVigueur = "+" . $nbDeVigueur . "D6";

		$jetDegat["critique"] = Bral_Util_De::getLanceDe6($nbDeForce * $coefCritique);
		$jetDegat["critique"] = $jetDegat["critique"] + $this->view->user->force_bm_braldun + $this->view->user->force_bbdf_braldun;

		$jetDegat["noncritique"] = Bral_Util_De::getLanceDe6($nbDeForce);
		$jetDegat["noncritique"] = $jetDegat["noncritique"] + $this->view->user->force_bm_braldun + $this->view->user->force_bbdf_braldun;

		$jetDetails = Bral_Util_String::getSigneValeur($braldun->force_bm_braldun);
		$jetDetails .= Bral_Util_String::getSigneValeur($braldun->force_bbdf_braldun);

		$jetDegat["critique"] = $jetDegat["critique"] + Bral_Util_De::getLanceDe6($nbDeVigueur);
		$jetDegat["noncritique"] = $jetDegat["noncritique"] + Bral_Util_De::getLanceDe6($nbDeVigueur);

		$jetDegat["critique"] = floor($jetDegat["critique"] + $braldun->vigueur_bm_braldun + $braldun->vigueur_bbdf_braldun + $braldun->bm_degat_braldun);
		$jetDegat["noncritique"] = floor($jetDegat["noncritique"] + $braldun->vigueur_bm_braldun + $braldun->vigueur_bbdf_braldun + $braldun->bm_degat_braldun);

		$jetDetails .= Bral_Util_String::getSigneValeur($braldun->vigueur_bm_braldun);
		$jetDetails .= Bral_Util_String::getSigneValeur($braldun->vigueur_bbdf_braldun);
		$jetDetails .= Bral_Util_String::getSigneValeur($braldun->bm_degat_braldun);

		$jetDegat["critiquedetails"] = $jetDetailsCritiqueForce . $jetDetailsVigueur . $jetDetails;
		$jetDegat["noncritiquedetails"] = $jetDetailsNonCritiqueForce . $jetDetailsVigueur . $jetDetails;

		if ($jetDegat["critique"] < 0) {
			$jetDegat["critique"] = 0;
		}

		if ($jetDegat["noncritique"] < 0) {
			$jetDegat["noncritique"] = 0;
		}

		return $jetDegat;
	}

	public function calculPx() {
		parent::calculPx();
		$this->view->calcul_px_generique = false;

		if ($this->view->retourAttaque["attaqueReussie"] === true) {
			$this->view->nb_px_perso = $this->view->nb_px_perso + 1;
		}

		if ($this->view->retourAttaque["mort"] === true && $this->view->retourAttaque["idTypeGroupeMonstre"] != $this->view->config->game->groupe_monstre->type->gibier) {
			// [10+2*(diff de niveau) + Niveau Cible ]
			$this->view->nb_px_commun = 10 + 2 * ($this->view->retourAttaque["cible"]["niveau_cible"] - $this->view->user->niveau_braldun) + $this->view->retourAttaque["cible"]["niveau_cible"];
			if ($this->view->nb_px_commun < $this->view->nb_px_perso) {
				$this->view->nb_px_commun = $this->view->nb_px_perso;
			}
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}
}