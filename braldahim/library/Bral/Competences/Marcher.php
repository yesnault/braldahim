<?php

class Bral_Competences_Marcher extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass('zone'); 
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
	}
	
	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
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
			 	
			 	if ($i == -1 && $j == 1 && $valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
			 	} else if ($i == 1 && $j == -1 && $valid === true && $defautChecked == false) {
			 		$default = "checked";
			 		$defautChecked = true;
			 	} else {
			 		$default = "";
			 	}
			 	
			 	$tab[] = array ("x_offset" => $i,
			 	"y_offset" => $j,
			 	"default" => $default,
			 	"display" => $display,
			 	"change_level" => $change_level, // nouvelle ligne dans le tableau
				"valid" => $valid);	
				
				if ($change_level) {
					$change_level = false;
				}
			 }
		}
		$this->view->tableau = $tab;
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
		
		$this->view->user->x_hobbit = $this->view->user->x_hobbit + $offset_x;
		$this->view->user->y_hobbit = $this->view->user->y_hobbit + $offset_y;
		$this->view->user->pa_hobbit = $this->view->user->pa_hobbit - $this->view->nb_pa;
				
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();

		$data = array( 
			'x_hobbit' => $this->view->user->x_hobbit,
			'y_hobbit'  => $this->view->user->y_hobbit,
			'pa_hobbit' => $this->view->user->pa_hobbit,
		); 
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
		
		$id_type = $this->view->config->game->evenements->type->deplacement;
		$details = $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a marché";
		$this->majEvenements($this->view->user->id_hobbit, $id_type, $details);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_communes", "box_competences_basiques", "box_competences_metiers", "box_lieu", "box_evenements");
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
		switch($this->nom_systeme_environnement) {
			case "plaine" :
				$this->view->nb_cases = 2;
				$this->view->nb_pa = 1;
				break;
			case "marais" :
				$this->view->nb_cases = 2;
				$this->view->nb_pa = 1; 
				break;
			case "montagne" :
				$this->view->nb_cases = 1;
				$this->view->nb_pa = 2;
				break;
			case "foret" :
				$this->view->nb_cases = 1;
				$this->view->nb_pa = 1;
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
}