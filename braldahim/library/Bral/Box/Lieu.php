<?php

class Bral_Box_Lieu {
	
	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "Lieu";
	}
	
	function getNomInterne() {
		return "box_lieu";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		
		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$this->view->estLieuCourant = false;
		
		if (count($lieuRowset) > 1) {
			throw new Zend_Exception(get_class($this)."::nombre de lieux invalide > 1 !");
		} elseif (count($lieuRowset) == 1) {
			$lieu = $lieuRowset[0];
			$this->view->estLieuCourant = true;
			$this->view->idLieu = $lieu["id_lieu"];
			$this->view->nomLieu = $lieu["nom_lieu"];
			$this->view->nomTypeLieu = $lieu["nom_type_lieu"];
			$this->view->nomSystemeLieu = $lieu["nom_systeme_type_lieu"];
			$this->view->descriptionLieu = $lieu["description_lieu"];
			$this->view->descriptionTypeLieu = $lieu["description_type_lieu"];
			$this->view->estFranchissableLieu = ($lieu["est_franchissable_type_lieu"] == "oui");
			$this->view->estAlterableLieu = ($lieu["est_alterable_type_lieu"] == "oui");
			$this->view->paUtilisationLieu = $lieu["pa_utilisation_type_lieu"];
			$this->view->niveauMinLieu = $lieu["niveau_min_type_lieu"];
			
			$this->view->htmlLieu = $this->view->render("interface/lieux/".$lieu["nom_systeme_type_lieu"].".phtml");;
		}
		
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/lieu.phtml");
	}
	
}
