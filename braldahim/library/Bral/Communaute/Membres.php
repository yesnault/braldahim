<?php

class Bral_Communaute_Membres {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass("HobbitCommunaute");
		Zend_Loader::loadClass("RangCommunaute");

		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}

	function getNomInterne() {
		return "box_communaute_action";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$communaute = null;
		$this->view->tri = "";
		$this->view->filtre = "";
		$this->view->page = "";
		$this->precedentOk = false;
		$this->suivantOk = false;
		
		$hobbitCommunauteTable = new HobbitCommunaute();
		$communauteRowset = $hobbitCommunauteTable->findByIdHobbit($this->view->user->id_hobbit);
		if (count($communauteRowset) > 0) {
			foreach ($communauteRowset as $c) {
				$communaute = $c;
				break;
			}
		}
		
		if ($communaute == null) {
			throw new Zend_Exception(get_class($this)." Communaute Invalide");
		}
		
		$hobbitCommunauteRowset = $hobbitCommunauteTable->findByIdCommunaute($communaute["id_communaute"]);
		$tabMembres = null;

		foreach($hobbitCommunauteRowset as $m) {
			$tabMembres[] = array(
				"id_hobbit" => $m["id_hobbit"],
				"nom_hobbit" => $m["nom_hobbit"],
				"prenom_hobbit" => $m["prenom_hobbit"],
				"date_entree" => $m["date_entree_hobbit_communaute"],
			);
		}
		
		$rangCommunauteTable = new RangCommunaute();
		$rangsCommunauteRowset = $rangCommunauteTable->findByIdCommunaute($communaute["id_communaute"]);
		$tabRangs = null;

		foreach($rangsCommunauteRowset as $r) {
			$tabRangs[] = array(
				"id_type_rang" => $r["id_fk_type_rang_communaute"],
				"nom" => $r["nom_rang_communaute"],
			);
		}
		
		$this->view->tabRangs = $tabRangs;
		$this->view->tabMembres = $tabMembres;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute/membres.phtml");
	}
}
