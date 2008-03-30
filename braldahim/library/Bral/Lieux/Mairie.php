<?php

class Bral_Lieux_Mairie extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabDestinations = null;

	function prepareCommun() {
		Zend_Loader::loadClass("HobbitCommunaute"); 
		Zend_Loader::loadClass("Communaute"); 
		Zend_Loader::loadClass("RangCommunaute"); 
		
		$this->_coutCastars = $this->calculCoutCastars();
		$this->_utilisationPossible = (($this->view->user->castars_hobbit -  $this->_coutCastars) > 0);
		
		$hobbitCommunauteTable = new HobbitCommunaute();
		$hobbitCommunaute = $hobbitCommunauteTable->findByIdHobbit($this->view->user->id_hobbit);
		$this->view->hobbitAvecCommunaute = false;
	
		if (count($hobbitCommunaute) > 0) {
			$this->view->hobbitAvecCommunaute = true;
		}
		
		$communauteTable = new Communaute();
		$communautes = $communauteTable->fetchAll();
		$communautes = $communautes->toArray();
		
		$tabCommunaute = null;
		foreach($communautes as $c) {
			$tabCommunaute[$c["id_communaute"]] = array(
										'id_communaute' => $c["id_communaute"], 
										'nom_communaute' => $c["nom_communaute"]
										);
		}
		$this->view->communautes = $tabCommunaute;
	}

	function prepareFormulaire() {
		$this->view->utilisationPossible = $this->_utilisationPossible;
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {
		$this->view->creerCommunaute = false;
		$this->view->entrerCommunaute = false;
		$this->view->sortirCommunaute = false;
		$this->view->supprimerCommunaute = false;
		$this->view->communaute = null;
		
		$communaute = null;
		
		if ($this->_utilisationPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : castars:".$this->view->user->castars_hobbit." cout:".$this->_coutCastars);
		}
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : pa:".$this->view->user->pa_hobbit." cout:".$this->$this->view->paUtilisationLieu);	
		}
		
		$idCommunaute = null;
		if (((int)$this->request->get("valeur_1").""!=$this->request->getPost("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Val 1 invalide : ".$this->request->getPost("valeur_1"));
		} else {
			$idCommunaute = (int)$this->request->getPost("valeur_1");
			if ($idCommunaute != -1) {
				$this->view->entrerCommunaute = true;
			}
		}
		
		$nomCommunaute = null;
		if (((int)$this->request->getPost("valeur_2").""!=$this->request->getPost("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Val 2 invalide : ".$this->request->getPost("valeur_2"));
		} else {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
			$nomCommunaute = $filter->filter($this->request->getPost('valeur_3'));
			$nomCommunaute = $this->request->getPost("valeur_3");
			$this->view->creerCommunaute = true;
		}
		
		if (((int)$this->request->getPost("valeur_4").""!=$this->request->getPost("valeur_4")."")) {
			throw new Zend_Exception(get_class($this)." Val 4 invalide : ".$this->request->getPost("valeur_4"));
		} else {
			$this->view->sortirCommunaute = true;
		}
		
		if ($this->view->entrerCommunaute === true) {
			foreach ($this->view->communautes as $c) {
				if ($c["id_communaute"] == $idCommunaute) {
					$communaute = $c;
					break;
				}
			}
			
			if ($communaute == null) {
				throw new Zend_Exception(get_class($this)." Communaute invalide (".$idCommunaute.")");
			}
			
			$this->entrerCommunaute($idCommunaute);
			
		} else if ($this->view->creerCommunaute === true) {
			$communaute = $this->creerCommunaute($nomCommunaute);
		} else if ($this->view->sortirCommunaute === true) {
			$communaute = $this->sortirCommunaute($idCommunaute);
		} else {
			throw new Zend_Exception(get_class($this)." Action invalide");
		}
		
		$this->view->communaute = $communaute;
		
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->_coutCastars;
		$this->majHobbit();
	}


	function getListBoxRefresh() {
		return array("box_metier", "box_laban", "box_competences_metiers", "box_vue", "box_lieu");
	}

	private function calculCoutCastars() {
		return 50;
	}
	
	
	private function creerCommunaute($nomCommunaute) {
		$communauteTable = new Communaute();
		$data = array('nom_communaute' => $nomCommunaute,
			'date_creation_communaute' => date("Y-m-d H:i:s"),
			'id_fk_hobbit_createur_communaute' => $this->view->user->id_hobbit,
			'nb_membre_communaute' => 1,
			'description_communaute' => '',
		);
		$communaute = $data;
		$communaute["id_communaute"] = $communauteTable->insert($data);
		
		$rangCommunauteTable = new RangCommunaute();
		$data = array('id_fk_type_rang_communaute' => 1,
			'id_fk_communaute_rang_communaute' => $communaute["id_communaute"],
			'nom_rang_communaute' => 'Rang Createur',
			'description_rang_communaute' => 'Description Rang Createur',
		);
		$rangCommunauteTable->insert($data);
		
		$hobbitCommunauteTable = new HobbitCommunaute();
		$data = array('id_fk_communaute_communaute' => $communaute["id_communaute"],
			'id_fk_hobbit_communaute' => $this->view->user->id_hobbit,
			'date_entree_hobbit_communaute' => date("Y-m-d H:i:s"),
			'id_fk_rang_communaute_hobbit_communaute' => 1,
			'commentaire_hobbit_communaute' => 'Createur',
		);
		$hobbitCommunauteTable->insert($data);
		
		return $communaute;
	}
	
	private function sortirCommunaute($idCommunaute) {
		$communaute = $this->view->communautes[$idCommunaute];
		
		return $communaute;
	}
	
	private function entrerCommunaute($idCommunaute) {
		$communaute = $this->view->communautes[$idCommunaute];
		
		return $communaute;
	}
	
	private function supprimerCommunaute($idCommunate) {
		$this->view->supprimerCommunaute = true;
	}
}