<?php

class Bral_Competences_Fondre extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		
		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->view->fondreEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->fondreEchoppeOk = false;
			return;
		}
		$idEchoppe = -1;
		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit && 
				$e["nom_systeme_metier"] == "forgeron" && 
				$e["x_echoppe"] == $this->view->user->x_hobbit && 
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->fondreEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];
				break;
			}
		}
		
		if ($this->view->fondreEchoppeOk == false) {
			return;
		}
		
		Zend_Loader::loadClass("EchoppeMinerai");
		$tabMinerais = null;
		$echoppeMineraiTable = new EchoppeMinerai();
		$minerais = $echoppeMineraiTable->findByIdEchoppe($idEchoppe);
		
		$this->view->nb_arriereMinerai = 0;
		$this->view->fondreMineraiOk = true;
		
		if ($minerais != null) {
			foreach ($minerais as $m) {
				$tabMinerais[] = array(
				"id_type" => $m["id_fk_type_echoppe_minerai"],
				"nom_type" => $m["nom_type_minerai"],
				"quantite_arriere" => $m["quantite_arriere_echoppe_minerai"],
				"quantite_lingots" => $m["quantite_lingots_echoppe_minerai"],
				);
				if ($m["quantite_arriere_echoppe_minerai"] > 1) {
					$this->view->fondreMineraiOk = true;
				}
			}
		}
		$this->view->minerais = $tabMinerais;
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
		if ($this->view->fondreEchoppeOk == false || $this->view->fondreMineraiOk == false) {
			throw new Zend_Exception(get_class($this)." Fondre interdit ");
		}
		
		$idTypeMinerai = $this->request->get("valeur_1");
		if ($idTypeMinerai == null ) {
			throw new Zend_Exception(get_class($this)." Minerai inconnu ");
		}
		$mineraiOk = false;;
		foreach($this->view->minerais as $t) {
			if ($t["id_type"] == $idTypeMinerai) {
				$mineraiOk = true;
				$this->view->mineraiNomType = $t["nom_type"];
				break;
			}
		}
		if ($mineraiOk == false) {
			throw new Zend_Exception(get_class($this)." Minerai invalide");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculFondre($idTypeMinerai);
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculFondre($idTypeMinerai) {
		//2 unités de minerai donne 1D2 lingot(s) du minerai
		$this->view->nbLingots = Bral_Util_De::get_1d2();
		
		$echoppeMineraiTable = new EchoppeMinerai();
		$data = array(
			'id_fk_type_echoppe_minerai' => $idTypeMinerai,
			'id_fk_echoppe_echoppe_minerai' => $this->idEchoppe,
			'quantite_caisse_echoppe_minerai' => 0,
			'quantite_lingots_echoppe_minerai' => $this->view->nbLingots,
			'quantite_arriere_echoppe_minerai' => -2,
		);
		$echoppeMineraiTable->insertOrUpdate($data);
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
