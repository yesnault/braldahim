<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Depiauter extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("StatsRecolteurs");
		Zend_Loader::loadClass("Bral_Util_Quete");

		$id_monstre_courant = $this->request->get("monstre");

		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCaseCadavre($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$tabCadavres = null;
		$this->view->gibier = false;
		foreach($monstres as $c) {
			if ($c["genre_type_monstre"] == 'feminin') {
				$c_taille = $c["nom_taille_f_monstre"];
			} else {
				$c_taille = $c["nom_taille_m_monstre"];
			}
			if ($c["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
				$estGibier = true;
				if ($c["id_monstre"] == $id_monstre_courant || count ($monstres) == 1) {
					$this->view->gibier = true;
				}
			} else {
				$estGibier = false;
			}
			$tabCadavres[] = array("id_monstre" => $c["id_monstre"], "nom_monstre" => $c["nom_type_monstre"], 'taille_monstre' => $c_taille, 'id_fk_taille_monstre' => $c["id_fk_taille_monstre"], 'est_gibier' => $estGibier);
		}
		$this->view->labanPlein = true;
		$poidsRestantLaban = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
		$nbPossibleDansLabanMaximum = floor($poidsRestantLaban / Bral_Util_Poids::POIDS_PEAU);
		if ($nbPossibleDansLabanMaximum > 0) {
			$this->view->labanPlein = false;
		}
		$this->view->nbPossibleDansLabanMax = $nbPossibleDansLabanMaximum;
		$charretteTable = new Charrette();
		$charetteBraldun = $charretteTable->findByIdBraldun($this->view->user->id_braldun);
		$this->view->charettePleine = true;
		if (count($charetteBraldun) == 1) {
			$this->view->possedeCharrette = true;
			$this->view->idCharrette = $charetteBraldun[0]["id_charrette"];
			$tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun);
			$nbPossibleDansCharretteMaximum = floor($tabPoidsCharrette["place_restante"] / Bral_Util_Poids::POIDS_PEAU);

			if ($nbPossibleDansCharretteMaximum > 0) {
				$this->view->charettePleine = false;
			}
			$this->view->nbPossibleDansCharretteMax = $nbPossibleDansCharretteMaximum;
		} else {
			$this->view->possedeCharrette = false;
		}
		$this->view->id_monstre_courant = $id_monstre_courant;
		$this->view->tabCadavres = $tabCadavres;
		$this->view->nCadavres = count($tabCadavres);
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Cadavre invalide : ".$this->request->get("valeur_1"));
		} else {
			$idCadavre = (int)$this->request->get("valeur_1");
		}

		$attaqueCadavre = false;
		foreach ($this->view->tabCadavres as $c) {
			if ($c["id_monstre"] == $idCadavre) {
				$attaqueCadavre = true;
				break;
			}
		}
		if ($attaqueCadavre === false) {
			throw new Zend_Exception(get_class($this)." Cadavre invalide (".$idCadavre.")");
		}

		// Verification arrivee
		$arrivee = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
		if ($arrivee < 1 || $arrivee > 3) {
			throw new Zend_Exception(get_class($this)." Destination impossible ");
		}
		
		if ($this->view->charettePleine == true && $arrivee == 1) {
			throw new Zend_Exception(get_class($this)." Charette pleine !");
		}
		
		if ($this->view->possedeCharrette == false && $arrivee == 1) {
			throw new Zend_Exception(get_class($this)." Pas de charrette !");
		}
		
		if ($this->view->labanPlein == true && $arrivee == 2) {
			throw new Zend_Exception(get_class($this)." Laban plein !");
		}
		
		// Verification preference
		$choix = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		if ($choix < 1 || $choix > 2) {
			throw new Zend_Exception(get_class($this)." Préférence impossible ");
		}
		
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculDepiauter($idCadavre, $arrivee, $choix);
		}
		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	/*
	 * La quantité de peau est fonction de la taille du monstre :
	 * petit : 1D2 unité de peau + arrondi inf (BM FOR/2) unité de peau + 1
	 * normal : 1D3 unité de peau + arrondi inf (BM FOR/2) unité de peau + 1
	 * grand : 2D3 unité de peau + arrondi inf (BM FOR/2) unité de peau + 1
	 * gigantesque : 3D3 unité de peau + arrondi inf (BM FOR/2) unité de peau + 1
	 *
	 * Petit : 1D3 + arrondi inf (BM FOR/2) unité de viande + 1
	 * Normal : 2D3 + arrondi inf (BM FOR/2) + 1
	 * Grand : 3D3 + arrondi inf (BM FOR/2) + 1
	 * Gigantesque : 4D3 + arrondi inf (BM FOR/2) + 1
	 */
	private function calculDepiauter($id_monstre, $arrivee, $pref) {

		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($id_monstre);
		$monstre = $monstreRowset;

		if ($monstre == null || $monstre["id_monstre"] == null || $monstre["id_monstre"] == "") {
			throw new Zend_Exception(get_class($this)."::calculDepiauter monstre inconnu");
		}

		$this->view->nbPeau = 0;
		$this->view->nbViande = 0;
		Zend_Loader::loadClass("TailleMonstre");
		switch ($monstre["id_fk_taille_monstre"]) {
			case TailleMonstre::ID_TAILLE_PETIT : // petit
				$coefTaille = 1;
				$this->view->nbPeau = Bral_Util_De::get_1d2();
				if ($monstre["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
					$coefA = 4;
					$this->view->nbViande = Bral_Util_De::get_1d3() + floor(($this->view->user->force_bm_braldun + $this->view->user->force_bbdf_braldun) / 2);
				} else {
					$coefA = 8;
				}
				break;
			case TailleMonstre::ID_TAILLE_NORMAL : // normal
				$coefTaille = 2;
				$this->view->nbPeau = Bral_Util_De::get_1d3();
				if ($monstre["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
					$coefA = 4;
					$this->view->nbViande = Bral_Util_De::get_2d3() + floor(($this->view->user->force_bm_braldun + $this->view->user->force_bbdf_braldun) / 2);
				} else {
					$coefA = 8;
				}
				break;
			case TailleMonstre::ID_TAILLE_GRAND : // grand
				$coefTaille = 3;
				$this->view->nbPeau = Bral_Util_De::get_2d3();
				if ($monstre["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
					$coefA = 4;
					$this->view->nbViande = Bral_Util_De::get_3d3() + floor(($this->view->user->force_bm_braldun + $this->view->user->force_bbdf_braldun) / 2);
				} else {
					$coefA = 8;
				}
				break;
			case TailleMonstre::ID_TAILLE_GIGANTESQUE : // gigantesque
				$coefTaille = 4;
				$this->view->nbPeau = Bral_Util_De::get_3d3();
				if ($monstre["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
					$coefA = 4;
					$this->view->nbViande = Bral_Util_De::get_4d3() + floor(($this->view->user->force_bm_braldun + $this->view->user->force_bbdf_braldun) / 2);
				} else {
					$coefA = 8;
				}
				break;
		}

		$nbMax = $coefA * $coefTaille;

		$this->view->effetRune = false;

		if (Bral_Util_Commun::isRunePortee($this->view->user->id_braldun, "FO")) { // s'il possède une rune FO
			$this->view->effetRune = true;
			$this->view->nbPeau = ceil($this->view->nbPeau * 1.5);
		}

		$this->view->nbPeau = $this->view->nbPeau + ($this->view->user->force_bm_braldun + $this->view->user->force_bbdf_braldun) / 2 ;
		if ($this->view->nbPeau < 0) {
			$this->view->nbPeau = 0;
		}
		$this->view->nbPeau = intval($this->view->nbPeau) + 1;

		if ($this->view->nbViande < 0) {
			$this->view->nbViande = 0;
		}

		// viande que pour les gibiers
		if ($monstre["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
			$this->view->nbViande = $this->view->nbViande + 1;
		}

		$nbViandeDansLaban = 0;
		$nbViandeDansCharrette = 0;
		$nbViandeATerre = 0;

		$nbPeauDansLaban = 0;
		$nbPeauDansCharrette = 0;
		$nbPeauATerre = 0;

		if ($this->view->nbViande > $nbMax) {
			$this->view->nbViande = $nbMax;
		}

		if ($this->view->nbPeau > $nbMax) {
			$this->view->nbPeau = $nbMax;
		}
		
		$this->view->limitationLaban = false;
		$this->view->limitationCharrette = false;
		
		switch ($arrivee) {
			case 1 : //charrette
				if ($this->view->nbPeau + $this->view->nbViande > $this->view->nbPossibleDansCharretteMax) {
					if ( $pref == 1) {
						if ( $this->view->nbPeau > $this->view->nbPossibleDansCharretteMax ) {
							$nbPeauDansCharrette = $this->view->nbPossibleDansCharretteMax;
							$nbPeauATerre = $this->view->nbPeau - $nbPeauDansCharrette;
						} else {
							$nbPeauDansCharrette = $this->view->nbPeau;
						}
						if ($this->view->nbViande > $this->view->nbPossibleDansCharretteMax - $nbPeauDansCharrette) {
							$nbViandeDansCharrette = $this->view->nbPossibleDansCharretteMax - $nbPeauDansCharrette;
							$nbViandeATerre = $this->view->nbViande - $nbViandeDansCharrette;
						} else {
							$nbViandeDansCharrette = $this->view->nbViande;
						}
					}
					else {
						if ( $this->view->nbViande > $this->view->nbPossibleDansCharretteMax ) {
							$nbViandeDansCharrette = $this->view->nbPossibleDansCharretteMax;
							$nbViandeATerre = $this->view->nbViande - $nbViandeDansCharrette;
							
						} else {
							$nbViandeDansCharrette = $this->view->nbViande;
						}
						if ($this->view->nbPeau > $this->view->nbPossibleDansCharretteMax - $nbViandeDansCharrette) {
							$nbPeauDansCharrette = $this->view->nbPossibleDansCharretteMax - $nbViandeDansCharrette;
							$nbPeauATerre = $this->view->nbPeau - $nbPeauDansCharrette;
						} else {
							$nbPeauDansCharrette = $this->view->nbPeau;
						}						
					}
					$this->view->limitationCharrette = true;
				}
				else {
					$nbPeauDansCharrette = $this->view->nbPeau;
					$nbViandeDansCharrette = $this->view->nbViande;
				}	
				
				if ($nbPeauDansCharrette > 0) {
					$charretteTable = new Charrette();
					$data = array(
						'id_fk_braldun_charrette' => $this->view->user->id_braldun,
						'quantite_peau_charrette' => $nbPeauDansCharrette,
					);
					$charretteTable->insertOrUpdate($data);
				}
		
				Zend_Loader::loadClass("TypeIngredient");
				if ($nbViandeDansCharrette > 0) {
					Zend_Loader::loadClass("CharretteIngredient");
					$charretteTable = new CharretteIngredient();
					$data = array(
						'id_fk_type_charrette_ingredient' => TypeIngredient::ID_TYPE_VIANDE_FRAICHE,
						'id_fk_charrette_ingredient' => $this->view->idCharrette,
						'quantite_charrette_ingredient' => $nbViandeDansCharrette,
					);
					$charretteTable->insertOrUpdate($data);
				}
				Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
				break;
			case 2 : //laban
				if ($this->view->nbPeau + $this->view->nbViande > $this->view->nbPossibleDansLabanMax) {
					if ( $pref == 1) {
						if ( $this->view->nbPeau > $this->view->nbPossibleDansLabanMax ) {
							$nbPeauDansLaban = $this->view->nbPossibleDansLabanMax;
							$nbPeauATerre = $this->view->nbPeau - $nbPeauDansLaban;
						} else {
							$nbPeauDansLaban = $this->view->nbPeau;
						}
						if ($this->view->nbViande > $this->view->nbPossibleDansLabanMax - $nbPeauDansLaban ) {
							$nbViandeDansLaban = $this->view->nbPossibleDansLabanMax - $nbPeauDansLaban;
							$nbViandeATerre = $this->view->nbViande - $nbViandeDansLaban;
						} else {
							$nbViandeDansLaban = $this->view->nbViande;
						}
					}
					else {
						if ( $this->view->nbViande > $this->view->nbPossibleDansLabanMax ) {
							$nbViandeDansLaban = $this->view->nbPossibleDansLabanMax;
							$nbViandeATerre = $this->view->nbViande - $nbViandeDansLaban;						
						} else {
							$nbViandeDansLaban = $this->view->nbViande;
						}
						if ($this->view->nbPeau > $this->view->nbPossibleDansLabanMax - $nbViandeDansLaban) {
							$nbPeauDansLaban = $this->view->nbPossibleDansLabanMax - $nbViandeDansLaban;
							$nbPeauATerre = $this->view->nbPeau - $nbPeauDansLaban;
						} else {
							$nbPeauDansLaban = $this->view->nbPeau;
						}						
					}
					$this->view->limitationLaban = true;
				}
				else {
					$nbPeauDansLaban = $this->view->nbPeau;
					$nbViandeDansLaban = $this->view->nbViande;
				}
				
				if ($nbPeauDansLaban > 0) {
					$labanTable = new Laban();
					$data = array(
						'id_fk_braldun_laban' => $this->view->user->id_braldun,
						'quantite_peau_laban' => $nbPeauDansLaban,
					);
					$labanTable->insertOrUpdate($data);
				}
		
				Zend_Loader::loadClass("TypeIngredient");
				if ($nbViandeDansLaban > 0) {
					Zend_Loader::loadClass("LabanIngredient");
					$labanTable = new LabanIngredient();
					$data = array(
						'id_fk_type_laban_ingredient' => TypeIngredient::ID_TYPE_VIANDE_FRAICHE,
						'id_fk_braldun_laban_ingredient' => $this->view->user->id_braldun,
						'quantite_laban_ingredient' => $nbViandeDansLaban,
					);
					$labanTable->insertOrUpdate($data);
				}
				
				break;
			case 3 : //sol
				$nbPeauATerre = $this->view->nbPeau;
				$nbViandeATerre = $this->view->nbViande;
				break;
		}

		if ($nbViandeATerre > 0) {
			Zend_Loader::loadClass("ElementIngredient");
			Zend_Loader::loadClass("TypeIngredient");
			$elementTable = new ElementIngredient();
			$data = array(
				'id_fk_type_element_ingredient' => TypeIngredient::ID_TYPE_VIANDE_FRAICHE,
				'quantite_element_ingredient' => $nbViandeATerre,
				'x_element_ingredient' => $this->view->user->x_braldun,
				'y_element_ingredient' => $this->view->user->y_braldun,
				'z_element_ingredient' => $this->view->user->z_braldun,
			);
			$elementTable->insertOrUpdate($data);
		}

		if ($nbPeauATerre > 0) {
			Zend_Loader::loadClass("Element");
			$elementTable = new Element();
			$data = array(
				"quantite_peau_element" => $nbPeauATerre,
				"x_element" => $this->view->user->x_braldun,
				"y_element" => $this->view->user->y_braldun,
				"z_element" => $this->view->user->z_braldun,
			);
			$elementTable->insertOrUpdate($data);
		}

		$statsRecolteurs = new StatsRecolteurs();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataRecolteurs["niveau_braldun_stats_recolteurs"] = $this->view->user->niveau_braldun;
		$dataRecolteurs["id_fk_braldun_stats_recolteurs"] = $this->view->user->id_braldun;
		$dataRecolteurs["mois_stats_recolteurs"] = date("Y-m-d", $moisEnCours);
		$dataRecolteurs["nb_peau_stats_recolteurs"] = $this->view->nbPeau;
		$dataRecolteurs["nb_viande_stats_recolteurs"] = $this->view->nbViande;
		$statsRecolteurs->insertOrUpdate($dataRecolteurs);

		$where = "id_monstre=".$id_monstre;
		$data = array('est_depiaute_cadavre' => 'oui');
		$monstreTable->update($data, $where);

		$idTypeEvenement = $this->view->config->game->evenements->type->competence;
		$libelleMonstreGibier = "monstre";
		if ($monstre["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
			$libelleMonstreGibier = "gibier";
		}

		$details = "[b".$this->view->user->id_braldun."] a dépiauté le ".$libelleMonstreGibier." [m".$id_monstre."]";
		$this->setDetailsEvenement($details, $idTypeEvenement);

		Bral_Util_Evenement::majEvenements($id_monstre, $idTypeEvenement, $details, "", $monstre["niveau_monstre"], "monstre");
			
		$this->view->estQueteEvenement = Bral_Util_Quete::etapeCollecter($this->view->user, $this->competence["id_fk_metier_competence"]);
		$this->view->nbPeauCharrette = $nbPeauDansCharrette;
		$this->view->nbViandeCharrette = $nbViandeDansCharrette;
		$this->view->nbPeauLaban = $nbPeauDansLaban;
		$this->view->nbViandeLaban = $nbViandeDansLaban;
		$this->view->nbPeauSol = $nbPeauATerre;
		$this->view->nbViandeSol = $nbViandeATerre;
		$this->view->arrivee = $arrivee;
		
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue", "box_laban", "box_charrette"));
	}
}
