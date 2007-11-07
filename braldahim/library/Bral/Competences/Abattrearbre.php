<?php

class Bral_Competences_Abattrearbre extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Zone");
		$zoneTable = new Zone();

		$zones = $zoneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$zone = $zones[0];

		switch($zone["nom_systeme_environnement"]) {
			case "foret" :
				$this->view->abattreArbreOk = true;
				break;
			case "marais":
			case "montagne":
			case "caverne":
			case "plaine" :
			case "foret" :
				$this->view->abattreArbreOk = true;
				break;
			default :
				throw new Exception("Abattre un arbre Environnement invalide:".$zone["nom_systeme_environnement"]. " x=".$x." y=".$y);
		}
		
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
		
		// Verification assaisonner
		if ($this->view->abattreArbreOk == false) {
			throw new Zend_Exception(get_class($this)." Abattre un arbre interdit ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculAbattreArbre();
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*
	 *  
	 */
	private function calculAbattreArbre() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("Bral_Util_De");
		
		$n = Bral_Util_De::get_1d3();
		$this->view->nbRondins = $n + floor($this->view->user->sagesse_base_hobbit / 5);
		
		$labanTable = new Laban();
		$data = array(
			'id_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_rondin_laban' => $this->view->nbRondins,
		);
		$labanTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_evenements");
	}
}
