<?php

class Bral_Box_Laban {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass('LabanPlante');
		Zend_Loader::loadClass('LabanMinerai');
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}

	function getTitreOnglet() {
		return "Laban";
	}

	function getNomInterne() {
		return "box_laban";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$tabPlantes = null;
		$labanPlanteTable = new LabanPlante();
		$plantes = $labanPlanteTable->findByIdHobbit($this->view->user->id_hobbit);

		foreach ($plantes as $p) {
			$tabPlantes[] = array(
			"id_laban_plante" => $p["id_laban_plante"],
			"type" => $p["nom_type_plante"],
			"categorie" => $p["categorie_type_plante"],
			"nom_partie_1" => $p["nom_partie_1_type_plante"],
			"nom_partie_2" => $p["nom_partie_2_type_plante"],
			"nom_partie_3" => $p["nom_partie_3_type_plante"],
			"nom_partie_4" => $p["nom_partie_4_type_plante"],
			"partie_1" => $p["partie_1_laban_plante"],
			"partie_2" => $p["partie_2_laban_plante"],
			"partie_3" => $p["partie_3_laban_plante"],
			"partie_4" => $p["partie_4_laban_plante"],
			);
		}

		$tabMinerais = null;
		$labanMineraiTable = new LabanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);

		foreach ($minerais as $m) {
			$tabMinerais[] = array(
			"type" => $m["nom_type_minerai"],
			"quantite" => $m["quantite_laban_minerai"],
			);
		}

		$this->view->nb_plantes = count($tabPlantes);
		$this->view->plantes = $tabPlantes;
		$this->view->nb_minerais = count($tabMinerais);
		$this->view->minerais = $tabMinerais;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/laban.phtml");
	}
}
?>