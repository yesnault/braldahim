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
class Bral_Competences_Cueillir extends Bral_Competences_Competence {

	private $_tabPlantes = null;

	function prepareCommun() {
		Zend_Loader::loadClass('Plante');
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('TypePartieplante');
		Zend_Loader::loadClass("Bral_Util_Quete");

		$tabPlantes = null;
		$this->view->planteOk = false;

		$typePartiePlanteTable = new TypePartieplante();
		$typePartiePlanteRowset = $typePartiePlanteTable->fetchall();
		foreach($typePartiePlanteRowset as $p) {
			$tabPartiePlante[$p->id_type_partieplante]["id"] = $p->id_type_partieplante;
			$tabPartiePlante[$p->id_type_partieplante]["nom"] = $p->nom_type_partieplante;
			$tabPartiePlante[$p->id_type_partieplante]["nom_systeme"] = $p->nom_systeme_type_partieplante;
			$tabPartiePlante[$p->id_type_partieplante]["description"] = $p->description_type_partieplante;
		}

		$planteTable = new Plante();
		$plantes = $planteTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		if (count($plantes) > 0) {
			$this->view->planteOk = true;
		}

		foreach ($plantes as $p) {
			if ($p["partie_1_plante"] > 0) $nom_partie_1 = $tabPartiePlante[$p["id_fk_partieplante1_type_plante"]]["nom"]; else $nom_partie_1="";
			if ($p["partie_2_plante"] > 0) $nom_partie_2 = $tabPartiePlante[$p["id_fk_partieplante2_type_plante"]]["nom"]; else $nom_partie_2="";
			if ($p["partie_3_plante"] > 0) $nom_partie_3 = $tabPartiePlante[$p["id_fk_partieplante3_type_plante"]]["nom"]; else $nom_partie_3="";
			if ($p["partie_4_plante"] > 0) $nom_partie_4 = $tabPartiePlante[$p["id_fk_partieplante4_type_plante"]]["nom"]; else $nom_partie_4="";
			$this->_tabPlantes[] = array("id_plante" => $p["id_plante"],
				"nom_type" => $p["nom_type_plante"],
				"categorie" => $p["categorie_type_plante"],
				"id_fk_type_plante" => $p["id_fk_type_plante"],
				"partie_1_plante" => $p["partie_1_plante"],
				"partie_2_plante" => $p["partie_2_plante"],
				"partie_3_plante" => $p["partie_3_plante"],
				"partie_4_plante" => $p["partie_4_plante"],
				"id_fk_partie_1" => $p["id_fk_partieplante1_type_plante"],
				"id_fk_partie_2" => $p["id_fk_partieplante2_type_plante"],
				"id_fk_partie_3" => $p["id_fk_partieplante3_type_plante"],
				"id_fk_partie_4" => $p["id_fk_partieplante4_type_plante"],
				"nom_partie_1" => $nom_partie_1,
				"nom_partie_2" => $nom_partie_2,
				"nom_partie_3" => $nom_partie_3,
				"nom_partie_4" => $nom_partie_4
			);
		}
		
		$this->view->plantes = $this->_tabPlantes;
		
		$this->view->labanPlein = true;
		$poidsRestantLaban = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
		$nbPossibleDansLabanMaximum = floor($poidsRestantLaban / Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);
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
			$nbPossibleDansCharretteMaximum = floor($tabPoidsCharrette["place_restante"] / Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);

			if ($nbPossibleDansCharretteMaximum > 0) {
				$this->view->charettePleine = false;
			}
			$this->view->nbPossibleDansCharretteMax = $nbPossibleDansCharretteMaximum;
		} else {
			$this->view->possedeCharrette = false;
		}
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		Zend_Loader::loadClass('StatsRecolteurs');

		$idPlante = intval($this->request->get("valeur_1"));

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
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
		
		// Verification de la plante
		$planteOk = false;
		if ($this->_tabPlantes != null) {
			foreach ($this->_tabPlantes as $p) {
				if ($p["id_plante"] == $idPlante) {
					$planteOk = true;
					$plante = $p;
					break;
				}
			}
		}

		if ($planteOk === false) {
			throw new Zend_Exception(get_class($this)." Plante invalide : ".$idPlante);
		}

		// calcul des jets
		$this->calculJets();
		$quantiteExtraite = $this->calculQuantiteAExtraire();

