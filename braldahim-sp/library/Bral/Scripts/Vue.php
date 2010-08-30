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
class Bral_Scripts_Vue extends Bral_Scripts_Script {

	public function getType() {
		return self::TYPE_DYNAMIQUE;
	}

	public function getEtatService() {
		return self::SERVICE_ACTIVE;
	}

	public function getVersion() {
		return 1;
	}

	public function calculScriptImpl() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Vue - calculScriptImpl - enter -");

		$retour = null;
		$retour .= $this->calculVue();

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Vue - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculVue() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Vue - calculVue - enter -");
		$retour = "";
		$this->calculVueBraldun($retour);
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Vue - calculVue - exit -");
		return $retour;
	}

	private function calculVueBraldun(&$retour) {

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
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("BraldunsMetiers");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Nid");
		Zend_Loader::loadClass("Palissade");
		Zend_Loader::loadClass("Buisson");
		Zend_Loader::loadClass("Bosquet");
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Route");
		Zend_Loader::loadClass("Eau");
		Zend_Loader::loadClass("SouleMatch");
		Zend_Loader::loadClass("Zone");
		Zend_Loader::loadClass("Bral_Util_Equipement");
		Zend_Loader::loadClass("Bral_Util_Potion");
			
		$x = $this->braldun->x_braldun;
		$y = $this->braldun->y_braldun;
		$z_position = $this->braldun->z_braldun;
		$bm = $this->braldun->vue_bm_braldun;
			
		Zend_Loader::loadClass("Bral_Util_Commun");
		$vue_nb_cases = Bral_Util_Commun::getVueBase($x, $y, $z_position) + $bm;
		$x_min = $x - $vue_nb_cases;
		$x_max = $x + $vue_nb_cases;
		$y_min = $y - $vue_nb_cases;
		$y_max = $y + $vue_nb_cases;

		$pos = $x.';'.$y.';'.$z_position.';'.$x_min.';'.$x_max.';'.$y_min.';'.$y_max;
		$fin = PHP_EOL;
		$retour .= 'POSITION;'.$pos.';'. $this->braldun->id_braldun.';'.$vue_nb_cases.';'.$bm.$fin;

		$monstreTable = new Monstre();
		$cadavres = $monstreTable->selectVueCadavre($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($monstreTable);
		$charretteTable = new Charrette();
		$charrettes = $charretteTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($charretteTable);
		$champTable = new Champ();
		$champs = $champTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($champTable);
		$crevasseTable = new Crevasse();
		$crevasses = $crevasseTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position, 'oui');
		unset($crevasseTable);
		$eauTable = new Eau();
		$eaux = $eauTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($eauTable);
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($echoppeTable);
		$elementTable = new Element();
		$elements = $elementTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementTable);
		$elementEquipementTable = new ElementEquipement();
		$elementsEquipements = $elementEquipementTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementEquipementTable);
		$elementMunitionsTable = new ElementMunition();
		$elementsMunitions = $elementMunitionsTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementMunitionsTable);
		$elementPartiePlanteTable = new ElementPartieplante();
		$elementsPartieplantes = $elementPartiePlanteTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementPartiePlanteTable);
		$elementMaterielTable = new ElementMateriel();
		$elementsMateriels = $elementMaterielTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementMaterielTable);
		$elementMineraisTable = new ElementMinerai();
		$elementsMinerais = $elementMineraisTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementMineraisTable);
		$elementPotionTable = new ElementPotion();
		$elementsPotions = $elementPotionTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementPotionTable);
		$elementAlimentTable = new ElementAliment();
		$elementsAliments = $elementAlimentTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementAlimentTable);
		$elementGraineTable = new ElementGraine();
		$elementsGraines = $elementGraineTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementGraineTable);
		$elementIngredientTable = new ElementIngredient();
		$elementsIngredients = $elementIngredientTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementIngredientTable);
		$elementRuneTable = new ElementRune();
		$elementsRunes = $elementRuneTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementRuneTable);
		$elementTabacTable = new ElementTabac();
		$elementsTabac = $elementTabacTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($elementTabacTable);
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($braldunTable);
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($lieuxTable);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($monstreTable);
		$nidTable = new Nid();
		$nids = $nidTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($nidTable);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($palissadeTable);
		$buissonTable = new Buisson();
		$buissons = $buissonTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($bosquetTable);
		$bosquetTable = new Bosquet();
		$bosquets = $bosquetTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($bosquetTable);
		$routeTable = new Route();
		$routes = $routeTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($routeTable);
		$souleMatchTable = new SouleMatch();
		$souleMatch = $souleMatchTable->selectBallonVue($x_min, $y_min, $x_max, $y_max);
		unset($souleMatchTable);
		$zoneTable = new Zone();
		$zones = $zoneTable->selectVue($x_min, $y_min, $x_max, $y_max, $z_position);
		unset($zoneTable);

		$centre_x_min = $x_min;
		$centre_x_max = $x_max;
		$centre_y_min = $y_min;
		$centre_y_max = $y_max;

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
				$tabLieux = null;
				$tabMonstres = null;
				$tabNids = null;
				$tabPalissades = null;
				$tabBuissons = null;
				$tabBosquets = null;
				$tabRoutes = null;
				$tabBallons = null;
				$nom_systeme_environnement = null;
				$nom_environnement = null;

				$pos = $display_x.';'.$display_y.';'.$z_position;
				$fin = PHP_EOL;
					
				if (($j > $y_max) || ($j < $y_min) ||
				($i < $x_min) || ($i > $x_max) ||
				($j > $this->config->game->y_max) || ($j < $this->config->game->y_min) ||
				($i < $this->config->game->x_min) || ($i > $this->config->game->x_max)
				) {
					$nom_systeme_environnement = "inconnu";
				} else {
					$nom_environnement = "";
					$nom_systeme_environnement = "";
						
					if ($eaux != null) {
						foreach($eaux as $e) {
							if ($display_x == $e["x_eau"] && $display_y == $e["y_eau"]) {
								$nom_systeme_environnement = $e["type_eau"];
								$nom_environnement = "Eau";
							}
						}
					}
						
					foreach($zones as $z) {
						if ($display_x >= $z["x_min_zone"] &&
						$display_x <= $z["x_max_zone"] &&
						$display_y >= $z["y_min_zone"] &&
						$display_y <= $z["y_max_zone"]) {
							if ($nom_environnement == "") {
								$nom_systeme_environnement = $z["nom_systeme_environnement"];
								$nom_environnement = htmlspecialchars($z["nom_environnement"]);
							}
							$retour .= 'ENVIRONNEMENT;'.$pos.';'.$nom_systeme_environnement.';'.$nom_environnement.$fin;
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
								$retour .= 'CADAVRE;'.$pos.';'. $c["id_monstre"].';'.$c["nom_type_monstre"].';'.$c_taille.$fin;
							}
						}
					}

					if ($charrettes != null) {
						foreach($charrettes as $c) {
							if ($display_x == $c["x_charrette"] && $display_y == $c["y_charrette"]) {
								$tabCharrettes[] = array("id_charrette" => $c["id_charrette"], "nom" => $c["nom_type_materiel"]);
								$retour .= 'CHARRETTE;'.$pos.';'. $c["id_charrette"].';'.$c["nom_type_materiel"].$fin;
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
								$retour .= 'ECHOPPE;'.$pos.';'.$e["id_echoppe"].';'.$e["nom_echoppe"].';'.$e["nom_systeme_metier"].';'.$nom_metier.';'.$e["id_braldun"].$fin;
							}
						}
					}

					if ($champs != null) {
						foreach($champs as $e) {
							if ($display_x == $e["x_champ"] && $display_y == $e["y_champ"]) {
								$retour .= 'CHAMP;'.$pos.';'.$e["id_champ"].';'.$e["id_braldun"].$fin;
							}
						}
					}

					if ($crevasses != null) {
						foreach($crevasses as $c) {
							if ($display_x == $c["x_crevasse"] && $display_y == $c["y_crevasse"]) {
								$tabCrevasses[] = array("id_crevasse" => $c["id_crevasse"]);
								$retour .= 'CREVASSE;'.$pos.';'.$c["id_crevasse"].$fin;
							}
						}
					}

					if ($elements != null) {
						foreach($elements as $e) {
							if ($display_x == $e["x_element"] && $display_y == $e["y_element"]) {
								if ($e["quantite_peau_element"] > 0) $retour .= 'ELEMENT;'.$pos.';Peau;'.$e["quantite_peau_element"].$fin;
								if ($e["quantite_cuir_element"] > 0) $retour .= 'ELEMENT;'.$pos.';Cuir;'.$e["quantite_cuir_element"].$fin;
								if ($e["quantite_fourrure_element"] > 0) $retour .= 'ELEMENT;'.$pos.';Fourrure;'.$e["quantite_fourrure_element"].$fin;
								if ($e["quantite_planche_element"] > 0) $retour .= 'ELEMENT;'.$pos.';Planche;'.$e["quantite_planche_element"].$fin;
								if ($e["quantite_rondin_element"] > 0) $retour .= 'ELEMENT;'.$pos.';Rondin;'.$e["quantite_rondin_element"].$fin;
								if ($e["quantite_castar_element"] > 0) $retour .= 'ELEMENT;'.$pos.';Castar;'.$e["quantite_castar_element"].$fin;
							}
						}
					}

					if ($elementsEquipements != null) {
						foreach($elementsEquipements as $e) {
							if ($display_x == $e["x_element_equipement"] && $display_y == $e["y_element_equipement"]) {
								$retour .= 'EQUIPEMENT;'.$pos.';'.$e["id_element_equipement"].';'.Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]).';'.$e["nom_type_qualite"].';'.$e["niveau_recette_equipement"].';'.$e["suffixe_mot_runique"].$fin;
							}
						}
					}

					if ($elementsMateriels != null) {
						foreach($elementsMateriels as $e) {
							if ($display_x == $e["x_element_materiel"] && $display_y == $e["y_element_materiel"]) {
								$retour .= 'MATERIEL;'.$pos.';'.$e["id_element_materiel"].';'.$e["nom_type_materiel"].$fin;
							}
						}
					}

					if ($elementsMunitions != null) {
						foreach($elementsMunitions as $m) {
							if ($m["quantite_element_munition"] > 0) {
								if ($display_x == $m["x_element_munition"] && $display_y == $m["y_element_munition"]) {
									$retour .= 'MUNITION;'.$pos.';'.$m["nom_type_munition"].';'.$m["nom_pluriel_type_munition"].';'.$m["quantite_element_munition"].$fin;
								}
							}
						}
					}

					if ($elementsPotions != null) {
						foreach($elementsPotions as $p) {
							if ($display_x == $p["x_element_potion"] && $display_y == $p["y_element_potion"]) {
								$retour .= 'POTION;'.$pos.';'.$p["id_element_potion"].';'.Bral_Util_Potion::getNomType($p["type_potion"]).';'.$p["nom_type_potion"].';'.$p["nom_type_qualite"].';'.$p["niveau_potion"].$fin;
							}
						}
					}

					if ($elementsAliments != null) {
						foreach($elementsAliments as $p) {
							if ($display_x == $p["x_element_aliment"] && $display_y == $p["y_element_aliment"]) {
								$retour .= 'ALIMENT;'.$pos.';'.$p["id_element_aliment"].';'.$p["nom_type_aliment"].';'.$p["nom_type_qualite"].$fin;
							}
						}
					}

					if ($elementsGraines != null) {
						foreach($elementsGraines as $p) {
							if ($display_x == $p["x_element_graine"] && $display_y == $p["y_element_graine"]) {
								$retour .= 'GRAINE;'.$pos.';'.$p["quantite_element_graine"].';'.$p["nom_type_graine"].$fin;
							}
						}
					}

					if ($elementsIngredients != null) {
						foreach($elementsIngredients as $p) {
							if ($display_x == $p["x_element_ingredient"] && $display_y == $p["y_element_ingredient"]) {
								$retour .= 'INGREDIENT;'.$pos.';'.$p["quantite_element_ingredient"].';'.$p["nom_type_ingredient"].$fin;
							}
						}
					}

					if ($elementsMinerais != null) {
						foreach($elementsMinerais as $m) {
							if ($m["quantite_brut_element_minerai"] > 0) {
								if ($display_x == $m["x_element_minerai"] && $display_y == $m["y_element_minerai"]) {
									$retour .= 'MINERAI_BRUT;'.$pos.';'.$m["quantite_brut_element_minerai"].';'.$m["nom_type_minerai"].$fin;
								}
							}

							if ($m["quantite_lingots_element_minerai"] > 0) {
								if ($display_x == $m["x_element_minerai"] && $display_y == $m["y_element_minerai"]) {
									$retour .= 'LINGOT;'.$pos.';'.$m["quantite_lingots_element_minerai"].';'.$m["nom_type_minerai"].$fin;
								}
							}
						}
					}

					if ($elementsPartieplantes != null) {
						foreach($elementsPartieplantes as $m) {
							if ($m["quantite_element_partieplante"] > 0) {
								if ($display_x == $m["x_element_partieplante"] && $display_y == $m["y_element_partieplante"]) {
									$retour .= 'PLANTE_BRUTE;'.$pos.';'.$m["quantite_element_partieplante"].';'.$m["nom_type_partieplante"].';'.$m["nom_type_plante"].$fin;
								}
							}

							if ($m["quantite_preparee_element_partieplante"] > 0) {
								if ($display_x == $m["x_element_partieplante"] && $display_y == $m["y_element_partieplante"]) {
									$retour .= 'PLANTE_PREPAREE;'.$pos.';'.$m["quantite_preparee_element_partieplante"].';'.$m["nom_type_partieplante"].';'.$m["nom_type_plante"].$fin;
								}
							}
						}
					}

					if ($elementsRunes != null) {
						foreach($elementsRunes as $r) {
							if ($display_x == $r["x_element_rune"] && $display_y == $r["y_element_rune"]) {
								$retour .= 'RUNE;'.$pos.';'.$r["id_rune_element_rune"].$fin;
							}
						}
					}

					if ($elementsTabac != null) {
						foreach($elementsTabac as $m) {
							if ($m["quantite_feuille_element_tabac"] > 0) {
								if ($display_x == $m["x_element_tabac"] && $display_y == $m["y_element_tabac"]) {
									$retour .= 'TABAC;'.$pos.';'.$m["quantite_feuille_element_tabac"].';'.$m["nom_court_type_tabac"].$fin;
								}
							}
						}
					}

					if ($bralduns != null) {
						foreach($bralduns as $h) {
							if ($display_x == $h["x_braldun"] && $display_y == $h["y_braldun"]) {
								$retour .= 'BRALDUN;'.$pos.';'.$h["id_braldun"].';'.$h['est_ko_braldun'].';'.$h['est_intangible_braldun'].';'.$h['est_soule_braldun'].';'.$h["soule_camp_braldun"].';'.$h["id_fk_soule_match_braldun"].$fin;
							}
						}
					}

					if ($lieux != null) {
						foreach($lieux as $l) {
							if ($display_x == $l["x_lieu"] && $display_y == $l["y_lieu"]) {
								$retour .= 'LIEU;'.$pos.';'.$l["id_lieu"].';'.$l["nom_lieu"].';'.$l["nom_type_lieu"].';'.$l["nom_systeme_type_lieu"].$fin;
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
								$retour .= 'MONSTRE;'.$pos.';'.$m["id_monstre"].';'.$m["nom_type_monstre"].';'.$m_taille.';'.$m["niveau_monstre"].$fin;
							}
						}
					}

					if ($nids != null) {
						foreach($nids as $n) {
							if ($display_x == $n["x_nid"] && $display_y == $n["y_nid"]) {
								$retour .= 'NID;'.$pos.';'.$n["id_nid"].';'.$n["nom_nid_type_monstre"].$fin;
							}
						}
					}

					if ($palissades != null) {
						foreach($palissades as $p) {
							if ($display_x == $p["x_palissade"] && $display_y == $p["y_palissade"]) {
								$retour .= 'PALISSADE;'.$pos.';'.$p["id_palissade"].';'.$p["est_destructible_palissade"].$fin;
							}
						}
					}

					if ($buissons != null) {
						foreach($buissons as $b) {
							if ($display_x == $b["x_buisson"] && $display_y == $b["y_buisson"]) {
								$retour .= 'BUISSON;'.$pos.';'.$b["id_buisson"].';'.$b["nom_type_buisson"].$fin;
							}
						}
					}

					if ($bosquets != null) {
						foreach($bosquets as $b) {
							if ($display_x == $b["x_bosquet"] && $display_y == $b["y_bosquet"]) {
								$tabBosquets[] = array("id_bosquet" => $b["id_bosquet"]);
								$nom_systeme_environnement = $b["nom_systeme_type_bosquet"];
								$retour .= 'BOSQUET;'.$pos.';'.$b["id_bosquet"].';'.$b["nom_systeme_type_bosquet"].$fin;
							}
						}
					}

					if ($routes != null) {
						foreach($routes as $r) {
							if ($display_x == $r["x_route"] && $display_y == $r["y_route"]) {
								$retour .= 'ROUTE;'.$pos.';'.$r["id_route"].';'.$r["type_route"].$fin;
							}
						}
					}

					if ($souleMatch != null) {
						foreach($souleMatch as $s) {
							if ($display_x == $s["x_ballon_soule_match"] && $display_y == $s["y_ballon_soule_match"]) {
								$retour .= 'BALLON_SOULE;'.$pos.';present'.$fin;
							}
						}
					}
				}
			}
		}
	}
}