<?php

class Bral_Competences_Decouper extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		
		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->view->decouperEchoppeOk = false;
		$this->view->decouperPlancheOk = false;
		
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->decouperEchoppeOk = false;
			return;
		}
		$idEchoppe = -1;
			foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
				$e["nom_systeme_metier"] == "menuisier" &&
				$e["x_echoppe"] == $this->view->user->x_hobbit &&
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->decouperEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];

				$echoppeCourante = array(
				'id_echoppe' => $e["id_echoppe"],
				'x_echoppe' => $e["x_echoppe"],
				'y_echoppe' => $e["y_echoppe"],
				'id_metier' => $e["id_metier"],
				'quantite_rondin_arriere_echoppe' => $e["quantite_rondin_arriere_echoppe"],
				);
				$this->view->decouperPlancheOk = true;
				break;
			}
		}
		
		if ($this->view->decouperEchoppeOk == false) {
			return;
		}
		
		$this->idEchoppe = $idEchoppe;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		Zend_Loader::loadClass("Bral_Util_De");
		Zend_Loader::loadClass('Hobbit');

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification chasse
		if ($this->view->decouperEchoppeOk == false || $this->view->decouperPlancheOk == false) {
			throw new Zend_Exception(get_class($this)." decouper interdit ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculDecouper();
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*Découpe un rondin présent dans l'échoppe en planches.
	 * 4D3 de planches par rondin.
	 */
	private function calculDecouper() {
		$quantitePlanches = Bral_Util_De::get_4d3();
		
		$echoppeTable = new Echoppe();
		$data = array(
				'id_echoppe' => $this->idEchoppe,
				'quantite_rondin_arriere_echoppe' => -1,
				'quantite_planche_arriere_echoppe' => $quantitePlanches,
		);
		$echoppeTable->insertOrUpdate($data);
		
		$this->view->quantitePlanches = $quantitePlanches;
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_echoppes", "box_evenements");
	}
}
