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
class Bral_Competences_Extraire extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Filon');
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass("Bral_Util_Quete");

		$this->view->filonOk = false;

		$filonTable = new Filon();
		$filons = $filonTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$tabFilons = null;
		if (count($filons) > 0) {
			$this->view->filonOk = true;

			foreach($filons as $f) {
				$tabFilons[] = array(
					'id_filon' => $f["id_filon"], 
					'nom_type_minerai' => $f['nom_type_minerai'],
					'id_fk_type_minerai_filon' => $f["id_fk_type_minerai_filon"],
					'quantite_restante_filon' => $f["quantite_restante_filon"],
				);
			}
			$this->view->labanPlein = true;
			$poidsRestantLaban = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
			$nbPossibleDansLabanMaximum = floor($poidsRestantLaban / Bral_Util_Poids::POIDS_MINERAI);
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
				$nbPossibleDansCharretteMaximum = floor($tabPoidsCharrette["place_restante"] / Bral_Util_Poids::POIDS_MINERAI);
	
				if ($nbPossibleDansCharretteMaximum > 0) {
					$this->view->charettePleine = false;
				}
				$this->view->nbPossibleDansCharretteMax = $nbPossibleDansCharretteMaximum;
			} else {
				$this->view->possedeCharrette = false;
			}
		}

		$this->view->filons = $tabFilons;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		Zend_Loader::loadClass('Braldun');
		Zend_Loader::loadClass('StatsRecolteurs');

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		$idFilonRecu = intval($this->request->get("valeur_1"));
		$arrivee = intval($this->request->get("valeur_2"));
		
		//verification de la présence du filon
		$valid = false;
		foreach($this->view->filons as $f) {
			if ($idFilonRecu == $f["id_filon"]) {
				$idFilon = $f["id_filon"];
				$id_fk_type_minerai_filon = $f["id_fk_type_minerai_filon"];
				$quantite_restante_filon = $f["quantite_restante_filon"];
				$nom_type_minerai = $f["nom_type_minerai"];
				$valid = true;
				break;
			}
		}

		if ($valid===false) {
			throw new Zend_Exception(get_class($this)." Erreur inconnue. Valid id=".$idFilonRecu);
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
		
		// calcul des jets
		if ($this->view->filonOk == true) {
			$this->calculJets();
		} else { // ($this->view->filonOk == false) {
			$this->calculPx();
			$this->calculBalanceFaim();
			$this->majBraldun();
			return;
		}

		$quantiteExtraite = $this->calculQuantiteAExtraire();
		$nbATerre = 0;
		$nbDansLaban = 0;

		if ($this->view->okJet1 === true) {
			//Charrette
			if ($arrivee == 1) {
				Zend_Loader::loadClass('CharretteMinerai');
				$nbDansCharrette = $quantiteExtraite;
				if ($nbDansCharrette > $this->view->nbPossibleDansCharretteMax) {
					$nbDansCharrette = $this->view->nbPossibleDansCharretteMax;
					$nbATerre = $quantiteExtraite - $nbDansCharrette;
				}
	
				if ($nbDansCharrette > 0) {
					$charretteMineraiTable = new CharretteMinerai();
					$data = array(
						'id_fk_type_charrette_minerai' => $id_fk_type_minerai_filon,
						'id_fk_charrette_minerai' => $this->view->idCharrette,
						'quantite_brut_charrette_minerai' => $nbDansCharrette,
					);
					$charretteMineraiTable->insertOrUpdate($data);
					unset($charretteTable);
					Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
				}
			}
			
			//Laban
			if ($arrivee == 2) {
				Zend_Loader::loadClass('LabanMinerai');
				$nbDansLaban = $quantiteExtraite;
				if ($nbDansLaban > $this->view->nbPossibleDansLabanMax) {
					$nbDansLaban = $this->view->nbPossibleDansLabanMax;
					$nbATerre = $quantiteExtraite - $nbDansLaban;
				}
	
				if ($nbDansLaban > 0) {
					$labanMineraiTable = new LabanMinerai();
					$data = array(
						'id_fk_type_laban_minerai' => $id_fk_type_minerai_filon,
						'id_fk_braldun_laban_minerai' => $this->view->user->id_braldun,
						'quantite_brut_laban_minerai' => $nbDansLaban,
					);
					$labanMineraiTable->insertOrUpdate($data);
				}
			}
			
			//sol
			if ($arrivee == 3) {
				$nbATerre = $quantiteExtraite;
			}
			
			if ($nbATerre > 0) {
				Zend_Loader::loadClass("ElementMinerai");
				$elementMineraiTable = new ElementMinerai();
				$data = array(
					'id_fk_type_element_minerai' => $id_fk_type_minerai_filon,
					'x_element_minerai' => $this->view->user->x_braldun,
					'y_element_minerai' => $this->view->user->y_braldun,
					'z_element_minerai' => $this->view->user->z_braldun,
					'quantite_brut_element_minerai' => $nbATerre,
				);
				$elementMineraiTable->insertOrUpdate($data);
			}
			
			$statsRecolteurs = new StatsRecolteurs();
			$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
			$dataRecolteurs["niveau_braldun_stats_recolteurs"] = $this->view->user->niveau_braldun;
			$dataRecolteurs["id_fk_braldun_stats_recolteurs"] = $this->view->user->id_braldun;
			$dataRecolteurs["mois_stats_recolteurs"] = date("Y-m-d", $moisEnCours);
			$dataRecolteurs["nb_minerai_stats_recolteurs"] = $quantiteExtraite;
			$statsRecolteurs->insertOrUpdate($dataRecolteurs);

			$this->view->estQueteEvenement = Bral_Util_Quete::etapeCollecter($this->view->user, $this->competence["id_fk_metier_competence"]);
		}

		// Destruction du filon s'il ne reste plus rien
		if ($quantite_restante_filon - $quantiteExtraite <= 0) {
			$filonTable = new Filon();
			$where = "id_filon=".$idFilon;
			$filonTable->delete($where);
			$filonDetruit = true;
		} else {
			$filonTable = new Filon();
			$data = array(
				'quantite_restante_filon' => $quantite_restante_filon - $quantiteExtraite,
			);
			$where = "id_filon=".$idFilon;
			$filonTable->update($data, $where);
			$filonDetruit = false;
		}
		unset($filonTable);

		$minerai = array("nom_type" => $nom_type_minerai, "quantite_extraite" => $quantiteExtraite);
		
		$this->view->nbATerre = $nbATerre;
		$this->view->minerai = $minerai;
		$this->view->filonDetruit = $filonDetruit;
		$this->view->arrivee = $arrivee;

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_laban", "box_vue", "box_charrette"));
	}

	/* La quantité de minerai extraite est fonction de la quantité de minerai
	 * disponible à cet endroit du filon (ce qu'il reste à exploiter) et
	 * le niveau de FOR du Braldun :
	 * de 0 à 4 : 1D3 + BM FOR
	 * de 5 à 9 : 1D3+1 + BM FOR
	 * de 10 à 14 :1D3+2 + BM FOR
	 * de 15 à 19 : 1D3+3 + BM FOR etc.
	 */
	private function calculQuantiteAExtraire() {
		$this->view->effetRune = false;

		$n = Bral_Util_De::get_1d3();
		$n = $n + floor($this->view->user->force_base_braldun / 5);

		if (Bral_Util_Commun::isRunePortee($this->view->user->id_braldun, "MI")) { // s'il possède une rune MI
			$this->view->effetRune = true;
			$n = ceil($n * 1.5);
		}

		$n = $n + ($this->view->user->force_bm_braldun + $this->view->user->force_bbdf_braldun) / 2 ;
		$n = intval($n);
		if ($n <= 0) {
			$n = 1;
		}

		return $n;
	}

	public function calculPx() {
		$this->view->nb_px_commun = 0;
		$this->view->calcul_px_generique = true;
		if ($this->view->okJet1 === true) {
			if ($this->view->filonOk === true) {
				$this->view->nb_px_perso =  $this->competence["px_gain"] + 1;
			} else {
				$this->view->nb_px_perso = $this->competence["px_gain"];
			}
		} else {
			$this->view->nb_px_perso = 0;
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}
}
