<?php

class Bral_Box_Echoppes {
	
	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "Echoppes";
	}
	
	function getNomInterne() {
		return "box_echoppes";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("HobbitsMetiers");
		Zend_Loader::loadClass("Region");
		
		$regionTable = new Region();
		$regions = $regionTable->fetchAll(null, 'nom_region');
		$regions = $regions->toArray();
		
		$echoppesTable = new Echoppe();
		$echoppesRowset = $echoppesTable->findByIdHobbit($this->view->user->id_hobbit);
		$this->view->estLieuCourant = false;
		
		$tabEchoppes = null;
		foreach($echoppesRowset as $e) {
			$tabEchoppes[] = array(
			"id_echoppe" => $e["id_echoppe"],
			"x_echoppe" => $e["x_echoppe"],
			"y_echoppe" => $e["y_echoppe"],
			"nom_metier" => $e["nom_metier"],
			"id_metier" =>  $e["id_metier"],
			"id_region" => $e["id_region"],
			"nom_region" => $e["nom_region"]
			);
		}
		
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersEchoppeByHobbitId($this->view->user->id_hobbit);
		$tabMetiers = null;
		$tabMetierCourant = null;

		foreach($hobbitsMetierRowset as $m) {
			if ($this->view->user->sexe_hobbit == 'feminin') {
				$nom_metier = $m["nom_feminin_metier"];
			} else {
				$nom_metier = $m["nom_masculin_metier"];
			}
			
			$regionsMetier = null;
			$tabEchoppesMetier = null;
			foreach ($regions as $r) {
				$regionMetier = null;
				$regionMetier["nom_region"] = $r["nom_region"];
				$regionMetier["echoppe"] = null;
				if (count($tabEchoppes) > 0) {
					foreach($tabEchoppes as $e) {
						if ($e["id_metier"] == $m["id_metier"] && 
							$r["id_region"] == $e["id_region"]) {
							$regionMetier["echoppe"] = $e;
						}
					}
				}
				$regionsMetier[] = $regionMetier;
			}
			
			$t = array("id_metier" => $m["id_metier"],
			"nom_metier" => $nom_metier,
			"nom_systeme" => $m["nom_systeme_metier"],
			"est_actif" => $m["est_actif_hmetier"],
			"regions" => $regionsMetier,
			);
			
			$tabMetiers[] = $t;
			if ($m["est_actif_hmetier"] == "oui") {
				$tabMetierCourant = $t;
			}
		}
		
		
		$this->view->tabMetierCourant = $tabMetierCourant;
		$this->view->tabMetiers = $tabMetiers;
		
		$this->view->echoppes = $tabEchoppes;
		$this->view->nEchoppes = count($tabEchoppes);
		
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/echoppes.phtml");
	}
	
}
