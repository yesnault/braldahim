<?php

class Bral_Competences_Abattrearbre extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Zone");
		$zoneTable = new Zone();

		$zones = $zoneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$zone = $zones[0];

		switch($zone["nom_systeme_environnement"]) {
			case "foret" :
				$this->view->abattreArbreEnvironnementOk = true;
				break;
			case "marais":
			case "montagne":
			case "caverne":
			case "plaine" :
				$this->view->abattreArbreEnvironnementOk = false;
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
		
		// Verification abattre arbre
		if ($this->view->abattreArbreEnvironnementOk == false) {
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
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Bral_Util_De");
		
		$n = Bral_Util_De::get_1d3();
		$this->view->nbRondins = $n + floor($this->view->user->sagesse_base_hobbit / 5);
		
		$charretteTable = new Charrette();
		$data = array(
			'quantite_rondin_charrette' => $this->view->nbRondins,
			'id_hobbit_charrette' => $this->view->user->id_hobbit,
		);
		$charretteTable->updateCharrette($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_charrette", "box_evenements");
	}
}
