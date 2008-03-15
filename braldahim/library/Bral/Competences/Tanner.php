<?php

class Bral_Competences_Tanner extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		
		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->view->tannerEchoppeOk = false;
		$this->view->tannerPeauOk = false;
		
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->tannerEchoppeOk = false;
			return;
		}
		$idEchoppe = -1;
			foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
				$e["nom_systeme_metier"] == "tanneur" &&
				$e["x_echoppe"] == $this->view->user->x_hobbit &&
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->tannerEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];

				$echoppeCourante = array(
				'id_echoppe' => $e["id_echoppe"],
				'x_echoppe' => $e["x_echoppe"],
				'y_echoppe' => $e["y_echoppe"],
				'id_metier' => $e["id_metier"],
				'quantite_peau_arriere_echoppe' => $e["quantite_peau_arriere_echoppe"],
				);
				if ($e["quantite_peau_arriere_echoppe"] >=2) {
					$this->view->tannerPeauOk = true;
				}
				break;
			}
		}
		
		if ($this->view->tannerEchoppeOk == false) {
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
		if ($this->view->tannerEchoppeOk == false || $this->view->tannerPeauOk == false) {
			throw new Zend_Exception(get_class($this)." tanner interdit ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculTanner();
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculTanner() {
		//Transforme 2 unités de peau en 1D2 unités de cuir ou de fourrure (suivant la peau).
		$quantiteCuir = 0;
		$quantiteFourrure = 0;
		
		$n = Bral_Util_De::get_1d100();
		if ($n <= 50) {
			$quantiteCuir = Bral_Util_De::get_1d2();
		} else {
			$quantiteFourrure = Bral_Util_De::get_1d2();
		}
		
		$echoppeTable = new Echoppe();
		$data = array(
				'id_echoppe' => $this->idEchoppe,
				'quantite_peau_arriere_echoppe' => -2,
				'quantite_cuir_arriere_echoppe' => $quantiteCuir,
				'quantite_fourrure_arriere_echoppe' => $quantiteFourrure,
		);
		$echoppeTable->insertOrUpdate($data);
		
		$this->view->quantiteCuir = $quantiteCuir;
		$this->view->quantiteFourrure = $quantiteFourrure;
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_echoppes", "box_evenements");
	}
}
