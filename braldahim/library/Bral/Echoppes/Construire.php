<?php

class Bral_Echoppes_Construire extends Bral_Echoppes_Echoppe {

	private $_achatPossible;
	private $_coutCastars;
	private $_tabNouveauMetiers;
	private $_tabMetiers;
	private $_possedeMetier;

	function prepareCommun() {
		Zend_Loader::loadClass('Lieu'); 	
		
		// on verifie que l'on est pas sur un lieu
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->view->construireLieuOk = true;
		
		if (count($lieux) > 0) {
			$this->view->construireLieuOk = false;
		}
		
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("HobbitsMetiers");
		Zend_Loader::loadClass("Region");
		
		$regionTable = new Region();
		$regions = $regionTable->fetchAll(null, 'nom_region');
		$regions = $regions->toArray();
		
		$regionCourante = null;
		foreach ($regions as $r) {
			if ($r["x_min_region"]<=$this->view->user->x_hobbit && 
			$r["x_max_region"]>=$this->view->user->x_hobbit && 
			$r["y_min_region"]<=$this->view->user->x_hobbit && 
			$r["y_max_region"]>=$this->view->user->x_hobbit) {
				$regionCourante = $r;
				break;
			}
		}
		
		$echoppesTable = new Echoppe();
		$echoppesRowset = $echoppesTable->findByIdHobbit($this->view->user->id_hobbit);
		
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
		
		$this->view->aucuneEchoppe = true;
		$this->view->construireMetierPossible = false;
		
		foreach($hobbitsMetierRowset as $m) {
			if ($m["est_actif_hmetier"] != "oui") {
				continue;
			} else {
				$this->view->construireMetierPossible = true;
				$this->id_metier_courant = $m["id_metier"];
			}
			
			if ($this->view->user->sexe_hobbit == 'feminin') {
				$this->view->nom_metier_courant = $m["nom_feminin_metier"];
			} else {
				$this->view->nom_metier_courant = $m["nom_masculin_metier"];
			}
			
			foreach ($regions as $r) {
				$this->view->aucuneEchoppe = true;
				if (count($tabEchoppes) > 0) {
					foreach($tabEchoppes as $e) {
						if ($e["id_metier"] == $m["id_metier"] && 
							$r["id_region"] == $e["id_region"]) {
							$this->view->aucuneEchoppe = false;
							break;
						}
					}
				}
			}
			
			if ($m["est_actif_hmetier"] == "oui") {
				break;
			}
		}
		
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->aucuneEchoppe !== true || 
			$this->view->construireMetierPossible !== true ||
			$this->view->construireMetierPossible !== true) {
			throw new Zend_Exception(get_class($this)." Construction interdite");
		}
		
		$echoppesTable = new Echoppe();
		$data = array(
		'id_hobbit_echoppe' => $this->view->user->x_hobbit,
		'x_echoppe' => $this->view->user->x_hobbit,
		'y_echoppe' => $this->view->user->x_hobbit,
		'id_fk_metier_echoppe' => $this->id_metier_courant,
		'date_creation_echoppe' => $this->view->user->x_hobbit,
		);
		$echoppesTable->insertOrUpdate($data);
		
	}

	function getListBoxRefresh() {
		return array("box_laban");
	}

}