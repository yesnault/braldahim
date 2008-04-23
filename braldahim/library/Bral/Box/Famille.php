<?php

class Bral_Box_Famille extends Bral_Box_Box {

//	function __construct($request, $view, $interne) {
//		$this->_request = $request;
//		$this->view = $view;
//		$this->view->affichageInterne = $interne;
//	}

	function getTitreOnglet() {
		return "Famille";
	}

	function getNomInterne() {
		return "box_famille";
	}

	function getChargementInBoxes() {
		return false;
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/famille.phtml");
	}
	
	private function data() {
		Zend_Loader::loadClass('Couple');
		$hobbitTable = new Hobbit();
	
		$this->view->pereMereOk = false;
		if ($this->view->user->id_fk_mere_hobbit != null && $this->view->user->id_fk_pere_hobbit != null &&
			$this->view->user->id_fk_mere_hobbit != 0 && $this->view->user->id_fk_pere_hobbit != 0 ) {
			$this->view->pereMereOk = true;
			
			$hobbitTable = new Hobbit();
			$pere = $hobbitTable->findById($this->view->user->id_fk_pere_hobbit);
			$mere = $hobbitTable->findById($this->view->user->id_fk_mere_hobbit);
			
			$this->view->pere = $pere;
			$this->view->mere = $mere;
		}
		
		// on regarde s'il y a des enfants
		$enfants = null;
		$enfantsRowset = $hobbitTable->findEnfants($this->view->user->sexe_hobbit, $this->view->user->id_hobbit);
		
		$this->view->nbEnfants = count($enfantsRowset);
		
		if (count($this->view->nbEnfants) > 0) {
			foreach($enfantsRowset as $e) {
				$enfants[] = array("prenom" => $e["prenom_hobbit"],
									"nom" => $e["nom_hobbit"],
									"id_hobbit" => $e["id_hobbit"],
									"sexe_hobbit" => $e["sexe_hobbit"],
									"date_naissance" => $e["date_creation_hobbit"]);
			}
			
		}
		$this->view->enfants = $enfants;
		
		// on va chercher les informations du conjoint
		$coupleTable = new Couple();
		$conjointRowset = $coupleTable->findConjoint($this->view->user->sexe_hobbit, $this->view->user->id_hobbit);
		$conjoint = null;
		if (count($conjointRowset) > 0) {
			foreach($conjointRowset as $c) {
				$conjoint = array(
					"prenom" => $c["prenom_hobbit"],
					"nom" => $c["nom_hobbit"],
					"id_hobbit" => $c["id_hobbit"]
				);
			}
		}
		
		$this->view->conjoint = $conjoint;
		
		$this->view->dateNaissance = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y \&\a\g\r\a\v\e; H:i:s',$this->view->user->date_creation_hobbit);
		$this->view->nom_interne = $this->getNomInterne();
	}
}
