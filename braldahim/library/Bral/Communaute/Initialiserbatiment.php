<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Initialiserbatiment extends Bral_Communaute_Communaute {

	function getTitreOnglet() {}
	function setDisplay($display) {}

	function getNomInterne() {
		return "box_action";
	}

	function getTitre() {
		return "Initialiser un bâtiment de communauté";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Communaute");

		$this->view->nomLieu = null;

		if ($this->view->user->rangCommunaute > Bral_Util_Communaute::ID_RANG_TENANCIER) {
			throw new Zend_Exception(get_class($this)." Vous n'êtes pas tenancier de la Communauté ". $this->view->user->rangCommunaute);
		}

		if (!Bral_Util_Communaute::possedeUnHall($this->view->user->id_fk_communaute_braldun)) {
			throw new Zend_Exception("Bral_Communaute_Initialiserbatiment :: Hall invalide idC:".$this->view->user->id_fk_communaute_braldun);
		}

		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('Palissade');

		$this->distance = 3;
		$this->view->x_min = $this->view->user->x_braldun - $this->distance;
		$this->view->x_max = $this->view->user->x_braldun + $this->distance;
		$this->view->y_min = $this->view->user->y_braldun - $this->distance;
		$this->view->y_max = $this->view->user->y_braldun + $this->distance;
		$this->view->nb_cases = 3;

		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);

		$defautChecked = false;

		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			$change_level = true;
			for ($i = -$this->distance; $i <= $this->distance; $i ++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;
				$z = $this->view->user->z_braldun;
					
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

				foreach($lieux as $l) {
					if ($x == $l["x_lieu"] && $y == $l["y_lieu"] && $z == $l["z_lieu"]) {
						$valid = false;
						break;
					}
				}

				foreach($palissades as $p) {
					if ($x == $p["x_palissade"] && $y == $p["y_palissade"] && $z == $p["z_palissade"]) {
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
					"valid" => $valid
				);

				$tabValidation[$i][$j] = $valid;
					
				if ($change_level) {
					$change_level = false;
				}
			}
		}

		// on selectionne tous les lieux de la communaute

		Zend_Loader::loadClass('TypeLieu');
		$typeLieuTable = new TypeLieu();
		$typesLieux = $typeLieuTable->findByTypeCommunaute();

		$tabTypesLieux = null;
		foreach($typesLieux as $t) {
			$display = true;
			foreach($lieux as $l) { // si dans les lieux, il y a déjà un lieu de même type
				if ($t["id_type_lieu"] == $l["id_fk_type_lieu"] && $l["id_fk_communaute_lieu"] == $this->view->user->id_fk_communaute_braldun) {
					// on ne pourra pas construire un bâtiment du même type une seconde fois
					$display = false;
				}
			}
			if ($display) {
				$tabTypesLieux[$t["id_type_lieu"]]["type"] = $t;
				$tabTypesLieux[$t["id_type_lieu"]]["selected"] = "";
			}
		}

		$this->view->tableau = $tab;
		$this->tableauValidation = $tabValidation;
		$this->view->typeLieux = $tabTypesLieux;

		$this->view->nb_pa = 1;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->assezDePa == false) {
			return;
		}

		if (((int)$this->_request->get("valeur_1").""!=$this->_request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Type invalide : ".$this->_request->get("valeur_1"));
		} else {
			$idTypeLieu = (int)$this->_request->get("valeur_1");
		}

		if (!array_key_exists($idTypeLieu, $this->view->typeLieux)) {
			throw new Zend_Exception(get_class($this)." Type invalide 2 : ".$idTypeLieu);
		}

		$x_y = $this->_request->get("valeur_2");
		list ($offset_x, $offset_y) = preg_split("/h/", $x_y);

		if ($offset_x < -$this->view->nb_cases || $offset_x > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this)." Position X impossible : ".$offset_x);
		}

		if ($offset_y < -$this->view->nb_cases || $offset_y > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this)." Position Y impossible : ".$offset_y);
		}

		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this)." Position XY impossible : ".$offset_x.$offset_y);
		}

		$x = $this->view->user->x_braldun + $offset_x;
		$y = $this->view->user->y_braldun + $offset_y;

		$this->initialiser($idTypeLieu, $x, $y);
		$this->majBraldun();
	}

	private function initialiser($idTypeLieu, $x, $y) {

		Zend_Loader::loadClass('Bral_Util_Communaute');
		Zend_Loader::loadClass('Communaute');

		$communauteTable = new Communaute();
		$communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
		if (count($communauteRowset) == 1) {
			$communaute = $communauteRowset[0];
		}

		$lieuTable = new Lieu();

		$nomLieu = $this->view->typeLieux[$idTypeLieu]["type"]["nom_type_lieu"]." de la communauté ".$communaute["nom_communaute"];

		$data = array(
			'nom_lieu' => $nomLieu,
			'description_lieu' => "",
			'x_lieu' => $x,
			'y_lieu' => $y,
			'z_lieu' => 0,
			'etat_lieu' => 100,
			'id_fk_type_lieu' => $idTypeLieu,
			'id_fk_communaute_lieu' => $this->view->user->id_fk_communaute_braldun,
			'date_creation_lieu' => date("Y-m-d H:i:s"),
			'niveau_lieu' => 0,
			'niveau_prochain_lieu' => 1,
			'nb_pa_depenses_lieu' => 0,
			'nb_castars_depenses_lieu' => 0,
			'date_entretien_lieu' => date("Y-m-d H:i:s"),
		);

		$lieuTable->insert($data);

		$this->view->nomLieu = $nomLieu;

		Zend_Loader::loadClass("TypeEvenementCommunaute");
		Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");

		$details = $nomLieu;
		$detailsBot = "Le bâtiment -".$nomLieu."- a été initialisé. ".PHP_EOL;
		$detailsBot .= "Le bâtiment est automatiquement en construction vers le niveau 1.".PHP_EOL.PHP_EOL;
		$detailsBot .= "Pour le construire complètement, chaque Braldûn de la communauté peut aller sur le bâtiment et ";
		$detailsBot .= "utiliser l'action -Construire un bâtiment- pour faire progresser la construction.".PHP_EOL.PHP_EOL;
		$detailsBot .= "La progression de chaque construction est visible dans l'onglet Communauté.".PHP_EOL.PHP_EOL;
		$detailsBot .= "Une fois la construction niveau 1 terminée, le bâtiment offrira de nouvelles possibilités pour la communauté, ainsi que des points d'influence.";

		$detailsBot .= PHP_EOL.PHP_EOL."Action réalisée par [b".$this->view->user->id_braldun."]";
		Bral_Util_EvenementCommunaute::ajoutEvenements($this->view->user->id_fk_communaute_braldun, TypeEvenementCommunaute::ID_TYPE_INITIALISATION_BATIMENT, $details, $detailsBot, $this->view);

	}

	function getListBoxRefresh() {
		$tab = array("box_profil", "box_lieu", "box_communaute", "box_evenements", "box_communaute_evenements", "box_cockpit");
		if ($this->view->nomLieu != null) {
			$tab[] = "box_vue";
		}
		return $tab;
	}

}