		for ($i=1; $i<=4; $i++) {
			$tab[$i]["estVide"] = true;
			$tab[$i]["quantite"] = 0;
			$tab[$i]["id_fk"] = -1;
			$cueillette[$i]["quantite"] = 0;
			$cueillette[$i]["id_fk"] = -1;
			$cueillette[$i]["id_type_plante"] = $plante["id_fk_type_plante"];
			if ($i == 1 && $plante["partie_1_plante"] > 0) {
				$tab[$i]["id_fk"] = $plante["id_fk_partie_1"];
				$tab[$i]["quantite"] = $plante["partie_1_plante"];
				$tab[$i]["estVide"] = false;
				$cueillette[$i]["id_fk"] = $plante["id_fk_partie_1"];
				$cueillette[$i]["nom_partie"] = $plante["nom_partie_1"];
			}
			if ($i == 2 && $plante["partie_2_plante"] > 0) {
				$tab[$i]["id_fk"] = $plante["id_fk_partie_2"];
				$tab[$i]["quantite"] = $plante["partie_2_plante"];
				$tab[$i]["estVide"] = false;
				$cueillette[$i]["id_fk"] = $plante["id_fk_partie_2"];
				$cueillette[$i]["nom_partie"] = $plante["nom_partie_2"];
			}
			if ($i == 3 && $plante["partie_3_plante"] > 0) {
				$tab[$i]["id_fk"] = $plante["id_fk_partie_3"];
				$tab[$i]["quantite"] = $plante["partie_3_plante"];
				$tab[$i]["estVide"] = false;
				$cueillette[$i]["id_fk"] = $plante["id_fk_partie_3"];
				$cueillette[$i]["nom_partie"] = $plante["nom_partie_3"];
			}
			if ($i == 4 && $plante["partie_4_plante"] > 0) {
				$tab[$i]["id_fk"] = $plante["id_fk_partie_4"];
				$tab[$i]["quantite"] = $plante["partie_4_plante"];
				$tab[$i]["estVide"] = false;
				$cueillette[$i]["id_fk"] = $plante["id_fk_partie_4"];
				$cueillette[$i]["nom_partie"] = $plante["nom_partie_4"];
			}
		}

		$planteADetruire = false;
		for ($i=1; $i<=$quantiteExtraite; $i++) {
			$idx = Bral_Util_De::get_de_specifique(1, 4);
			if ($tab[$idx]["quantite"] > 0 && $tab[$idx]["estVide"] === false) {
				$cueillette[$idx]["quantite"] = $cueillette[$idx]["quantite"] + 1;
				$tab[$idx]["quantite"] = $tab[$idx]["quantite"] - 1;
				if ($tab[$idx]["quantite"] < 1) {
					$tab[$idx]["estVide"] = true;
					if ($tab[1]["estVide"] === true && $tab[2]["estVide"] === true  &&
					$tab[3]["estVide"] === true && $tab[4]["estVide"] === true ) {
						$planteADetruire = true;
						break; // si la plante est vide, on sort
					}
				}
			} else {
				$tab[$idx]["estVide"] = true;
				if ($tab[1]["estVide"] === true && $tab[2]["estVide"] === true  &&
				$tab[3]["estVide"] === true && $tab[4]["estVide"] === true ) {
					$planteADetruire = true;
					break; // si la plante est vide, on sort
				} else {
					$i--;
				}
			}
		}

		$nbCueilletteLaban = 0;
		$nbCueilletteCharrette = 0;
		$nbCueilletteATerre = 0;

