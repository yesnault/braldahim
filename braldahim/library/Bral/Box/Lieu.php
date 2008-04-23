<?php

class Bral_Box_Lieu extends Bral_Box_Box {
	
//	function __construct($request, $view, $interne) {
//		$this->_request = $request;
//		$this->view = $view;
//		$this->view->affichageInterne = $interne;
//	}
	
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
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("Lieu");
		
		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
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
			
			$this->view->htmlLieu = $this->view->render("interface/lieux/".$lieu["nom_systeme_type_lieu"].".phtml");
		} else {
			$echoppesTable = new Echoppe();
			$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
			if (count($echoppeRowset) > 1) {
				throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
			} elseif (count($echoppeRowset) == 1) {
				$echoppe = $echoppeRowset[0];
				$this->view->estLieuCourant = true;
				
				$nom = "Échoppe";
				if ($echoppe["nom_masculin_metier"] == "A") {
					$nom .= " d'";
				} else {
					$nom .= " de ";
				}
				if ($echoppe["sexe_hobbit"] == "masculin") {
					$nom .= $echoppe["nom_masculin_metier"];
				} else {
					$nom .= $echoppe["nom_feminin_metier"];
				}
				$nom .= " appartenant à ".$echoppe["prenom_hobbit"];
				$nom .= " ".$echoppe["nom_hobbit"];
				$nom .= " n°".$echoppe["id_hobbit"];
				
				$this->view->nomLieu = $nom;
				$this->view->nomTypeLieu = "Échoppe";
				$this->view->nomSystemeLieu = "echoppe";
				$this->view->descriptionLieu = $echoppe["commentaire_echoppe"];
				$this->view->estFranchissableLieu = true;
				$this->view->estAlterableLieu = false;
				$this->view->paUtilisationLieu = 0;
				$this->view->niveauMinLieu = 0;
				
				$this->view->htmlLieu = $this->view->render("interface/lieux/echoppe.phtml");
			}
		}
		
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/lieu.phtml");
	}
	
}
