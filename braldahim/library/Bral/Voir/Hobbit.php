<?php

class Bral_Voir_Hobbit {

	function __construct($request, $view) {
		Zend_Loader::loadClass("Communaute");
		Zend_Loader::loadClass("HobbitCommunaute");

		$this->_request = $request;
		$this->view = $view;
	}

	function getNomInterne() {
		return "box_voir_hobbit_inner";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$this->view->connu = false;
		$this->view->hobbit = null;
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->findById($this->getValeurVerif($this->_request->get("hobbit")));
		if (count($hobbitRowset) == 1) {
			$hobbitRowset = $hobbitRowset->toArray();
			$this->view->hobbit = $hobbitRowset;
			$this->view->connu = true;
			
			$hobbitCommunauteTable = new HobbitCommunaute();
			$hobbitCommunaute = $hobbitCommunauteTable->findByIdHobbit($this->view->hobbit["id_hobbit"]);
			// TODO RANG
		} else {
			$hobbit = null;
		}
		
		if ($this->_request->get("menu") == "evenements" && $this->view->connu != null) {
			return $this->renderEvenements();
		} else { 
			return $this->view->render("voir/hobbit.phtml");
		}
	}
	
	function renderEvenements() {
		
		return $this->view->render("voir/hobbit/evenements.phtml");
	}
	
	private function preparePage() {
	}
	
	private function getValeurVerif($val) {
		if (((int)$val.""!=$val."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$val);
		} else {
			return (int)$val;
		}
	}
}
