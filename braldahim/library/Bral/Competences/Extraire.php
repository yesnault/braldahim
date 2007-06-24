<?php

class Bral_Competences_Extraire extends Bral_Competences_Competence {

	private $_tabPlantes = null;
	function prepareCommun() {
		Zend_Loader::loadClass('Filon');
		$tabPlantes = null;
		$this->view->filonOk = false;

		$filonTable = new Filon();
		$this->view->filon = $filonTable->findCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
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

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true && $this->view->filonOk  === true) {
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

			$labanMineraiTable = new LabanMinerai();
			$data = array(
			'id_fk_type_laban_minerai' => $id_fk_type_minerai_filon,
			'id_hobbit_laban_minerai' => $this->view->user->id_hobbit,
			'quantite_laban_minerai' => $quantiteExtraite,
			);

			$labanMineraiTable->insertOrUpdate($data);

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

			$minerai = array("nom_type" => $nom_type_minerai, "quantite_extraite" => $quantiteExtraite);

			$this->view->minerai = $minerai;
			$this->view->filonDetruit = $filonDetruit;
			
			$this->majEvenementsStandard();
		}
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_evenements");
	}

	/* La quantité de minerai extraite est fonction de la quantité de minerai
	 * disponible à cet endroit du filon (ce qu'il reste à exploiter) et
	 * le niveau de FOR du Hobbit :
	 * de 0 à 4 : 1D3
	 * de 5 à 9 : 1D3+1
	 * de 10 à 14 :1D3+2
	 * de 15 à 19 : 1D3+3 etc.â€¦
	 */
	private function calculQuantiteAExtraire() {
		Zend_Loader::loadClass("Bral_Util_De");
		$n = Bral_Util_De::get_1d3();
		$n = $n + floor($this->view->user->force_base_hobbit / 5);
		return $n;
	}
}
