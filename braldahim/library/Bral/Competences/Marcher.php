<?php

class Bral_Competences_Marcher extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass('Zone'); 
		Zend_Loader::loadClass('Palissade');
		
		$zoneTable = new Zone();
		$zone = $zoneTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);

		// La requete ne doit renvoyer qu'une seule case
		if (count($zone) == 1) {
			$case = $zone[0];
		} else {
			throw new Zend_Exception(get_class($this)."::prepareFormulaire : Nombre de case invalide");
		}
		
		$this->view->environnement = $case["nom_environnement"];
		$this->nom_systeme_environnement = $case["nom_systeme_environnement"];
		
		/*
		 * Si le hobbit n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa = false) {
			return;
		}
		
		$this->view->marcherPossible = false;
		
		$this->distance = $this->view->nb_cases;
		$this->view->x_min = $this->view->user->x_hobbit - $this->distance;
		$this->view->x_max = $this->view->user->x_hobbit + $this->distance;
		$this->view->y_min = $this->view->user->y_hobbit - $this->distance;
		$this->view->y_max = $this->view->user->y_hobbit + $this->distance;
		
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		
		$this->tabValidationPalissade = null;
		for ($j = $this->view->nb_cases; $j >= -$this->view->nb_cases; $j--) {
			 for ($i = -$this->view->nb_cases; $i <= $this->view->nb_cases; $i++) {
				$x = $this->view->user->x_hobbit + $i;
			 	$y = $this->view->user->y_hobbit + $j;
			 	$this->tabValidationPalissade[$x][$y] = true;
			 }
		}
		foreach($palissades as $p) {
			$this->tabValidationPalissade[$p["x_palissade"]][$p["y_palissade"]] = false;
		}
		
		if ($this->view->nb_cases == 2) {
			$this->calculPalissade();
		}
		
		$defautChecked = false;
		
		for ($j = $this->view->nb_cases; $j >= -$this->view->nb_cases; $j --) {
			 $change_level = true;
			 for ($i = -$this->view->nb_cases; $i <= $this->view->nb_cases; $i ++) {
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
				
			 	// on regarde s'il n'y a pas de palissade
		 		if ($this->tabValidationPalissade[$x][$y] === false) {
		 			$valid = false;
		 		}

		 		if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
					$this->view->marcherPossible = true;
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
		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = split("h", $x_y);
		
		if ($offset_x < -$this->view->nb_cases || $offset_x > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this)." Deplacement X impossible : ".$offset_x);
		}
		
		if ($offset_y < -$this->view->nb_cases || $offset_y > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this)." Deplacement Y impossible : ".$offset_y);
		}
		
		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this)." Deplacement XY impossible : ".$offset_x.$offset_y);
		}
		
		$this->view->user->x_hobbit = $this->view->user->x_hobbit + $offset_x;
		$this->view->user->y_hobbit = $this->view->user->y_hobbit + $offset_y;
		$this->view->user->pa_hobbit = $this->view->user->pa_hobbit - $this->view->nb_pa;
		
		$id_type = $this->view->config->game->evenements->type->deplacement;
		$details = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a marché";
		$this->setDetailsEvenement($details, $id_type);
		
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_lieu", "box_evenements", "box_echoppes");
	}
	
	/* Pour marcher, le nombre de PA utilise est variable suivant l'environnement
	* sur lequel le hobbit marche :
	* Plaine : 1 PA jusqu'a 2 cases
	* Foret : 1 PA pour 1 case
	* Marais : 2 PA pour 1 case
	* Montagneux : 2 PA pour 1 case
	* Caverneux : 1 PA pour 1 case
	*/
	public function calculNbPa() {
		$this->view->effetMot = false;
		
		switch($this->nom_systeme_environnement) {
			case "plaine" :
				$this->view->nb_cases = 2;
				$this->view->nb_pa = 1;
				break;
			case "marais" :
				$this->view->nb_cases = 1;
				$this->view->nb_pa = 2;
				if (Bral_Util_Commun::getEquipementByNomSystemeMot($this->view->user->id_hobbit, "mot_p") != null) {
					$this->view->effetMot = true;
					$this->view->nb_pa = 1;
				}
				break;
			case "montagne" :
				$this->view->nb_cases = 1;
				$this->view->nb_pa = 2;
				if (Bral_Util_Commun::getEquipementByNomSystemeMot($this->view->user->id_hobbit, "mot_c") != null) {
					$this->view->effetMot = true;
					$this->view->nb_pa = 1;
				}
				break;
			case "foret" :
				$this->view->nb_cases = 1;
				$this->view->nb_pa = 1;
				if (Bral_Util_Commun::getEquipementByNomSystemeMot($this->view->user->id_hobbit, "mot_t") != null) {
					$this->view->effetMot = true;
					$this->view->nb_cases = 2;
				}
				break;
			case "caverne" :
				$this->view->nb_cases = 1;
				$this->view->nb_pa = 1;
				break;
			default:
				throw new Zend_Exception(get_class($this)."::environnement invalide :".$this->nom_systeme_environnement);
		}
		
		if ($this->view->user->pa_hobbit - $this->view->nb_pa < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
	}
	
	private function calculPalissade() {
		$nbCase = 1;
		
		for ($j = $nbCase; $j >= -$nbCase; $j--) {
			 for ($i = -$nbCase; $i <= $nbCase; $i++) {
			 	if ($j == 0 && $i == 0) {
			 		continue;
			 	}
			 	$x = $this->view->user->x_hobbit + $i;
			 	$y = $this->view->user->y_hobbit + $j;
			 	
			 	// d'abord les coins
			 	if ($j == 1 && $i == -1) {
			 		if ($this->tabValidationPalissade[$x][$y] == false) {
			 			$this->tabValidationPalissade[$x-1][$y+1] = false;
			 		}
			 	}
			 	if ($j == -1 && $i == -1) {
			 		if ($this->tabValidationPalissade[$x][$y] == false) {
			 			$this->tabValidationPalissade[$x-1][$y-1] = false;
			 		}
			 	}
			 	if ($j == -1 && $i == 1) {
			 		if ($this->tabValidationPalissade[$x][$y] == false) {
			 			$this->tabValidationPalissade[$x+1][$y-1] = false;
			 		}
			 	}
			 	if ($j == 1 && $i == 1) {
			 		if ($this->tabValidationPalissade[$x][$y] == false) {
			 			$this->tabValidationPalissade[$x+1][$y+1] = false;
			 		}
			 	}
			 	
			 	
			 	if ($j == 0 && $i == -1) {
			 		if ($this->tabValidationPalissade[$x][$y] == false) {
			 			if ($this->tabValidationPalissade[$x][$y+1] == false &&
			 				$this->tabValidationPalissade[$x][$y-1] == false) {
			 				$this->tabValidationPalissade[$x-1][$y] = false;
			 			}
				 		if ($this->tabValidationPalissade[$x][$y+1] == false) {
				 			$this->tabValidationPalissade[$x-1][$y+1] = false;
				 		}
			 			if ($this->tabValidationPalissade[$x][$y-1] == false) {
				 			$this->tabValidationPalissade[$x-1][$y-1] = false;
				 		}
			 		}
			 	}
			 	if ($j == 0 && $i == 1) {
			 		if ($this->tabValidationPalissade[$x][$y] == false) {
			 			if ($this->tabValidationPalissade[$x][$y+1] == false &&
			 				$this->tabValidationPalissade[$x][$y-1] == false) {
			 				$this->tabValidationPalissade[$x+1][$y] = false;
			 			}
				 		if ($this->tabValidationPalissade[$x][$y+1] == false) {
				 			$this->tabValidationPalissade[$x+1][$y+1] = false;
				 		}
			 			if ($this->tabValidationPalissade[$x][$y-1] == false) {
				 			$this->tabValidationPalissade[$x+1][$y-1] = false;
				 		}
			 		}
			 	}
			 	
			 	if ($j == 1 && $i == 0) {
			 		if ($this->tabValidationPalissade[$x][$y] == false) {
			 			if ($this->tabValidationPalissade[$x-1][$y] == false &&
			 				$this->tabValidationPalissade[$x+1][$y] == false) {
			 				$this->tabValidationPalissade[$x][$y+1] = false;
			 			}
				 		if ($this->tabValidationPalissade[$x-1][$y] == false) {
				 			$this->tabValidationPalissade[$x-1][$y+1] = false;
				 		}
			 			if ($this->tabValidationPalissade[$x+1][$y] == false) {
				 			$this->tabValidationPalissade[$x+1][$y+1] = false;
				 		}
			 		}
			 	}
			 	
			 	if ($j == -1 && $i == 0) {
			 		if ($this->tabValidationPalissade[$x][$y] == false) {
			 			if ($this->tabValidationPalissade[$x-1][$y] == false &&
			 				$this->tabValidationPalissade[$x+1][$y] == false) {
			 				$this->tabValidationPalissade[$x][$y-1] = false;
			 			}
				 		if ($this->tabValidationPalissade[$x-1][$y] == false) {
				 			$this->tabValidationPalissade[$x-1][$y-1] = false;
				 		}
			 			if ($this->tabValidationPalissade[$x+1][$y] == false) {
				 			$this->tabValidationPalissade[$x+1][$y-1] = false;
				 		}
			 		}
			 	}
			 }
		}
	}
}