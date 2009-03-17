<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
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
			Zend_Loader::loadClass("Castar");
			Zend_Loader::loadClass("Charrette");
			Zend_Loader::loadClass("Echoppe");
			Zend_Loader::loadClass("Element");
			Zend_Loader::loadClass("ElementEquipement");
			Zend_Loader::loadClass("ElementMunition");
			Zend_Loader::loadClass("ElementPartieplante");
			Zend_Loader::loadClass("ElementMinerai");
			Zend_Loader::loadClass("ElementPotion");
			Zend_Loader::loadClass("ElementRune");
			Zend_Loader::loadClass("Lieu");
			Zend_Loader::loadClass("HobbitsMetiers");
			Zend_Loader::loadClass("Monstre");
			Zend_Loader::loadClass("Palissade");
			Zend_Loader::loadClass("Region");
			Zend_Loader::loadClass("TypeLieu");
			Zend_Loader::loadClass("Route");
			Zend_Loader::loadClass("SouleMatch");
			Zend_Loader::loadClass("Ville");
			Zend_Loader::loadClass("Zone");
			Zend_Loader::loadClass('Bral_Util_Marcher');
			Zend_Loader::loadClass("Bral_Util_Equipement");

			$this->prepare();
			$this->deplacement();
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/vue.phtml");
	}

	private function prepare() {
		$this->view->vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) + $this->view->user->vue_bm_hobbit;
		$this->view->x_min = $this->view->user->x_hobbit - $this->view->vue_nb_cases;
		$this->view->x_max = $this->view->user->x_hobbit + $this->view->vue_nb_cases;
		$this->view->y_min = $this->view->user->y_hobbit - $this->view->vue_nb_cases;
		$this->view->y_max = $this->view->user->y_hobbit + $this->view->vue_nb_cases;

		$this->view->estVueEtendue = false;

		if (($this->_request->get("caction") == "box_vue") && ($this->_request->get("valeur_1") != "")) { // si le joueur a clique sur une icone
			$this->deplacement = $this->_request->get("valeur_1");
			$this->view->centre_x = $this->get_deplacement_verif($this->view->x_min, $this->view->x_max, $this->_request->get("valeur_2"), 0);
			$this->view->centre_y = $this->get_deplacement_verif($this->view->y_min, $this->view->y_max, $this->_request->get("valeur_3"), 0);
		} else if ($this->_request->get("caction") == "voir") {
			$this->view->estVueEtendue = true;
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
			
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		unset($hobbitsMetiersTable);
		$tabMetiers = null;
		if ($hobbitsMetierRowset != null) {
			foreach($hobbitsMetierRowset as $m) {
				$possedeMetier = true;
				$tabMetiers[] = $m["nom_systeme_metier"];
			}
			unset($hobbitsMetierRowset);
		}

		$monstreTable = new Monstre();
		$cadavres = $monstreTable->selectVueCadavre($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($monstreTable);
		$castarTable = new Castar();
		$castars = $castarTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($castarTable);
		$charretteTable = new Charrette();
		$charrettes = $charretteTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($charretteTable);
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($echoppeTable);
		$elementTable = new Element();
		$elements = $elementTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($elementTable);
		$elementEquipementTable = new ElementEquipement();
		$elementsEquipements = $elementEquipementTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($elementEquipementTable);
		$elementMunitionsTable = new ElementMunition();
		$elementsMunitions = $elementMunitionsTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($elementMunitionsTable);
		$elementPartiePlanteTable = new ElementPartieplante();
		$elementsPartieplantes = $elementPartiePlanteTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($elementPartiePlanteTable);
		$elementMineraisTable = new ElementMinerai();
		$elementsMinerais = $elementMineraisTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($elementMineraisTable);
		$elementPotionTable = new ElementPotion();
		$elementsPotions = $elementPotionTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($elementPotionTable);
		$elementRuneTable = new ElementRune();
		$elementsRunes = $elementRuneTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($elementRuneTable);
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($hobbitTable);
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($lieuxTable);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($monstreTable);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($palissadeTable);
		$regionTable = new Region();
		$regions = $regionTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($regionTable);
		$routeTable = new Route();
		$routes = $routeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($routeTable);
		$souleMatchTable = new SouleMatch();
		$souleMatch = $souleMatchTable->selectBallonVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($souleMatchTable);
		$villeTable = new Ville();
		$villes = $villeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
		unset($villeTable);
		$zoneTable = new Zone();
		$zones = $zoneTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max);
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
		if ($this->view->estVueEtendue === false) {
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
				$tabEchoppes = null;
				$tabElements = null;
				$tabElementsEquipements = null;
				$tabElementsMunitions = null;
				$tabElementsMineraisBruts = null;
				$tabElementsLingots = null;
				$tabElementsPartieplantesBrutes = null;
				$tabElementsPartieplantesPreparees = null;
				$tabElementsPotions = null;
				$tabElementsRunes = null;
				$tabHobbits = null;
				$tabLieux = null;
				$tabMonstres = null;
				$tabPalissades = null;
				$tabRoutes = null;
				$tabBallons = null;
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
							$nom_environnement = htmlspecialchars($z["nom_environnement"]);
							break;
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
								$tabCadavres[] = array("id_monstre" => $c["id_monstre"], "nom_monstre" => $c["nom_type_monstre"], 'taille_monstre' => $c_taille);
							}
						}
					}

					if ($castars != null) {
						foreach($castars as $c) {
							if ($display_x == $c["x_castar"] && $display_y == $c["y_castar"]) {
								$tabCastars[] = array("nb_castar" => $c["nb_castar"]);
							}
						}
					}

					if ($charrettes != null) {
						foreach($charrettes as $c) {
							if ($display_x == $c["x_charrette"] && $display_y == $c["y_charrette"]) {
								$tabCharrettes[] = array("id_charrette" => $c["id_charrette"]);
							}
						}
					}

					if ($echoppes != null) {
						foreach($echoppes as $e) {
							if ($display_x == $e["x_echoppe"] && $display_y == $e["y_echoppe"]) {
								if ($e["sexe_hobbit"] == 'feminin') {
									$nom_metier = $e["nom_feminin_metier"];
								} else {
									$nom_metier = $e["nom_masculin_metier"];
								}
								$tabEchoppes[] = array("id_echoppe" => $e["id_echoppe"], "nom_echoppe" => $e["nom_echoppe"], "nom_systeme_metier"=> $e["nom_systeme_metier"], "nom_metier" => $nom_metier, "nom_hobbit" => $e["nom_hobbit"], "prenom_hobbit" => $e["prenom_hobbit"], "id_hobbit" => $e["id_hobbit"]);
							}
						}
					}

					if ($elements != null) {
						foreach($elements as $e) {
							if ($display_x == $e["x_element"] && $display_y == $e["y_element"]) {
								if ($e["quantite_peau_element"] > 0) $tabElements[] = array("nom" => "Peau", "s" => "x", "nb" => $e["quantite_peau_element"]);
								if ($e["quantite_viande_element"] > 0) $tabElements[] = array("nom" => "Viande", "s" => "s", "nb" => $e["quantite_viande_element"]);
								if ($e["quantite_viande_preparee_element"] > 0) $tabElements[] = array("nom" =>  "Viande(s) pr&eacute;par&eacute;e", "s" => "s", "nb" => $e["quantite_viande_preparee_element"]);
								if ($e["quantite_ration_element"] > 0) $tabElements[] = array("nom" => "Ration", "s" => "s", "nb" => $e["quantite_ration_element"]);
								if ($e["quantite_cuir_element"] > 0) $tabElements[] = array("nom" => "Cuir", "s" => "s", "nb" => $e["quantite_cuir_element"]);
								if ($e["quantite_fourrure_element"] > 0) $tabElements[] = array("nom" => "Fourrure", "s" => "s", "nb" => $e["quantite_fourrure_element"]);
								if ($e["quantite_planche_element"] > 0) $tabElements[] = array("nom" => "Planche", "s" => "s", "nb" => $e["quantite_planche_element"]);
							}
						}
					}

					if ($elementsEquipements != null) {
						foreach($elementsEquipements as $e) {
							if ($display_x == $e["x_element_equipement"] && $display_y == $e["y_element_equipement"]) {
								$tabElementsEquipements[] = array("id_equipement" => $e["id_element_equipement"],
									"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_element_equipement"]),
									"qualite" => $e["nom_type_qualite"],
									"niveau" => $e["niveau_recette_equipement"],
									"suffixe" => $e["suffixe_mot_runique"]);
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
									"nom" => $p["nom_type_potion"],
									"qualite" => $p["nom_type_qualite"],
									"niveau" => $p["niveau_element_potion"],
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
								$tabElementsRunes[] = array("id_element_rune" => $r["id_element_rune"]);
							}
						}
					}

					if ($hobbits != null) {
						foreach($hobbits as $h) {
							if ($display_x == $h["x_hobbit"] && $display_y == $h["y_hobbit"]) {
								$tabHobbits[] = array("id_hobbit" => $h["id_hobbit"], "nom_hobbit" => $h["nom_hobbit"], "prenom_hobbit" => $h["prenom_hobbit"], "niveau_hobbit" => $h["niveau_hobbit"], "id_communaute" => $h["id_fk_communaute_hobbit"], "nom_communaute" => $h["nom_communaute"], "sexe_hobbit" => $h["sexe_hobbit"]);
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
								$tabMonstres[] = array("id_monstre" => $m["id_monstre"], "nom_monstre" => $m["nom_type_monstre"], 'taille_monstre' => $m_taille, 'niveau_monstre' => $m["niveau_monstre"]);
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

					if ($routes != null) {
						foreach($routes as $r) {
							if ($display_x == $r["x_route"] && $display_y == $r["y_route"]) {
								$tabRoutes[] = array("id_route" => $r["id_route"], "est_route" => $r["est_route"]);
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

				if ($this->view->user->x_hobbit == $display_x && $this->view->user->y_hobbit == $display_y) { // Position du joueur
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
					
				if (count($tabRoutes) == 1 && $tabRoutes[0]["est_route"] == "oui") {
					$css = "route";
				} else if (count($tabRoutes) == 1 && $tabRoutes[0]["est_route"] == "non") {
					$css = "terrasse";
				} else if (count($tabPalissades) > 0) {
					$css = "palissade";
				} else {
					$css = $nom_systeme_environnement;
				}

				if ($this->view->centre_x == $display_x && $this->view->centre_y == $display_y) {
					$this->view->centre_environnement = $nom_environnement;
				}

				if ($marcher != null && $marcher["tableauValidationXY"] != null && array_key_exists($display_x, $marcher["tableauValidationXY"]) && array_key_exists($display_y, $marcher["tableauValidationXY"][$display_x])) {
					$tabMarcher = $marcher["tableauValidationXY"][$display_x][$display_y];
				} else {
					$tabMarcher = null;
				}

				$tab = array ("x" => $display_x, "y" => $display_y, //
					"change_level" => $change_level, // nouvelle ligne dans le tableau ;
					"css_actuelle" => $cssActuelle,
					"nom_zone" => $nom_zone,
					"description_zone" => $nom_zone,
					"css" => $css,
					"n_cadavres" => count($tabCadavres),
					"cadavres" => $tabCadavres,
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
					"n_elements_munitions" => count($tabElementsMunitions),
					"elements_munitions" => $tabElementsMunitions,
					"n_elements_partieplante_brutes" => count($tabElementsPartieplantesBrutes),
					"elements_partieplantes_brutes" => $tabElementsPartieplantesBrutes,
					"n_elements_partieplante_preparees" => count($tabElementsPartieplantesPreparees),
					"elements_partieplantes_preparees" => $tabElementsPartieplantesPreparees,
					"n_elements_potions" => count($tabElementsPotions),
					"elements_potions" => $tabElementsPotions,
					"n_elements_minerais_bruts" => count($tabElementsMineraisBruts),
					"elements_minerais_bruts" => $tabElementsMineraisBruts,
					"n_elements_lingots" => count($tabElementsLingots),
					"elements_lingots" => $tabElementsLingots,
					"n_elements_runes" => count($tabElementsRunes),
					"elements_runes" => $tabElementsRunes,
					"n_hobbits" => count($tabHobbits),
					"hobbits" => $tabHobbits,
					"n_lieux" => count($tabLieux),
					"lieux" => $tabLieux,
					"n_monstres" => count($tabMonstres),
					"monstres" => $tabMonstres,
					"n_palissades" => count($tabPalissades),
					"palissades" => $tabPalissades,
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
		unset($elementsPartieplantes);
		unset($elementsMinerais);
		unset($elementsPotions);
		unset($elementsRunes);
		unset($hobbits);
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