		if ($this->view->okJet1 === true) {
			// reussite, on met dans le laban
			if ($arrivee == 2) {
				Zend_Loader::loadClass('LabanPartieplante');
				$labanPartiePlanteTable = new LabanPartieplante();	
				for ($i=1; $i<=4; $i++) {
					if ($cueillette[$i]["quantite"] > 0) {
						$dansLaban = 0;
						$aTerre = 0;
	
						if ($nbCueilletteLaban + $cueillette[$i]["quantite"] > $this->view->nbPossibleDansLabanMax) {
							$dansLaban = $this->view->nbPossibleDansLabanMax - $nbCueilletteLaban;
							if ($dansLaban > $cueillette[$i]["quantite"]) $dansLaban = $cueillette[$i]["quantite"];
							if ($dansLaban < 0) $dansLaban = 0;
							$aTerre = $cueillette[$i]["quantite"] - $dansLaban;
							if ($aTerre < 0) $aTerre = 0;
						} else { // tout passe dans le laban
							$dansLaban = $cueillette[$i]["quantite"];
						}
	
						if ($dansLaban > 0) {
							$nbCueilletteLaban = $nbCueilletteLaban + $dansLaban;
							$data = array(
								'id_fk_type_laban_partieplante' => $cueillette[$i]["id_fk"],
								'id_fk_type_plante_laban_partieplante' => $cueillette[$i]["id_type_plante"],
								'id_fk_braldun_laban_partieplante' => $this->view->user->id_braldun,
								'quantite_laban_partieplante' => $dansLaban, //$cueillette[$i]["quantite"],
							);
							$labanPartiePlanteTable->insertOrUpdate($data);
						}
					}
					if ($aTerre > 0) {
						Zend_Loader::loadClass("ElementPartieplante");
						$elementPartiePlanteTable = new ElementPartieplante();
						$nbCueilletteATerre = $nbCueilletteATerre + $aTerre;
						$data = array(
							'id_fk_type_element_partieplante' => $cueillette[$i]["id_fk"],
							'id_fk_type_plante_element_partieplante' => $cueillette[$i]["id_type_plante"],
							'x_element_partieplante' => $this->view->user->x_braldun,
							'y_element_partieplante' => $this->view->user->y_braldun,
							'z_element_partieplante' => $this->view->user->z_braldun,
							'quantite_element_partieplante' => $aTerre,
						);
						$elementPartiePlanteTable->insertOrUpdate($data);
					}
				}
			}
			// reussite, on met dans la charrette
			if ($arrivee == 1) {
				Zend_Loader::loadClass('CharrettePartieplante');
				$charrettePartiePlanteTable = new CharrettePartieplante();	
				for ($i=1; $i<=4; $i++) {
					if ($cueillette[$i]["quantite"] > 0) {
						$dansCharrette = 0;
						$aTerre = 0;
	
						if ($nbCueilletteCharrette + $cueillette[$i]["quantite"] > $this->view->nbPossibleDansCharretteMax) {
							$dansCharrette = $this->view->nbPossibleDansCharretteMax - $nbCueilletteCharrette;
							if ($dansCharrette > $cueillette[$i]["quantite"]) $dansCharrette = $cueillette[$i]["quantite"];
							if ($dansCharrette < 0) $dansCharrette = 0;
							$aTerre = $cueillette[$i]["quantite"] - $dansCharrette;
							if ($aTerre < 0) $aTerre = 0;
						} else { // tout passe dans le charrette
							$dansCharrette = $cueillette[$i]["quantite"];
						}
	
						if ($dansCharrette > 0) {
							$nbCueilletteCharrette = $nbCueilletteCharrette + $dansCharrette;
							$data = array(
								'id_fk_type_charrette_partieplante' => $cueillette[$i]["id_fk"],
								'id_fk_type_plante_charrette_partieplante' => $cueillette[$i]["id_type_plante"],
								'id_fk_charrette_partieplante' => $this->view->idCharrette,
								'quantite_charrette_partieplante' => $dansCharrette, //$cueillette[$i]["quantite"],
							);
							$charrettePartiePlanteTable->insertOrUpdate($data);
						}
					}
					if ($aTerre > 0) {
						Zend_Loader::loadClass("ElementPartieplante");
						$elementPartiePlanteTable = new ElementPartieplante();
						$nbCueilletteATerre = $nbCueilletteATerre + $aTerre;
						$data = array(
							'id_fk_type_element_partieplante' => $cueillette[$i]["id_fk"],
							'id_fk_type_plante_element_partieplante' => $cueillette[$i]["id_type_plante"],
							'x_element_partieplante' => $this->view->user->x_braldun,
							'y_element_partieplante' => $this->view->user->y_braldun,
							'z_element_partieplante' => $this->view->user->z_braldun,
							'quantite_element_partieplante' => $aTerre,
						);
						$elementPartiePlanteTable->insertOrUpdate($data);
					}
				}
			}
			if ($arrivee == 3) {
				for ($i=1; $i<=4; $i++) {
					if ($cueillette[$i]["quantite"] > 0) {
						Zend_Loader::loadClass("ElementPartieplante");
						$elementPartiePlanteTable = new ElementPartieplante();
						$aTerre = $cueillette[$i]["quantite"];
						$nbCueilletteATerre = $nbCueilletteATerre + $aTerre;
						$data = array(
							'id_fk_type_element_partieplante' => $cueillette[$i]["id_fk"],
							'id_fk_type_plante_element_partieplante' => $cueillette[$i]["id_type_plante"],
							'x_element_partieplante' => $this->view->user->x_braldun,
							'y_element_partieplante' => $this->view->user->y_braldun,
							'z_element_partieplante' => $this->view->user->z_braldun,
							'quantite_element_partieplante' => $aTerre,
						);
						$elementPartiePlanteTable->insertOrUpdate($data);
					}
				}
			}
			$statsRecolteurs = new StatsRecolteurs();
			$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
			$dataRecolteurs["niveau_braldun_stats_recolteurs"] = $this->view->user->niveau_braldun;
			$dataRecolteurs["id_fk_braldun_stats_recolteurs"] = $this->view->user->id_braldun;
			$dataRecolteurs["mois_stats_recolteurs"] = date("Y-m-d", $moisEnCours);
			$dataRecolteurs["nb_partieplante_stats_recolteurs"] = $nbCueilletteLaban + $nbCueilletteCharrette + $nbCueilletteATerre;
			$statsRecolteurs->insertOrUpdate($dataRecolteurs);

			$this->view->estQueteEvenement = Bral_Util_Quete::etapeCollecter($this->view->user, $this->competence["id_fk_metier_competence"]);
		}

