<?php

class Bral_Competences_Monterpalissade extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Charrette'); 	
		Zend_Loader::loadClass('Echoppe'); 	
		Zend_Loader::loadClass('Hobbit'); 	
		Zend_Loader::loadClass('Lieu'); 	
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('Palissade');  	
		Zend_Loader::loadClass('Ville'); 	
	
		$this->view->monterPalissadeOk = false;
		$this->view->monterPalissadeCharretteOk = false;
		/*
		 * On verifie qu'il y a au moins 2 rondins
		 */
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);
	
		if (!isset($charrette)) {
			return;
		}
		
		$this->view->nRondins = 0;
		foreach ($charrette as $c) {
			$this->view->nRondins = $c["quantite_rondin_charrette"];
			$this->view->monterPalissadeCharretteOk = true;
			break;
		}
		
		if ($this->view->nRondins < 2) {
			return;
		}
		
		$this->distance = 1;
		$this->view->x_min = $this->view->user->x_hobbit - $this->distance;
		$this->view->x_max = $this->view->user->x_hobbit + $this->distance;
		$this->view->y_min = $this->view->user->y_hobbit - $this->distance;
		$this->view->y_max = $this->view->user->y_hobbit + $this->distance;
		
		$villeTable = new Ville();
		$villes = $villeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);

		$defautChecked = false;
		
		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			 $change_level = true;
			 for ($i = -$this->distance; $i <= $this->distance; $i ++) {
			 	$x = $this->view->user->x_hobbit + $i;
			 	$y = $this->view->user->y_hobbit + $j;
			 	
			 	$display = $x;
			 	$display .= " ; ";
			 	$display .= $y;
			 	
			 	if (($j == 0 && $i == 0) == false) { // on n'affiche pas de boutons dans la case du milieu
					$valid = true;
			 	} else {
			 		$valid = false;
			 	}
			 	
			 	if ($x < $this->view->config->game->x_min || $x > $this->view->config->game->x_max
			 		|| $y < $this->view->config->game->y_min || $y > $this->view->config->game->y_max ) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
			 	}
			 	
			 	foreach($echoppes as $e) {
					if ($x == $e["x_echoppe"] && $y == $e["y_echoppe"]) {
						$valid = false;
						break;
					}
				}
				
			 	foreach($lieux as $l) {
					if ($x == $l["x_lieu"] && $y == $l["y_lieu"]) {
						$valid = false;
						break;
					}
				}
			 	
			 	foreach($hobbits as $h) {
					if ($x == $h["x_hobbit"] && $y == $h["y_hobbit"]) {
						$valid = false;
						break;
					}
				}
				
				foreach($monstres as $m) {
					if ($x == $m["x_monstre"] && $y == $m["y_monstre"]) {
						$valid = false;
						break;
					}
				}
				
			 	foreach($palissades as $p) {
					if ($x == $p["x_palissade"] && $y == $p["y_palissade"]) {
						$valid = false;
						break;
					}
				}

				foreach($villes as $v) {
					if ($x >= $v["x_min_ville"] &&
						$x <= $v["x_max_ville"] &&
						$y >= $v["y_min_ville"] &&
						$y <= $v["y_max_ville"]) {
						$valid = false;
						break;
					}
				}
				
			 	if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
			 		$this->view->monterPalissadeOk = true;
			 	} else {
			 		$default = "";
			 	}

			 	$tab[] = array ("x_offset" => $i,
			 	"y_offset" => $j,
			 	"default" => $default,
			 	"display" => $display,
			 	"change_level" => $change_level, // nouvelle ligne dans le tableau
				"valid" => $valid);	
				
			 	$tabValidation[$i][$j] = $valid;
			 	
				if ($change_level) {
					$change_level = false;
				}
			 }
		}
		$this->view->tableau = $tab;
		$this->tableauValidation = $tabValidation;
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
		
		if ($this->view->monterPalissadeOk == false) {
			throw new Zend_Exception(get_class($this)." Monter Palissade interdit");
		}

		if ($this->view->nRondins < 2 ) {
			throw new Zend_Exception(get_class($this)." Monter Palissade interdit : rondins insuffisants");
		}
		
		if ($this->view->monterPalissadeCharretteOk == false) {
			throw new Zend_Exception(get_class($this)." Monter Palissade interdit : pas de charrette");
		}
		
		// on verifie que l'on peut monter une palissade sur la case
		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = split("h", $x_y);
		if ($offset_x < -$this->distance || $offset_x > $this->distance) {
			throw new Zend_Exception(get_class($this)." MonterPalissade X impossible : ".$offset_x);
		}
		
		if ($offset_y < -$this->distance || $offset_y > $this->distance) {
			throw new Zend_Exception(get_class($this)." MonterPalissade Y impossible : ".$offset_y);
		}
		
		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this)." MonterPalissade XY impossible : ".$offset_x.$offset_y);
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculMonterPalissade($this->view->user->x_hobbit + $offset_x, $this->view->user->y_hobbit + $offset_y);
			$this->majEvenementsStandard();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculMonterPalissade($x, $y) {
		
		/*
		 * [11.1-11*0.68] % -> 2+1D3
		 * [100-(11.1-11*0.68)-(10*0.68)] % -> 1+1D3
		 * [10*0.68] % -> 1D3
		 */
		$maitrise = $this->hobbit_competence["pourcentage_hcomp"];
		$chance_a = 11.1-11 * $maitrise;
		$chance_b = 100-(11.1-11 * $maitrise)-(10 * $maitrise);
		$chance_c = 10 * $maitrise;
		
		$tirage = Bral_Util_De::get_1d100();
		
		if ($tirage > 0 && $tirage <= $chance_a) {
			$this->view->nRondinsNecessaires = 2 + Bral_Util_De::get_1d3();
			$this->view->nRondinsNecessairesFormule = "2 + 1D3";
		} elseif ($tirage > $chance_a && $tirage <= $chance_b) {
			$this->view->nRondinsNecessaires = 1 + Bral_Util_De::get_1d3();
			$this->view->nRondinsNecessairesFormule = "1 + 1D3";
		} elseif ($tirage > $chance_b && $tirage <= 100) {
			$this->view->nRondinsNecessaires = Bral_Util_De::get_1d3();
			$this->view->nRondinsNecessairesFormule = "1D3";
		}
		
		$this->view->nRondinsSuffisants = false;
		
		if ($this->view->nRondins >= $this->view->nRondinsNecessaires) {
			$this->view->nRondinsSuffisants = true;
		} else {
			return;
		}
		
		$charretteTable = new Charrette();
		$data = array(
			'quantite_rondin_charrette' => -$this->view->nRondinsNecessaires,
			'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
		);
		$charretteTable->updateCharrette($data);
		
		$date_creation = date("Y-m-d H:i:s");
		$nb_jours = $this->view->user->vigueur_base_hobbit / 2;
		$date_fin = Bral_Util_ConvertDate::get_date_add_day_to_date($date_creation, $nb_jours);
		
		$data = array(
		"x_palissade"  => $x,
		"y_palissade" => $y,
		"agilite_palissade" => 0,
		"armure_naturelle_palissade" => $this->view->user->armure_naturelle_hobbit * 4,
		"pv_restant_palissade" => $this->view->user->pv_restant_hobbit * 4,
		"date_creation_palissade" => $date_creation,
		"date_fin_palissade" => $date_fin,
		);
		
		$palissadeTable = new Palissade();
		$palissadeTable->insert($data);
		
		$this->view->palissade = $data;
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_metiers", "box_laban", "box_charrette", "box_evenements");
	}
}
