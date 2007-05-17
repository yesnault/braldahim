<?php

abstract class Bral_Lieux_Lieu {
	
	function __construct($nomSystemeLieu, $request, $view, $action) {
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeLieu;

		$this->prepareCommun();
		
		switch($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}
	
	abstract function prepareCommun();
	abstract function prepareFormulaire();
	abstract function prepareResultat();
	abstract function getListBoxRefresh();
	
	function getNomInterne() {
		return "box_action";
	}
	
	function render() {
		switch($this->action) {
			case "ask":
				return $this->view->render("lieux/".$this->nom_systeme."_formulaire.phtml");
				break;
			case "do":
				return $this->view->render("lieux/".$this->nom_systeme."_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}
}