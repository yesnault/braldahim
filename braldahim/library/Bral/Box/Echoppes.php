<?php

class Bral_Box_Echoppes {
	
	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "&Eacute;choppes";
	}
	
	function getNomInterne() {
		return "box_echoppes";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		Zend_Loader::loadClass("Bral_Echoppes_Echoppe");
		Zend_Loader::loadClass("Bral_Echoppes_Liste");
		$box = new Bral_Echoppes_Liste("liste", $this->_request, $this->view, "ask");
		$idEchoppeCourante = $box->prepareCommun();

		if ($idEchoppeCourante != false) {
			$box = Bral_Echoppes_Factory::getVoir($this->_request, $this->view, $idEchoppeCourante);
			$this->view->htmlContenu = $box->render();
			$this->view->nom_interne = $this->getNomInterne();
			return $this->view->render("interface/echoppes.phtml");
		} else {
			$this->view->htmlContenu = $box->render();
			$this->view->nom_interne = $this->getNomInterne();
			return $this->view->render("interface/echoppes.phtml");
		}
	}
}
