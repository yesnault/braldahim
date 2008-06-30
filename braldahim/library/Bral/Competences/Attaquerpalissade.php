<?php

class Bral_Competences_Attaquerpalissade extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Echoppe'); 	
		Zend_Loader::loadClass('Palissade');  	

		$this->view->attaquerPalissadeOk = false;
	
		$this->distance = 1;
		$this->view->x_min = $this->view->user->x_hobbit - $this->distance;
		$this->view->x_max = $this->view->user->x_hobbit + $this->distance;
		$this->view->y_min = $this->view->user->y_hobbit - $this->distance;
		$this->view->y_max = $this->view->user->y_hobbit + $this->distance;
		
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);

		$defautChecked = false;
		
		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			 $change_level = true;
			 for ($i = -$this->distance; $i <= $this->distance; $i ++) {
			 	$x = $this->view->user->x_hobbit + $i;
			 	$y = $this->view->user->y_hobbit + $j;
			 	
			 	$display = $x;
			 	$display .= " ; ";
			 	$display .= $y;
			 	
				$valid = false;
			 	
			 	foreach($palissades as $p) {
					if ($x == $p["x_palissade"] && $y == $p["y_palissade"]) {
						$valid = true;
						break;
					}
				}

			 	if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
			 		$this->view->attaquerPalissadeOk = true;
			 	} else {
			 		$default = "";
			 	}

			 	$tab[] = array ("x_offset" => $i,
				 	"y_offset" => $j,
				 	"default" => $default,
				 	"display" => $display,
				 	"change_level" => $change_level, // nouvelle ligne dans le tableau
					"valid" => $valid
			 	);	
				
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
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}
		
		if ($this->view->attaquerPalissadeOk == false) {
			throw new Zend_Exception(get_class($this)." Attaquer Palissade interdit");
		}

		// on verifie que l'on peut attaquer une palissade sur la case
		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = split("h", $x_y);
		if ($offset_x < -$this->distance || $offset_x > $this->distance) {
			throw new Zend_Exception(get_class($this)." AttaquerPalissade X impossible : ".$offset_x);
		}
		
		if ($offset_y < -$this->distance || $offset_y > $this->distance) {
			throw new Zend_Exception(get_class($this)." AttaquerPalissade Y impossible : ".$offset_y);
		}
		
		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this)." AttaquerPalissade XY impossible : ".$offset_x.$offset_y);
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculAttaquerPalissade($this->view->user->x_hobbit + $offset_x, $this->view->user->y_hobbit + $offset_y);
		}
		
		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculAttaquerPalissade($x, $y) {
		
		/*
		 * [11.1-11*0.68] % -> 2+1D3
		 * [100-(11.1-11*0.68)-(10*0.68)] % -> 1+1D3
		 * [10*0.68] % -> 1D3
		 */
		$maitrise = $this->hobbit_competence["pourcentage_hcomp"];
		$chance_a = 11.1-11 * $maitrise;
		$chance_b = 100-(11.1-11 * $maitrise)-(10 * $maitrise);
		$chance_c = 10 * $maitrise;
		
		
		/*
		 * Afin de déterminer la qualité de la palissage n jet de dés sont effectués. 
		 * Seul le meilleur des n jets est gardé. n=(BM SAG/2)+1.
		 */
		$n = (($this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit) / 2 ) + 1;
		
		if ($n < 1) $n = 1;
		
		$tirage = 0;
		
		for ($i = 1; $i <= $n; $i ++) {
			$tirageTemp = Bral_Util_De::get_1d100();
			if ($tirageTemp > $tirage) {
				$tirage = $tirageTemp;
			}
		}
		
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
		unset($charretteTable);
		
		$date_creation = date("Y-m-d H:i:s");
		$nb_jours = ($this->view->user->vigueur_base_hobbit / 2) + Bral_Util_De::get_1d3();;
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
		unset($palissadeTable);
		
		$this->view->palissade = $data;
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_competences_metiers", "box_vue", "box_laban", "box_charrette", "box_evenements");
	}
}
