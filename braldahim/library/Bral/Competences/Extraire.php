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
		
		$this->view->poidsPlaceDisponible = false;
		
		$this->preCalculPoids();
		if ($this->view->poidsPlaceDisponible == false) {
			return;
		}
		
		$this->view->filonOk = false;

		$filonTable = new Filon();
		$this->view->filon = $filonTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		if (count($this->view->filon) > 0) {
			$this->view->filonOk = true;
		}
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		Zend_Loader::loadClass('LabanMinerai');
		Zend_Loader::loadClass('Hobbit');
		Zend_Loader::loadClass('StatsRecolteurs');

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		} elseif ($this->view->poidsPlaceDisponible == false) {
			throw new Zend_Exception(get_class($this)." Poids invalide");
		}
		
		// calcul des jets
		if ($this->view->filonOk == true) {
			$this->calculJets();
		} else { // ($this->view->filonOk == false) {
			$this->calculPx();
			$this->calculBalanceFaim();
			$this->majHobbit();
			return;
		}

		$valid = false;
		foreach($this->view->filon as $f) {
			$idFilon = $f["id_filon"];
			$id_fk_type_minerai_filon = $f["id_fk_type_minerai_filon"];
			$quantite_restante_filon = $f["quantite_restante_filon"];
			$nom_type_minerai = $f["nom_type_minerai"];
			$valid = true;
			break;
		}
		
		if ($valid===false) {
			throw new Zend_Exception(get_class($this)." Erreur inconnue. Valid=".$valid);
		}
		
		$quantiteExtraite = $this->calculQuantiteAExtraire();
		
		if ($this->view->okJet1 === true) {
			$labanMineraiTable = new LabanMinerai();
			$data = array(
				'id_fk_type_laban_minerai' => $id_fk_type_minerai_filon,
				'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
				'quantite_brut_laban_minerai' => $quantiteExtraite,
			);
	
			$labanMineraiTable->insertOrUpdate($data);
			
			$statsRecolteurs = new StatsRecolteurs();
			$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
			$dataRecolteurs["niveau_hobbit_stats_recolteurs"] = $this->view->user->niveau_hobbit;
			$dataRecolteurs["id_fk_hobbit_stats_recolteurs"] = $this->view->user->id_hobbit;
			$dataRecolteurs["mois_stats_recolteurs"] = date("Y-m-d", $moisEnCours);
			$dataRecolteurs["nb_minerai_stats_recolteurs"] = $quantiteExtraite;
			$statsRecolteurs->insertOrUpdate($dataRecolteurs);
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

		$this->view->minerai = $minerai;
		$this->view->filonDetruit = $filonDetruit;
		
		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_laban"));
	}

	/* La quantité de minerai extraite est fonction de la quantité de minerai
	 * disponible à cet endroit du filon (ce qu'il reste à exploiter) et
	 * le niveau de FOR du Hobbit :
	 * de 0 à 4 : 1D3 + BM FOR
	 * de 5 à 9 : 1D3+1 + BM FOR
	 * de 10 à 14 :1D3+2 + BM FOR
	 * de 15 à 19 : 1D3+3 + BM FOR etc.
	 */
	private function calculQuantiteAExtraire() {
		$this->view->effetRune = false;
		
		$n = Bral_Util_De::get_1d3();
		$n = $n + floor($this->view->user->force_base_hobbit / 5);
		
		if (Bral_Util_Commun::isRunePortee($this->view->user->id_hobbit, "MI")) { // s'il possède une rune MI
			$this->view->effetRune = true;
			$n = ceil($n * 1.5);
		}
		
		$n = $n + ($this->view->user->force_bm_hobbit + $this->view->user->force_bbdf_hobbit) / 2 ;
		$n = intval($n);
		if ($n <= 0) {
			$n = 1;
		}
		
		if ($n > $this->view->nbElementPossible) {
			$n = $this->view->nbElementPossible;
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
	
	private function preCalculPoids() {
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		if ($poidsRestant < 0) $poidsRestant = 0;
		
		$this->view->nbElementPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_MINERAI);
		
		if ($this->view->nbElementPossible < 1) {
			$this->view->poidsPlaceDisponible = false;
		} else {
			$this->view->poidsPlaceDisponible = true;
		}
	}
}
