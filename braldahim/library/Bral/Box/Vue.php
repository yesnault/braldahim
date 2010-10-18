<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Vue extends Bral_Box_Box {

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
		if ($this->view->affichageInterne === true) {
			Zend_Loader::loadClass("Charrette");
			Zend_Loader::loadClass("Echoppe");
			Zend_Loader::loadClass("Champ");
			Zend_Loader::loadClass("Crevasse");
			Zend_Loader::loadClass("Element");
			Zend_Loader::loadClass("ElementAliment");
			Zend_Loader::loadClass("ElementEquipement");
			Zend_Loader::loadClass("ElementMunition");
			Zend_Loader::loadClass("ElementPartieplante");
			Zend_Loader::loadClass("ElementMateriel");
			Zend_Loader::loadClass("ElementMinerai");
			Zend_Loader::loadClass("ElementPotion");
			Zend_Loader::loadClass("ElementGraine");
			Zend_Loader::loadClass("ElementIngredient");
			Zend_Loader::loadClass("ElementRune");
			Zend_Loader::loadClass("ElementTabac");
			Zend_Loader::loadClass("Environnement");
			Zend_Loader::loadClass("Lieu");
			Zend_Loader::loadClass("BraldunsMetiers");
			Zend_Loader::loadClass("Monstre");
			Zend_Loader::loadClass("Nid");
			Zend_Loader::loadClass("Palissade");
			Zend_Loader::loadClass("Region");
			Zend_Loader::loadClass("Buisson");
			Zend_Loader::loadClass("Bosquet");
			Zend_Loader::loadClass("TypeLieu");
			Zend_Loader::loadClass("Route");
			Zend_Loader::loadClass("Eau");
			Zend_Loader::loadClass("SouleMatch");
			Zend_Loader::loadClass("Tunnel");
			Zend_Loader::loadClass("Ville");
			Zend_Loader::loadClass("Zone");
			Zend_Loader::loadClass('Bral_Util_Marcher');
			Zend_Loader::loadClass("Bral_Util_Equipement");
			Zend_Loader::loadClass("Bral_Util_Potion");

			$this->prepare();
			$this->deplacement();
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/vue.phtml");
	}

	private function prepare() {
		if ($this->view->user->administrationvue === true) {
			$this->prepareAdministrationVue();
		}

		if ($this->view->user->administrationvue === true && $this->view->user->administrationvueDonnees != null) {
			$x = $this->view->user->administrationvueDonnees["x_position"];
			$y = $this->view->user->administrationvueDonnees["y_position"];
			$z = $this->view->user->administrationvueDonnees["z_position"];
			$bm = 10;

		} else {
			$x = $this->view->user->x_braldun;
			$y = $this->view->user->y_braldun;
			$z = $this->view->user->z_braldun;
			$bm = $this->view->user->vue_bm_braldun;
		}

		$this->view->vue_nb_cases = Bral_Util_Commun::getVueBase($x, $y, $z) + $bm;
		if ($this->view->vue_nb_cases < 0) {
			$this->view->vue_nb_cases = 0;
		}
		$this->view->x_min = $x - $this->view->vue_nb_cases;
		$this->view->x_max = $x + $this->view->vue_nb_cases;
		$this->view->y_min = $y - $this->view->vue_nb_cases;
		$this->view->y_max = $y + $this->view->vue_nb_cases;

		$this->view->z_position = $z;

		$this->view->estVueEtendue = false;

		if (($this->_request->get("caction") == "box_vue") && ($this->_request->get("valeur_1") != "")) { // si le joueur a clique sur une icone
			$this->deplacement = $this->_request->get("valeur_1");
			$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->_request->get("valeur_2"), 0);
			$this->view->centre_y = $this->get_deplacement_verif($this->view->y_min, $this->view->y_max, $this->_request->get("valeur_3"), 0);
		} else if ($this->_request->get("caction") == "voir") {
			$this->view->estVueEtendue = true;
		} else {
			$this->view->centre_x = $x;
			$this->view->centre_y = $y;
		}
	}

	private function prepareAdministrationVue() {
		Zend_Loader::loadClass("Ville");
		$villeTable = new Ville();
		$villes = $villeTable->fetchAll();
		$this->view->administrationVilles = $villes;

		Zend_Loader::loadClass("Lieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->fetchAll();
		$this->view->administrationLieux = $lieux;
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
				$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->view->centre_x, -1);
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
		$this->view->environnement = null;
		$this->view->centre_nom_region = null;
		$this->view->est_pvp_region = null;
		$this->view->centre_description_region = null;
		$this->view->centre_nom_ville = null;
		$this->view->centre_est_capitale = null;
			
		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($this->view->user->id_braldun);
		unset($braldunsMetiersTable);
		$tabMetiers = null;
		if ($braldunsMetierRowset != null) {
			foreach($braldunsMetierRowset as $m) {
				$possedeMetier = true;
				$tabMetiers[] = $m["nom_systeme_metier"];
			}
			unset($braldunsMetierRowset);
		}

		$monstreTable = new Monstre();
		$cadavres = $monstreTable->selectVueCadavre($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($monstreTable);
		$charretteTable = new Charrette();
		$charrettes = $charretteTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($charretteTable);
		$champTable = new Champ();
		$champs = $champTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($champTable);
		$crevasseTable = new Crevasse();
		$crevasses = $crevasseTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position, 'oui');
		unset($crevasseTable);
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($echoppeTable);
		$elementTable = new Element();
		$elements = $elementTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementTable);
		$elementEquipementTable = new ElementEquipement();
		$elementsEquipements = $elementEquipementTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementEquipementTable);
		$elementMunitionsTable = new ElementMunition();
		$elementsMunitions = $elementMunitionsTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementMunitionsTable);
		$elementPartiePlanteTable = new ElementPartieplante();
		$elementsPartieplantes = $elementPartiePlanteTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementPartiePlanteTable);
		$elementMaterielTable = new ElementMateriel();
		$elementsMateriels = $elementMaterielTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementMaterielTable);
		$elementMineraisTable = new ElementMinerai();
		$elementsMinerais = $elementMineraisTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementMineraisTable);
		$elementPotionTable = new ElementPotion();
		$elementsPotions = $elementPotionTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementPotionTable);
		$elementAlimentTable = new ElementAliment();
		$elementsAliments = $elementAlimentTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementAlimentTable);
		$elementGraineTable = new ElementGraine();
		$elementsGraines = $elementGraineTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementGraineTable);
		$elementIngredientTable = new ElementIngredient();
		$elementsIngredients = $elementIngredientTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementIngredientTable);
		$elementRuneTable = new ElementRune();
		$elementsRunes = $elementRuneTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementRuneTable);
		$elementTabacTable = new ElementTabac();
		$elementsTabac = $elementTabacTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($elementTabacTable);
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position, -1, true, true);
		unset($braldunTable);
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($lieuxTable);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($monstreTable);
		$nidTable = new Nid();
		$nids = $nidTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($nidTable);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($palissadeTable);
		$buissonTable = new Buisson();
		$buissons = $buissonTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($bosquetTable);
		$eauTable = new Eau();
		$eaux = $eauTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($eauTable);
		$bosquetTable = new Bosquet();
		$bosquets = $bosquetTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($bosquetTable);
		$regionTable = new Region();
		$regions = $regionTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($regionTable);
		$routeTable = new Route();
		if ($this->view->user->administrationvue === true) {
			$routes = $routeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position, "toutes");
		} else {
			$routes = $routeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		}
		unset($routeTable);
		$souleMatchTable = new SouleMatch();
		$souleMatch = $souleMatchTable->selectBallonVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($souleMatchTable);

		$tunnels = null;
		if ($this->view->z_position < 10) {
			$tunnelTable = new Tunnel();
			$tunnels = $tunnelTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
			unset($tunnelTable);
		}
		$villeTable = new Ville();
		$villes = $villeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($villeTable);
		$zoneTable = new Zone();
		$zones = $zoneTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->z_position);
		unset($zoneTable);

		if ($this->view->estVueEtendue == false) {
			$centre_x_min = $this->view->centre_x - $this->view->config->game->box_vue_taille;
			$centre_x_max = $this->view->centre_x + $this->view->config->game->box_vue_taille;
			$centre_y_min = $this->view->centre_y - $this->view->config->game->box_vue_taille;
			$centre_y_max = $this->view->centre_y + $this->view->config->game->box_vue_taille;
		} else {
			$centre_x_min = $this->view->x_min;
			$centre_x_max = $this->view->x_max;
			$centre_y_min = $this->view->y_min;
			$centre_y_max = $this->view->y_max;
		}

		$marcher = null;
		if ($this->view->estVueEtendue === false && $this->view->user->administrationvue == false) {
			$utilMarcher = new Bral_Util_Marcher();
			$marcher = $utilMarcher->calcul($this->view->user);
		}

		for ($j = $centre_y_max; $j >= $centre_y_min; $j --) {
			$change_level = true;
			for ($i = $centre_x_min; $i <= $centre_x_max; $i ++) {
				$display_x = $i;
				$display_y = $j;
				$tabCadavres = null;
				$tabCastars = null;
				$tabCharrettes = null;
				$tabChamps = null;
				$tabCrevasses = null;
				$tabEaux = null;
				$tabEchoppes = null;
				$tabElements = null;
				$tabElementsEquipements = null;
				$tabElementsMateriels = null;
				$tabElementsMunitions = null;
				$tabElementsMineraisBruts = null;
				$tabElementsLingots = null;
				$tabElementsPartieplantesBrutes = null;
				$tabElementsPartieplantesPreparees = null;
				$tabElementsPotions = null;
				$tabElementsAliments = null;
				$tabElementsGraines = null;
				$tabElementsIngredients = null;
				$tabElementsRunes = null;
				$tabElementsTabac = null;
				$tabBralduns = null;
				$tabBraldunsKo = null;
				$tabLieux = null;
				$tabMonstres = null;
				$tabNids = null;
				$tabPalissades = null;
				$tabBuissons = null;
				$tabBosquets = null;
				$tabRoutes = null;
				$tabBallons = null;
				$tabTunnels = null;
				$nom_systeme_environnement = null;
				$nom_environnement = null;
				$nom_zone = null;
				$est_mine_zone = null;
				$description_zone = null;
				$ville = null;
					
				if (($j > $this->view->y_max) || ($j < $this->view->y_min) ||
				($i < $this->view->x_min) || ($i > $this->view->x_max) ||
				($j > $this->view->config->game->y_max) || ($j < $this->view->config->game->y_min) ||
				($i < $this->view->config->game->x_min) || ($i > $this->view->config->game->x_max)
				) {
					$nom_systeme_environnement = "inconnu";
				} else {
					foreach($zones as $z) {
						if ($display_x >= $z["x_min_zone"] &&
						$display_x <= $z["x_max_zone"] &&
						$display_y >= $z["y_min_zone"] &&
						$display_y <= $z["y_max_zone"]) {
							$nom_zone = $z["nom_zone"];
							$est_mine_zone = $z["est_mine_zone"];
							$description_zone = $z["description_zone"];
							if ($est_mine_zone == "oui") {
								$nom_systeme_environnement = Environnement::INCONNU;
							} else {
								$nom_systeme_environnement = $z["nom_systeme_environnement"];
							}
							$nom_environnement = htmlspecialchars($z["nom_environnement"]);
							break;
						}
					}

					if ($tunnels != null && $est_mine_zone == "oui") {
						foreach($tunnels as $t) {
							if ($display_x == $t["x_tunnel"] && $display_y == $t["y_tunnel"]) {
								$nom_systeme_environnement = Environnement::NOM_SYSTEME_CAVERNE;
								$nom_environnement = Environnement::NOM_CAVERNE;
							}
						}
					}

					if ($cadavres != null) {
						foreach($cadavres as $c) {
							if ($display_x == $c["x_monstre"] && $display_y == $c["y_monstre"]) {
								if ($c["genre_type_monstre"] == 'feminin') {
									$c_taille = $c["nom_taille_f_monstre"];
								} else {
									$c_taille = $c["nom_taille_m_monstre"];
								}
								if ($c["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
									$estGibier = true;
								} else {
									$estGibier = false;
								}
								$tabCadavres[] = array("id_monstre" => $c["id_monstre"], "nom_monstre" => $c["nom_type_monstre"], 'taille_monstre' => $c_taille, 'est_gibier' => $estGibier);
							}
						}
					}

					if ($charrettes != null) {
						foreach($charrettes as $c) {
							if ($display_x == $c["x_charrette"] && $display_y == $c["y_charrette"]) {
								$tabCharrettes[] = array("id_charrette" => $c["id_charrette"], "nom" => $c["nom_type_materiel"]);
							}
						}
					}

					if ($echoppes != null) {
						foreach($echoppes as $e) {
							if ($display_x == $e["x_echoppe"] && $display_y == $e["y_echoppe"]) {
								if ($e["sexe_braldun"] == 'feminin') {
									$nom_metier = $e["nom_feminin_metier"];
								} else {
									$nom_metier = $e["nom_masculin_metier"];
								}
								$tabEchoppes[] = array("id_echoppe" => $e["id_echoppe"], "nom_echoppe" => $e["nom_echoppe"], "nom_systeme_metier"=> $e["nom_systeme_metier"], "nom_metier" => $nom_metier, "nom_braldun" => $e["nom_braldun"], "prenom_braldun" => $e["prenom_braldun"], "id_braldun" => $e["id_braldun"]);
							}
						}
					}

					if ($champs != null) {
						foreach($champs as $e) {
							if ($display_x == $e["x_champ"] && $display_y == $e["y_champ"]) {
								$tabChamps[] = array("id_champ" => $e["id_champ"], "nom_champ" => $e["nom_champ"], "nom_braldun" => $e["nom_braldun"], "prenom_braldun" => $e["prenom_braldun"], "id_braldun" => $e["id_braldun"]);
							}
						}
					}

					if ($crevasses != null) {
						foreach($crevasses as $c) {
							if ($display_x == $c["x_crevasse"] && $display_y == $c["y_crevasse"]) {
								$tabCrevasses[] = array("id_crevasse" => $c["id_crevasse"]);
							}
						}
					}

					if ($elements != null) {
						foreach($elements as $e) {
							if ($display_x == $e["x_element"] && $display_y == $e["y_element"]) {
								if ($e["quantite_peau_element"] > 0) $tabElements[] = array("nom" => "Peau", "s" => "x", "nb" => $e["quantite_peau_element"]);
								if ($e["quantite_cuir_element"] > 0) $tabElements[] = array("nom" => "Cuir", "s" => "s", "nb" => $e["quantite_cuir_element"]);
								if ($e["quantite_fourrure_element"] > 0) $tabElements[] = array("nom" => "Fourrure", "s" => "s", "nb" => $e["quantite_fourrure_element"]);
								if ($e["quantite_planche_element"] > 0) $tabElements[] = array("nom" => "Planche", "s" => "s", "nb" => $e["quantite_planche_element"]);
								if ($e["quantite_rondin_element"] > 0) $tabElements[] = array("nom" => "Rondin", "s" => "s", "nb" => $e["quantite_rondin_element"]);
								//if ($e["quantite_castar_element"] > 0) $tabElements[] = array("nom" => "Castar", "s" => "s", "nb" => $e["quantite_castar_element"]);
								if ($e["quantite_castar_element"] > 0) $tabCastars[] = array("nb_castar" =>  $e["quantite_castar_element"], "butin" => $e["id_fk_butin_element"]);
							}
						}
					}

					if ($elementsEquipements != null) {
						foreach($elementsEquipements as $e) {
							if ($display_x == $e["x_element_equipement"] && $display_y == $e["y_element_equipement"]) {
								$tabElementsEquipements[] = array("id_equipement" => $e["id_element_equipement"],
									"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
									"qualite" => $e["nom_type_qualite"],
									"niveau" => $e["niveau_recette_equipement"],
									"suffixe" => $e["suffixe_mot_runique"]);
							}
						}
					}

					if ($elementsMateriels != null) {
						foreach($elementsMateriels as $e) {
							if ($display_x == $e["x_element_materiel"] && $display_y == $e["y_element_materiel"]) {
								$tabElementsMateriels[] = array("id_materiel" => $e["id_element_materiel"], 'nom' => $e["nom_type_materiel"]);
							}
						}
					}

					if ($elementsMunitions != null) {
						foreach($elementsMunitions as $m) {
							if ($m["quantite_element_munition"] > 0) {
								if ($display_x == $m["x_element_munition"] && $display_y == $m["y_element_munition"]) {
									$tabElementsMunitions[] = array(
										"type" => $m["nom_type_munition"],
										"pluriel" => $m["nom_pluriel_type_munition"],
										"quantite" => $m["quantite_element_munition"],
									);
								}
							}
						}
					}

					if ($elementsPotions != null) {
						foreach($elementsPotions as $p) {
							if ($display_x == $p["x_element_potion"] && $display_y == $p["y_element_potion"]) {
								$tabElementsPotions[] = array("id_element_potion" => $p["id_element_potion"],
									"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),	
									"nom" => $p["nom_type_potion"],
									"qualite" => $p["nom_type_qualite"],
									"niveau" => $p["niveau_potion"],
								);
							}
						}
					}

					if ($elementsAliments != null) {
						foreach($elementsAliments as $p) {
							if ($display_x == $p["x_element_aliment"] && $display_y == $p["y_element_aliment"]) {
								$tabElementsAliments[] = array("id_element_aliment" => $p["id_element_aliment"],
									"nom" => $p["nom_type_aliment"],
									"qualite" => $p["nom_type_qualite"],
								);
							}
						}
					}

					if ($elementsGraines != null) {
						foreach($elementsGraines as $p) {
							if ($display_x == $p["x_element_graine"] && $display_y == $p["y_element_graine"]) {
								$tabElementsGraines[] = array("quantite" => $p["quantite_element_graine"],
									"type" => $p["nom_type_graine"],
								);
							}
						}
					}

					if ($elementsIngredients != null) {
						foreach($elementsIngredients as $p) {
							if ($display_x == $p["x_element_ingredient"] && $display_y == $p["y_element_ingredient"]) {
								$tabElementsIngredients[] = array("quantite" => $p["quantite_element_ingredient"],
									"type" => $p["nom_type_ingredient"],
								);
							}
						}
					}

					if ($elementsMinerais != null) {
						foreach($elementsMinerais as $m) {
							if ($m["quantite_brut_element_minerai"] > 0) {
								if ($display_x == $m["x_element_minerai"] && $display_y == $m["y_element_minerai"]) {
									$tabElementsMineraisBruts[] = array(
										"type" => $m["nom_type_minerai"],
										"quantite" => $m["quantite_brut_element_minerai"],
									);
								}
							}

							if ($m["quantite_lingots_element_minerai"] > 0) {
								if ($display_x == $m["x_element_minerai"] && $display_y == $m["y_element_minerai"]) {
									$tabElementsLingots[] = array(
										"type" => $m["nom_type_minerai"],
										"quantite" => $m["quantite_lingots_element_minerai"],
									);
								}
							}
						}
					}

					if ($elementsPartieplantes != null) {
						foreach($elementsPartieplantes as $m) {
							if ($m["quantite_element_partieplante"] > 0) {
								if ($display_x == $m["x_element_partieplante"] && $display_y == $m["y_element_partieplante"]) {
									$tabElementsPartieplantesBrutes[] = array(
										"type" => $m["nom_type_partieplante"],
										"type_plante" => $m["nom_type_plante"],
										"quantite" => $m["quantite_element_partieplante"],
									);
								}
							}

							if ($m["quantite_preparee_element_partieplante"] > 0) {
								if ($display_x == $m["x_element_partieplante"] && $display_y == $m["y_element_partieplante"]) {
									$tabElementsPartieplantesPreparees[] = array(
										"type" => $m["nom_type_partieplante"],
										"type_plante" => $m["nom_type_plante"],
										"quantite" => $m["quantite_preparee_element_partieplante"],
									);
								}
							}
						}
					}

					if ($elementsRunes != null) {
						foreach($elementsRunes as $r) {
							if ($display_x == $r["x_element_rune"] && $display_y == $r["y_element_rune"]) {
								$tabElementsRunes[] = array("id_rune_element_rune" => $r["id_rune_element_rune"], 'id_butin' => $r["id_fk_butin_element_rune"]);
							}
						}
					}

					if ($elementsTabac != null) {
						foreach($elementsTabac as $m) {
							if ($m["quantite_feuille_element_tabac"] > 0) {
								if ($display_x == $m["x_element_tabac"] && $display_y == $m["y_element_tabac"]) {
									$tabElementsTabac[] = array(
										"type" => $m["nom_court_type_tabac"],
										"quantite" => $m["quantite_feuille_element_tabac"],
									);
								}
							}
						}
					}

					if ($bralduns != null) {
						foreach($bralduns as $h) {
							if ($display_x == $h["x_braldun"] && $display_y == $h["y_braldun"]) {
								if ($h["est_ko_braldun"] == "oui") {
									$tabBraldunsKo[] = array("id_braldun" => $h["id_braldun"], "nom_braldun" => $h["nom_braldun"], "prenom_braldun" => $h["prenom_braldun"], "niveau_braldun" => $h["niveau_braldun"], "id_communaute" => $h["id_fk_communaute_braldun"], "nom_communaute" => $h["nom_communaute"], "sexe_braldun" => $h["sexe_braldun"], "est_soule_braldun" => $h["est_soule_braldun"], "soule_camp_braldun" => $h["soule_camp_braldun"], "est_intangible_braldun" => $h["est_intangible_braldun"]);
								} else {
									$tabBralduns[] = array("id_braldun" => $h["id_braldun"], "nom_braldun" => $h["nom_braldun"], "prenom_braldun" => $h["prenom_braldun"], "niveau_braldun" => $h["niveau_braldun"], "id_communaute" => $h["id_fk_communaute_braldun"], "nom_communaute" => $h["nom_communaute"], "sexe_braldun" => $h["sexe_braldun"], "est_soule_braldun" => $h["est_soule_braldun"], "soule_camp_braldun" => $h["soule_camp_braldun"], "est_intangible_braldun" => $h["est_intangible_braldun"]);
								}
							}
						}
					}

					if ($lieux != null) {
						foreach($lieux as $l) {
							if ($display_x == $l["x_lieu"] && $display_y == $l["y_lieu"]) {
								$tabLieux[] = array("id_lieu" => $l["id_lieu"], "nom_lieu" => $l["nom_lieu"], "nom_type_lieu" => $l["nom_type_lieu"], "nom_systeme_type_lieu" => $l["nom_systeme_type_lieu"]);
								$lieuCourant = $l;
								$estLimiteVille = false;
							}
						}
					}

					if ($monstres != null) {
						foreach($monstres as $m) {
							if ($display_x == $m["x_monstre"] && $display_y == $m["y_monstre"]) {
								if ($m["genre_type_monstre"] == 'feminin') {
									$m_taille = $m["nom_taille_f_monstre"];
								} else {
									$m_taille = $m["nom_taille_m_monstre"];
								}
								if ($m["id_fk_type_groupe_monstre"] == $this->view->config->game->groupe_monstre->type->gibier) {
									$estGibier = true;
								} else {
									$estGibier = false;
								}
								$tabMonstres[] = array("id_monstre" => $m["id_monstre"], "id_type_monstre" => $m["id_type_monstre"], "nom_monstre" => $m["nom_type_monstre"], 'taille_monstre' => $m_taille, 'niveau_monstre' => $m["niveau_monstre"], "est_gibier" => $estGibier);
							}
						}
					}

					if ($nids != null) {
						foreach($nids as $n) {
							if ($display_x == $n["x_nid"] && $display_y == $n["y_nid"]) {
								$tabNids[] = array("id_nid" => $n["id_nid"], "nom_nid" => $n["nom_nid_type_monstre"]);
							}
						}
					}

					if ($villes != null) {
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
					}

					if ($regions != null) {
						foreach($regions as $r) {
							if ($display_x >= $r["x_min_region"] &&
							$display_x <= $r["x_max_region"] &&
							$display_y >= $r["y_min_region"] &&
							$display_y <= $r["y_max_region"]) {
								$region = array("nom" => $r["nom_region"], "description" => $r["description_region"], "est_pvp_region" => $r["est_pvp_region"]);
								break;
							}
						}
					}

					if ($palissades != null) {
						foreach($palissades as $p) {
							if ($display_x == $p["x_palissade"] && $display_y == $p["y_palissade"]) {
								$tabPalissades[] = array("id_palissade" => $p["id_palissade"], "est_destructible_palissade" => $p["est_destructible_palissade"]);
							}
						}
					}

					if ($buissons != null) {
						foreach($buissons as $b) {
							if ($display_x == $b["x_buisson"] && $display_y == $b["y_buisson"]) {
								$tabBuissons[] = array("id_buisson" => $b["id_buisson"], "nom_buisson" => $b["nom_type_buisson"]);
							}
						}
					}

					if ($bosquets != null) {
						foreach($bosquets as $b) {
							if ($display_x == $b["x_bosquet"] && $display_y == $b["y_bosquet"]) {
								$tabBosquets[] = array("id_bosquet" => $b["id_bosquet"]);
								$nom_systeme_environnement = $b["nom_systeme_type_bosquet"];
								$nom_environnement = $b["description_type_bosquet"];
							}
						}
					}

					if ($eaux != null) {
						foreach($eaux as $e) {
							if ($display_x == $e["x_eau"] && $display_y == $e["y_eau"]) {
								$tabEaux[] = array("id_eau" => $e["id_eau"]);
								$nom_environnement = $e["type_eau"];
							}
						}
					}

					if ($routes != null) {
						foreach($routes as $r) {
							if ($display_x == $r["x_route"] && $display_y == $r["y_route"]) {
								$tabRoutes[] = array("id_route" => $r["id_route"], "type_route" => $r["type_route"]);
							}
						}
					}

					if ($souleMatch != null) {
						foreach($souleMatch as $s) {
							if ($display_x == $s["x_ballon_soule_match"] && $display_y == $s["y_ballon_soule_match"]) {
								$tabBallons[] = array("est_ballon_present" => true);
							}
						}
					}

				}

				if ($this->view->user->x_braldun == $display_x && $this->view->user->y_braldun == $display_y) { // Position du joueur
					$cssActuelle = "actuelle";
					$this->view->environnement = $nom_environnement;
					$this->view->centre_nom_region = $region["nom"];
					$this->view->est_pvp_region = ($region["est_pvp_region"] == 'oui');
					$this->view->centre_description_region = $region["description"];
					$this->view->centre_nom_ville = $ville["nom_ville"];
					$this->view->centre_est_capitale = ($ville["est_capitale"] == "oui");
				} else {
					$cssActuelle = "";
				}
					
				if (count($tabPalissades) > 0) {
					$css = "palissade";
				} else  {
					$css = $nom_systeme_environnement;
					if ($css == null) {
						$css = "inconnu";
					}

					if (count($tabEaux) >= 1) {
						$css = $nom_environnement;
					} elseif (count($tabRoutes) >= 1) {
						if ($tabRoutes[0]["type_route"] == "ville") {
							$css = "pave";
						} elseif ($tabRoutes[0]["type_route"] == "echoppe") {
							$css = "pave";
						} elseif ($tabRoutes[0]["type_route"] == "route") {
							$css = "route";
						} else {
							$css .= "-gr";
						}
					}
					if (count($tabCrevasses) >= 1) {
						$css .= "-crevasse";
					}
				}

				if ($this->view->centre_x == $display_x && $this->view->centre_y == $display_y) {
					$this->view->centre_environnement = $nom_environnement;
				}

				if ($this->view->user->x_braldun == $display_x && $this->view->user->y_braldun == $display_y) {
					$tabMarcher["case"] = null;
				} else if ($marcher != null && $marcher["tableauValidationXY"] != null && array_key_exists($display_x, $marcher["tableauValidationXY"]) && array_key_exists($display_y, $marcher["tableauValidationXY"][$display_x])) {
					$tabMarcher["case"] = $marcher["tableauValidationXY"][$display_x][$display_y];
					$tabMarcher["general"] = $marcher;
				} else {
					$tabMarcher["case"] = null;
				}

				$tab = array ("x" => $display_x, "y" => $display_y, "z" => $this->view->z_position, //
					"change_level" => $change_level, // nouvelle ligne dans le tableau ;
					"css_actuelle" => $cssActuelle,
					"nom_zone" => $nom_zone,
					"description_zone" => $nom_zone,
					"css" => $css,
					"n_cadavres" => count($tabCadavres),
					"cadavres" => $tabCadavres,
					"n_champs" => count($tabChamps),
					"champs" => $tabChamps,
					"n_crevasses" => count($tabCrevasses),
					"crevasses" => $tabCrevasses,
					"n_echoppes" => count($tabEchoppes),
					"echoppes" => $tabEchoppes,
					"n_castars" => count($tabCastars),
					"castars" => $tabCastars,
					"n_charrettes" => count($tabCharrettes),
					"charrettes" => $tabCharrettes,
					"n_elements" => count($tabElements),
					"elements" => $tabElements,
					"n_elements_equipements" => count($tabElementsEquipements),
					"elements_equipements" => $tabElementsEquipements,
					"n_elements_materiels" => count($tabElementsMateriels),
					"elements_materiels" => $tabElementsMateriels,
					"n_elements_munitions" => count($tabElementsMunitions),
					"elements_munitions" => $tabElementsMunitions,
					"n_elements_partieplante_brutes" => count($tabElementsPartieplantesBrutes),
					"elements_partieplantes_brutes" => $tabElementsPartieplantesBrutes,
					"n_elements_partieplante_preparees" => count($tabElementsPartieplantesPreparees),
					"elements_partieplantes_preparees" => $tabElementsPartieplantesPreparees,
					"n_elements_potions" => count($tabElementsPotions),
					"elements_potions" => $tabElementsPotions,
					"n_elements_graines" => count($tabElementsGraines),
					"elements_graines" => $tabElementsGraines,
					"n_elements_ingredients" => count($tabElementsIngredients),
					"elements_ingredients" => $tabElementsIngredients,
					"n_elements_aliments" => count($tabElementsAliments),
					"elements_aliments" => $tabElementsAliments,
					"n_elements_minerais_bruts" => count($tabElementsMineraisBruts),
					"elements_minerais_bruts" => $tabElementsMineraisBruts,
					"n_elements_lingots" => count($tabElementsLingots),
					"elements_lingots" => $tabElementsLingots,
					"n_elements_runes" => count($tabElementsRunes),
					"elements_runes" => $tabElementsRunes,
					"n_elements_tabac" => count($tabElementsTabac),
					"elements_tabc" => $tabElementsTabac,
					"n_bralduns" => count($tabBralduns),
					"bralduns" => $tabBralduns,
					"n_braldunsKo" => count($tabBraldunsKo),
					"braldunsKo" => $tabBraldunsKo,
					"n_lieux" => count($tabLieux),
					"lieux" => $tabLieux,
					"n_monstres" => count($tabMonstres),
					"monstres" => $tabMonstres,
					"n_nids" => count($tabNids),
					"nids" => $tabNids,
					"n_palissades" => count($tabPalissades),
					"palissades" => $tabPalissades,
					"n_buissons" => count($tabBuissons),
					"buissons" => $tabBuissons,
					"n_bosquets" => count($tabBosquets),
					"bosquets" => $tabBosquets,
					"n_eaux" => count($tabEaux),
					"eaux" => $tabEaux,
					"n_routes" => count($tabRoutes),
					"routes" => $tabRoutes,
					"n_ballons" => count($tabBallons),
					"ballons" => $tabBallons,
					"ville" => $ville,
					"marcher" => $tabMarcher,
				);
				$tableau[] = $tab;
				if ($change_level) {
					$change_level = false;
				}
			}
		}

		unset($cadavres);
		unset($castars);
		unset($charrettes);
		unset($echoppes);
		unset($elements);
		unset($elementsEquipements);
		unset($elementsMateriels);
		unset($elementsPartieplantes);
		unset($elementsMinerais);
		unset($elementsPotions);
		unset($elementsRunes);
		unset($bralduns);
		unset($lieux);
		unset($monstres);
		unset($palissades);
		unset($regions);
		unset($routes);
		unset($villes);
		unset($zones);

		$this->view->tableau = $tableau;
		unset($tableau);
	}
}