		// s'il n'y a plus rien sur la plante, il faut la supprimer
		if ($planteADetruire === true) {
			$planteTable = new Plante();
			$where = "id_plante=".$idPlante;
			$planteTable->delete($where);
		} else { // sinon, il faut la mettre à jour
			$data = array(
				"partie_1_plante" => $p["partie_1_plante"] - $cueillette[1]["quantite"],
				"partie_2_plante" => $p["partie_2_plante"] - $cueillette[2]["quantite"],
				"partie_3_plante" => $p["partie_3_plante"] - $cueillette[3]["quantite"],
				"partie_4_plante" => $p["partie_4_plante"] - $cueillette[4]["quantite"],
			);
			$planteTable = new Plante();
			$where = "id_plante=".$idPlante;
			$planteTable->update($data, $where);
		}

		$this->view->cueillette = $cueillette;
		$this->view->nbCueillette = $nbCueilletteLaban + $nbCueilletteCharrette + $nbCueilletteATerre;
		$this->view->nbCueilletteCharrette = $nbCueilletteCharrette;
		$this->view->nbCueilletteLaban = $nbCueilletteLaban;
		$this->view->nbCueilletteTerre = $nbCueilletteATerre;
		$this->view->planteDetruite = $planteADetruire;
		$this->view->plante = $plante;
		$this->view->arrivee = $arrivee;
			
		$this->setEvenementQueSurOkJet1(false);

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_laban", "box_vue", "box_charrette"));
	}

	/*
	 * La quantité extraite est fonction de la quantité disponible à cet endroit.
	 * (Directement dans le sac à dos)
	 *  Quantità maximum ramassée est fonction du niveau d'agilite du Braldun :
	 *  AGI : QUANTITE
	 *  0-4 : 1D3 + BM /2
	 *  5-9 : 1D3+1 + BM /2
	 *  10-14 : 1D3+2 + BM /2
	 *  15-19 : 1D3+3 + BM /2
	 *  20-24 : 1D3+4 + BM /2
	 */
	private function calculQuantiteAExtraire() {
		Zend_Loader::loadClass('Bral_Util_Commun');
		$this->view->effetRune = false;

		$n = Bral_Util_De::get_1d3();
		$n = $n + floor($this->view->user->agilite_base_braldun / 5);

		if (Bral_Util_Commun::isRunePortee($this->view->user->id_braldun, "RI")) { // s'il possède une rune RI
			$this->view->effetRune = true;
			$n = ceil($n * 1.5);
		}

		$n  = $n  + ($this->view->user->agilite_bm_braldun + $this->view->user->agilite_bbdf_braldun) / 2 ;
		$n  = intval($n);
		if ($n <= 0) {
			$n  = 1;
		}

		return $n;
	}

}
