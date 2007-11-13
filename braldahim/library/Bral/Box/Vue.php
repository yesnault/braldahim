<?php

class Bral_Box_Vue {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass("Cadavre");
		Zend_Loader::loadClass("Castar");
		Zend_Loader::loadClass("Filon");
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("HobbitsMetiers");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Palissade");
		Zend_Loader::loadClass("Plante");
		Zend_Loader::loadClass("Region");
		Zend_Loader::loadClass("Rune");
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Ville");
		Zend_Loader::loadClass("Zone");

		Zend_Loader::loadClass('Bral_Util_Commun');

		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		$this->prepare();
		$this->deplacement();
	}

	function getTitreOnglet() {
		return "Vue";
	}

	function getNomInterne() {
		return "box_vue";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		$this->data();
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/vue.phtml");
	}

	private function prepare() {
		$commun = new Bral_Util_Commun();
		$this->view->vue_nb_cases = $commun->getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit;
		$this->view->x_min = $this->view->user->x_hobbit - $this->view->vue_nb_cases;
		$this->view->x_max = $this->view->user->x_hobbit + $this->view->vue_nb_cases;
		$this->view->y_min = $this->view->user->y_hobbit - $this->view->vue_nb_cases;
		$this->view->y_max = $this->view->user->y_hobbit + $this->view->vue_nb_cases;

		if (($this->_request->get("caction") == "box_vue") && ($this->_request->get("valeur_1") != "")) { // si le joueur a clique sur une icone
			$this->deplacement = $this->_request->get("valeur_1");
			$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->_request->get("valeur_2"), 0);
			$this->view->centre_y = $this->get_deplacement_verif($this->view->y_min, $this->view->y_max, $this->_request->get("valeur_3"), 0);
		} else {
			$this->view->centre_x = $this->view->user->x_hobbit;
			$this->view->centre_y = $this->view->user->y_hobbit;
		}
	}

	private function deplacement() {
		switch ($this->_request->get("valeur_1")) {
			case "hg" :
				$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->view->centre_x, -1);
				$this->view->centre_y = $this->get_deplacement_verif($this->view->y_min, $this->view->y_max, $this->view->centre_y, +1);
				break;
			case "h" :
				$this->view->centre_y = $this->get_deplacement_verif($this->view->y_min, $this->view->y_max, $this->view->centre_y, +1);
				break;
			case "hd" :
				$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->view->centre_x, +1);
				$this->view->centre_y = $this->get_deplacement_verif($this->view->y_min, $this->view->y_max, $this->view->centre_y, +1);
				break;
			case "g" :
				$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->view->centre_x, -1);
				break;
			case "d" :
				$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->view->centre_x, +1);
				break;
			case "bg" :
				$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->view->centre_x, +1);
				$this->view->centre_y = $this->get_deplacement_verif($this->view->y_min, $this->view->y_max, $this->view->centre_y, -1);
				break;
			case "b" :
				$this->view->centre_y = $this->get_deplacement_verif($this->view->y_min, $this->view->y_max, $this->view->centre_y, -1);
				break;
			case "bd" :
				$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->view->centre_x, +1);
				$this->view->centre_y = $this->get_deplacement_verif($this->view->y_min, $this->view->y_max, $this->view->centre_y, -1);
				break;
			default :
				return null;
		}
		$this->view->centre_x_min = $this->view->centre_x - $this->view->config->game->box_vue_taille;
		$this->view->centre_x_max = $this->view->centre_x + $this->view->config->game->box_vue_taille;
		$this->view->centre_y_min = $this->view->centre_y - $this->view->config->game->box_vue_taille;
		$this->view->centre_y_max = $this->view->centre_y + $this->view->config->game->box_vue_taille;
	}

	private function get_deplacement_verif($min, $max, $centre, $offset) {
		if (intval($centre) != $centre) {
			throw new Exception("Valeur invalide : $centre <-> intval($centre)");
		}
		if ($centre + $offset < $min) {
			return $centre;
		}
		if ($centre + $offset > $max) {
			return $centre;
		}
		return $centre + $offset;
	}

	private function data() {
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		$tabMetiers = null;
		foreach($hobbitsMetierRowset as $m) {
			$possedeMetier = true;
			$tabMetiers[] = $m["nom_systeme_metier"];
		}
		
		$cadavreTable = new Cadavre();
		$cadavres = $cadavreTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$castarTable = new Castar();
		$castars = $castarTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$filons = null;
		if (in_array("mineur", $tabMetiers)) {
			$filonsTable = new Filon();
			$filons = $filonsTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		}
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$plantes = null;
		if (in_array("herboriste", $tabMetiers)) {
			$planteTable = new Plante();
			$plantes = $planteTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		}
		$regionTable = new Region();
		$regions = $regionTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$villeTable = new Ville();
		$villes = $villeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$zoneTable = new Zone();
		$zones = $zoneTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		$runeTable = new Rune();
		$runes = $runeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		
		$centre_x_min = $this->view->centre_x - $this->view->config->game->box_vue_taille;
		$centre_x_max = $this->view->centre_x + $this->view->config->game->box_vue_taille;
		$centre_y_min = $this->view->centre_y - $this->view->config->game->box_vue_taille;
		$centre_y_max = $this->view->centre_y + $this->view->config->game->box_vue_taille;

		for ($j = $centre_y_max; $j >= $centre_y_min; $j --) {
			$change_level = true;
			for ($i = $centre_x_min; $i <= $centre_x_max; $i ++) {
				$display_x = $i;
				$display_y = $j;
				$tabCadavres = null;
				$tabCastars = null;
				$tabFilons = null;
				$tabHobbits = null;
				$tabLieux = null;
				$tabMonstres = null;
				$tabPalissades = null;
				$tabPlantes = null;
				$tabRunes = null;
				$nom_systeme_environnement = null;
				$nom_environnement = null;
				$nom_zone = null;
				$description_zone = null;
				$ville = null;
					
				if (($j > $this->view->y_max) || ($j < $this->view->y_min) ||
				($i < $this->view->x_min) || ($i > $this->view->x_max)) {
					$nom_systeme_environnement = "inconnu";
				} else {
					foreach($zones as $z) {
						if ($display_x >= $z["x_min_zone"] &&
						$display_x <= $z["x_max_zone"] &&
						$display_y >= $z["y_min_zone"] &&
						$display_y <= $z["y_max_zone"]) {
							$nom_zone = $z["nom_zone"];
							$description_zone = $z["description_zone"];
							$nom_systeme_environnement = $z["nom_systeme_environnement"];
							$nom_environnement = htmlentities($z["nom_environnement"]);
							break;
						}
					}

					foreach($cadavres as $c) {
						if ($display_x == $c["x_cadavre"] && $display_y == $c["y_cadavre"]) {
							if ($c["genre_type_monstre"] == 'feminin') {
								$c_taille = $c["nom_taille_f_monstre"];
							} else {
								$c_taille = $c["nom_taille_m_monstre"];
							}
							$tabCadavres[] = array("id_cadavre" => $c["id_cadavre"], "nom_cadavre" => $c["nom_type_monstre"], 'taille_cadavre' => $c_taille);
						}
					}

					foreach($castars as $c) {
						if ($display_x == $c["x_castar"] && $display_y == $c["y_castar"]) {
							$tabCastars[] = array("nb_castar" => $c["nb_castar"]);
						}
					}
					
					foreach($filons as $f) {
						if ($display_x == $f["x_filon"] && $display_y == $f["y_filon"]) {
							$tabFilons[] = array("type" => $f["nom_type_minerai"]);
						}
					}
					
					foreach($hobbits as $h) {
						if ($display_x == $h["x_hobbit"] && $display_y == $h["y_hobbit"]) {
							$tabHobbits[] = array("id_hobbit" => $h["id_hobbit"], "nom_hobbit" => $h["nom_hobbit"], "niveau_hobbit" => $h["niveau_hobbit"]);
						}
					}

					foreach($lieux as $l) {
						if ($display_x == $l["x_lieu"] && $display_y == $l["y_lieu"]) {
							$tabLieux[] = array("id_lieu" => $l["id_lieu"], "nom_lieu" => $l["nom_lieu"], "nom_type_lieu" => $l["nom_type_lieu"]);
							$lieuCourant = $l;
							$estLimiteVille = false;
						}
					}

					foreach($monstres as $m) {
						if ($display_x == $m["x_monstre"] && $display_y == $m["y_monstre"]) {
							if ($m["genre_type_monstre"] == 'feminin') {
								$m_taille = $m["nom_taille_f_monstre"];
							} else {
								$m_taille = $m["nom_taille_m_monstre"];
							}
							$tabMonstres[] = array("id_monstre" => $m["id_monstre"], "nom_monstre" => $m["nom_type_monstre"], 'taille_monstre' => $m_taille, 'niveau_monstre' => $m["niveau_monstre"]);
						}
					}
					
					foreach($villes as $v) {
						if ($display_x >= $v["x_min_ville"] &&
						$display_x <= $v["x_max_ville"] &&
						$display_y >= $v["y_min_ville"] &&
						$display_y <= $v["y_max_ville"]) {
							$estLimiteVille = false;

							if ($v["x_min_ville"] == $display_x || $v["x_max_ville"] == $display_y || $v["y_min_ville"] == $display_x || $v["y_max_ville"] == $display_y ) {
								$estLimiteVille = true;
							}
							$ville = array("estLimite" => $estLimiteVille, "nom_ville" => $v["nom_ville"], "est_capitale" => $v["est_capitale_ville"] , "nom_systeme" => $v["nom_systeme_ville"], "nom_region" => $v["nom_region"]);
							break;
						}
					}
					foreach($regions as $r) {
						if ($display_x >= $r["x_min_region"] &&
						$display_x <= $r["x_max_region"] &&
						$display_y >= $r["y_min_region"] &&
						$display_y <= $r["y_max_region"]) {
							$region = array("nom" => $r["nom_region"]);
							break;
						}
					}
					foreach($palissades as $p) {
						if ($display_x == $p["x_palissade"] && $display_y == $p["y_palissade"]) {
							$tabPalissades[] = array("id_palissade" => $p["id_palissade"]);
						}
					}
					foreach($plantes as $p) {
						if ($display_x == $p["x_plante"] && $display_y == $p["y_plante"]) {
							$tabPlantes[] = array("id_plante" => $p["id_plante"], "type" => $p["nom_type_plante"],
							"categorie" => $p["categorie_type_plante"],
							"quantite_1" =>$p["partie_1_plante"], "quantite_2" =>$p["partie_2_plante"],
							"quantite_3" =>$p["partie_3_plante"], "quantite_4" =>$p["partie_4_plante"],
							"nom_partie_1" =>"TODO", "nom_partie_2" =>"TODO",
							"nom_partie_3" =>"TODO", "nom_partie_4" =>"TODO");
						}
					}
					foreach($runes as $r) {
						if ($display_x == $r["x_rune"] && $display_y == $r["y_rune"]) {
							$tabRunes[] = array("id_rune" => $r["id_rune"]);
						}
					}
				}

				if ($this->view->user->x_hobbit == $display_x && $this->view->user->y_hobbit == $display_y) { // Position du joueur
					$actuelle = true;
					$css = "actuelle";
					$this->view->environnement = $nom_environnement;
					$this->view->centre_nom_region = $region["nom"];
					$this->view->centre_nom_ville = $ville["nom_ville"];
					$this->view->centre_est_capitale = ($ville["est_capitale"] == "oui");
				} else {
					$actuelle = false;
					$css = $nom_systeme_environnement;
				}

				if ($this->view->centre_x == $display_x && $this->view->centre_y == $display_y) {
					$this->view->centre_environnement = $nom_environnement;
				}

				$tab = array ("x" => $display_x, "y" => $display_y, //
				"change_level" => $change_level, // nouvelle ligne dans le tableau ;
				"position_actuelle" => $actuelle,
				"nom_zone" => $nom_zone,
				"description_zone" => $nom_zone,
				"css" => $css,
				"n_cadavres" => count($tabCadavres),
				"cadavres" => $tabCadavres,
				"n_castars" => count($tabCastars),
				"castars" => $tabCastars,
				"n_filons" => count($tabFilons),
				"filons" => $tabFilons,
				"n_hobbits" => count($tabHobbits),
				"hobbits" => $tabHobbits,
				"n_lieux" => count($tabLieux),
				"lieux" => $tabLieux,
				"n_monstres" => count($tabMonstres),
				"monstres" => $tabMonstres,
				"n_palissades" => count($tabPalissades),
				"palissades" => $tabPalissades,
				"n_runes" => count($tabRunes),
				"runes" => $tabRunes,
				"ville" => $ville,
				"n_plantes" => count($tabPlantes),
				"plantes" => $tabPlantes,
				);
				$tableau[] = $tab;
				if ($change_level) {
					$change_level = false;
				}
			}
		}

		$this->view->tableau = $tableau;
	}
}
