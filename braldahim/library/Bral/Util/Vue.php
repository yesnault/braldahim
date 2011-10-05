<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Vue
{

	public static function getJsonData(&$view)
	{
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('Echoppe');
		Zend_Loader::loadClass('Champ');
		Zend_Loader::loadClass('Crevasse');
		Zend_Loader::loadClass('Element');
		Zend_Loader::loadClass('ElementAliment');
		Zend_Loader::loadClass('ElementEquipement');
		Zend_Loader::loadClass('ElementMunition');
		Zend_Loader::loadClass('ElementPartieplante');
		Zend_Loader::loadClass('ElementMateriel');
		Zend_Loader::loadClass('ElementMinerai');
		Zend_Loader::loadClass('ElementPotion');
		Zend_Loader::loadClass('ElementGraine');
		Zend_Loader::loadClass('ElementIngredient');
		Zend_Loader::loadClass('ElementRune');
		Zend_Loader::loadClass('ElementTabac');
		Zend_Loader::loadClass('Environnement');
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('BraldunsMetiers');
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('Nid');
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass('Region');
		Zend_Loader::loadClass('Buisson');
		Zend_Loader::loadClass('Bosquet');
		Zend_Loader::loadClass('TypeLieu');
		Zend_Loader::loadClass('Route');
		Zend_Loader::loadClass('Eau');
		Zend_Loader::loadClass('SouleMatch');
		Zend_Loader::loadClass('Tunnel');
		Zend_Loader::loadClass('Ville');
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('Bral_Util_Marcher');
		Zend_Loader::loadClass('Bral_Util_Equipement');
		Zend_Loader::loadClass('Bral_Util_Potion');

		self::prepare($view);
		return self::data($view);
	}

	private static function prepare(&$view)
	{
		$x = $view->user->x_braldun;
		$y = $view->user->y_braldun;
		$z = $view->user->z_braldun;
		$bm = $view->user->vue_bm_braldun;

		$view->vue_nb_cases = Bral_Util_Commun::getVueBase($x, $y, $z) + $bm;
		if ($view->vue_nb_cases < 0) {
			$view->vue_nb_cases = 0;
		}
		$view->x_min = $x - $view->vue_nb_cases;
		$view->x_max = $x + $view->vue_nb_cases;
		$view->y_min = $y - $view->vue_nb_cases;
		$view->y_max = $y + $view->vue_nb_cases;

		$view->z_position = $z;

		$view->centre_x = $x;
		$view->centre_y = $y;
	}

	private static function data(&$view)
	{
		$view->environnement = null;
		$view->centre_nom_region = null;
		$view->est_pvp_region = null;
		$view->centre_description_region = null;
		$view->centre_nom_ville = null;
		$view->centre_est_capitale = null;

		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($view->user->id_braldun);
		unset($braldunsMetiersTable);
		$tabMetiers = null;
		if ($braldunsMetierRowset != null) {
			foreach ($braldunsMetierRowset as $m) {
				$possedeMetier = true;
				$tabMetiers[] = $m['nom_systeme_metier'];
			}
			unset($braldunsMetierRowset);
		}

		$monstreTable = new Monstre();
		$cadavres = $monstreTable->selectVueCadavre($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$charretteTable = new Charrette();
		$charrettes = $charretteTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$champTable = new Champ();
		$champs = $champTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$crevasseTable = new Crevasse();
		$crevasses = $crevasseTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position, 'oui');
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementTable = new Element();
		$elements = $elementTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementEquipementTable = new ElementEquipement();
		$elementsEquipements = $elementEquipementTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementMunitionsTable = new ElementMunition();
		$elementsMunitions = $elementMunitionsTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementPartiePlanteTable = new ElementPartieplante();
		$elementsPartieplantes = $elementPartiePlanteTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementMaterielTable = new ElementMateriel();
		$elementsMateriels = $elementMaterielTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementMineraisTable = new ElementMinerai();
		$elementsMinerais = $elementMineraisTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementPotionTable = new ElementPotion();
		$elementsPotions = $elementPotionTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementAlimentTable = new ElementAliment();
		$elementsAliments = $elementAlimentTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementGraineTable = new ElementGraine();
		$elementsGraines = $elementGraineTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementIngredientTable = new ElementIngredient();
		$elementsIngredients = $elementIngredientTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementRuneTable = new ElementRune();
		$elementsRunes = $elementRuneTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$elementTabacTable = new ElementTabac();
		$elementsTabac = $elementTabacTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position, -1, true, true);
		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$nidTable = new Nid();
		$nids = $nidTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$buissonTable = new Buisson();
		$buissons = $buissonTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$eauTable = new Eau();
		$eaux = $eauTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$bosquetTable = new Bosquet();
		$bosquets = $bosquetTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$routeTable = new Route();
		$routes = $routeTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		$souleMatchTable = new SouleMatch();
		$souleMatch = $souleMatchTable->selectBallonVue($view->x_min, $view->y_min, $view->x_max, $view->y_max);

		$tunnels = null;
		if ($view->z_position < 10) {
			$tunnelTable = new Tunnel();
			$tunnels = $tunnelTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
			unset($tunnelTable);
		}
		$villeTable = new Ville();
		$villes = $villeTable->findAllWithRegion();
		
		unset($villeTable);
		$zoneTable = new Zone();
		$zones = $zoneTable->selectVue($view->x_min, $view->y_min, $view->x_max, $view->y_max, $view->z_position);
		unset($zoneTable);

		$centre_x_min = $view->x_min;
		$centre_x_max = $view->x_max;
		$centre_y_min = $view->y_min;
		$centre_y_max = $view->y_max;
		$positionZ = $view->user->z_braldun;

		/*
				   * $marcher = null;
				   * if ($view->estVueEtendue === false && $view->user->administrationvue == false) {
					  $utilMarcher = new Bral_Util_Marcher();
					  $marcher = $utilMarcher->calcul($view->user);
				  }*/

		$estSurLieu = false;
		$estSurEchoppe = false;
		$estSurChamp = false;

		$tableau["Couches"][$positionZ]["Z"] = $positionZ;
		$tableau["Vues"][0]["Z"] = $positionZ;

		for ($j = $centre_y_max; $j >= $centre_y_min; $j--) {
			for ($i = $centre_x_min; $i <= $centre_x_max; $i++) {
				$display_x = $i;
				$display_y = $j;

				foreach ($zones as $z) {
					if ($display_x >= $z['x_min_zone'] &&
						$display_x <= $z['x_max_zone'] &&
						$display_y >= $z['y_min_zone'] &&
						$display_y <= $z['y_max_zone']
					) {
						$nom_zone = $z['nom_zone'];
						$description_zone = $z['description_zone'];
						$nom_systeme_environnement = $z['nom_systeme_environnement'];
						$nom_environnement = htmlspecialchars($z['nom_environnement']);

						if ($tunnels != null) {
							foreach ($tunnels as $t) {
								if ($display_x == $t['x_tunnel'] && $display_y == $t['y_tunnel']) {
									$nom_systeme_environnement = Environnement::NOM_SYSTEME_TUNNEL;
									$nom_environnement = Environnement::NOM_TUNNEL;
									break;
								}
							}
						}

						if ($bosquets != null) {
							foreach ($bosquets as $b) {
								if ($display_x == $b['x_bosquet'] && $display_y == $b['y_bosquet']) {
									$tabBosquets[] = array('id_bosquet' => $b['id_bosquet']);
									$nom_systeme_environnement = $b['nom_systeme_type_bosquet'];
									$nom_environnement = $b['description_type_bosquet'];
								}
							}
						}

						if ($eaux != null) {
							foreach ($eaux as $e) {
								if ($display_x == $e['x_eau'] && $display_y == $e['y_eau']) {
									$tabEaux[] = array('id_eau' => $e['id_eau']);
									$nom_systeme_environnement = $e['type_eau'];
								}
							}
						}

						if ($routes != null) {
							foreach ($routes as $r) {
								if ($display_x == $r['x_route'] && $display_y == $r['y_route']) {

									if ($r['type_route'] == 'ville' || $r['type_route'] == 'ruine') {
										$nom_systeme_environnement = 'pave';
									} elseif ($r['type_route'] == 'echoppe') {
										$nom_systeme_environnement = 'pave';
									} elseif ($r['type_route'] == 'route') {
										$nom_systeme_environnement = 'route';
									} else {
										$nom_systeme_environnement .= '-gr';
									}

									break;
								}
							}
						}

						if ($palissades != null) {
							foreach ($palissades as $p) {
								if ($display_x == $p['x_palissade'] && $display_y == $p['y_palissade']) {
									//$tabPalissades[] = array('id_palissade' => $p['id_palissade'], 'est_destructible_palissade' => $p['est_destructible_palissade'], 'est_portail_palissade' => $p['est_portail_palissade'], 'date_fin_palissade' => $p["date_fin_palissade"]);
									//TODO Afficher les infos sur la palissade sur la vue
									if ($p['est_portail_palissade'] == "oui") {
										$nom_systeme_environnement = "portail";
									} else {
										$nom_systeme_environnement = "palissade";
									}
								}
							}
						}

						if ($crevasses != null) {
							foreach ($crevasses as $c) {
								if ($display_x == $c['x_crevasse'] && $display_y == $c['y_crevasse']) {
									$tabCrevasses[] = array('id_crevasse' => $c['id_crevasse']);
									$nom_systeme_environnement = $nom_systeme_environnement . '-' . 'crevasse';
								}
							}
						}

						$tableau["Couches"][$positionZ]["Cases"][] = array("X" => $display_x, "Y" => $display_y, "Fond" => $nom_systeme_environnement);
						break;
					}
				}
			}
		}


		if ($echoppes != null) {
			foreach ($echoppes as $e) {
				if ($e['sexe_braldun'] == 'feminin') {
					$nom_metier = $e['nom_feminin_metier'];
				} else {
					$nom_metier = $e['nom_masculin_metier'];
				}
				//$tabEchoppes[] = array('id_echoppe' => $e['id_echoppe'], 'nom_echoppe' => $e['nom_echoppe'], 'nom_systeme_metier' => $e['nom_systeme_metier'], 'nom_metier' => $nom_metier, 'nom_braldun' => $e['nom_braldun'], 'prenom_braldun' => $e['prenom_braldun'], 'id_braldun' => $e['id_braldun']);
				$tableau["Couches"][$positionZ]["Echoppes"][] = array(
					"X" => $e['x_echoppe'],
					"Y" => $e['y_echoppe'],
					"Nom" => "" . $e['nom_echoppe'],
					"Métier" => $e['nom_systeme_metier'],
					"IdBraldun" => $e['id_braldun'],
					"NomCompletBraldun" => $e['prenom_braldun'] . " " . $e['nom_braldun'],
				);
			}
		}


		if ($champs != null) {
			foreach ($champs as $e) {
				//$tabChamps[] = array('id_champ' => $e['id_champ'], 'nom_champ' => $e['nom_champ'], 'nom_braldun' => $e['nom_braldun'], 'prenom_braldun' => $e['prenom_braldun'], 'id_braldun' => $e['id_braldun']);
				$tableau["Couches"][$positionZ]["Champs"][] = array(
					"X" => $e['x_champ'],
					"Y" => $e['y_champ'],
					"IdBraldun" => $e['id_braldun'],
					"NomCompletBraldun" => $e['prenom_braldun'] . " " . $e['nom_braldun'],
				);
			}
		}

		if ($lieux != null) {
			foreach ($lieux as $l) {
				/*$tabLieux[] = array('id_lieu' => $l['id_lieu'], 'nom_lieu' => $l['nom_lieu'], 'nom_type_lieu' => $l['nom_type_lieu'], 'nom_systeme_type_lieu' => $l['nom_systeme_type_lieu']);
									$lieuCourant = $l;
									$estLimiteVille = false;
									*/

				$tableau["LieuxVilles"][] = array("Id" => $l['id_lieu'],
					"Nom" => $l['nom_lieu'],
					"IdTypeLieu" => $l['id_type_lieu'],
					"X" => $l['x_lieu'],
					"Y" => $l['y_lieu']
				);

			}
		}


		if ($villes != null) {
			foreach ($villes as $v) {
				//	$region = array('nom' => $r['nom_region'], 'description' => $r['description_region'], 'est_pvp_region' => $r['est_pvp_region']);
				$tableau["Régions"][$v["id_region"]-1] = array(
					'id' => $v["id_region"],
					"Nom" => $v['nom_region'],
					"XMin" => $v['x_min_region'],
					"XMax" => $v['x_max_region'],
					"YMin" => $v['y_min_region'],
					"YMax" => $v['y_max_region'],
					"EstPvp" => ($v['est_pvp_region'] == "oui"),
				);

				$tableau["Villes"][] = array(
					'id' => $v["id_ville"],
					"Nom" => $v['nom_ville'],
					'EstCapitale' => $v["est_capitale_ville"],
					'XMin' => $v["x_min_ville"],
					'XMax' => $v["x_max_ville"],
					'YMin' => $v["y_min_ville"],
					'YMax' => $v["y_max_ville"],
				);
			}
		}

		$tabCommunaute = null;
		if ($bralduns != null) {
			foreach ($bralduns as $b) {
				$tableau["Vues"][0]["Time"] = date('now');
				$tableau["Vues"][0]["Voyeur"] = $view->user->id_braldun;
				$tableau["Vues"][0]["XMin"] = $view->x_min;
				$tableau["Vues"][0]["XMax"] = $view->x_max;
				$tableau["Vues"][0]["YMin"] = $view->y_min;
				$tableau["Vues"][0]["YMax"] = $view->y_max;

				$tableau["Vues"][0]["Bralduns"][] = array("Id" => $b['id_braldun'],
					"X" => $b['x_braldun'],
					"Y" => $b['y_braldun'],
					"Prénom" => $b['prenom_braldun'],
					"Nom" => $b['nom_braldun'],
					"Niveau" => $b['niveau_braldun'],
					"Sexe" => substr($b['sexe_braldun'], 0, 1),
					"KO" => ($b['est_ko_braldun'] == 'oui'),
					"Intangible" => ($b['est_intangible_braldun'] == "oui"),
					"Camp" => $b['soule_camp_braldun'] . "",
					"IdCommunauté" => $b['id_fk_communaute_braldun'],
				);

				if ($b['id_fk_communaute_braldun'] != null) {
					$tabCommunaute[$b['id_fk_communaute_braldun']] = array(
						'id_communaute' => $b['id_fk_communaute_braldun'],
						'nom_communaute' => $b['nom_communaute'],
					);
				}

			}
		}

		if ($charrettes != null) {
			foreach ($charrettes as $c) {
				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $c['x_charrette'],
					"Y" => $c['y_charrette'],
					'Type' => "charrette",
					'Quantité' => 0,
					'Label' => $c['nom_type_materiel'] . ' n°(' . $c['id_charrette'] . ')',
					'IdType' => $c['id_type_materiel'],
				);
			}
		}

		if ($elements != null) {
			foreach ($elements as $e) {
				self::addElement($tableau, $e, 'peau', 'peau', 'quantite_peau_element', 'x');
				self::addElement($tableau, $e, 'cuir', 'cuir', 'quantite_cuir_element', 's');
				self::addElement($tableau, $e, 'fourrure', 'fourrure', 'quantite_fourrure_element', 's');
				self::addElement($tableau, $e, 'planche', 'planche', 'quantite_planche_element', 's');
				self::addElement($tableau, $e, 'rondin', 'rondin', 'quantite_rondin_element', 's');
				self::addElement($tableau, $e, 'castar', 'castar', 'quantite_castar_element', 's');
			}
		}

		if ($elementsRunes != null) {
			foreach ($elementsRunes as $r) {
				/*   if ($display_x == $r['x_element_rune'] && $display_y == $r['y_element_rune']) {
									$tabElementsRunes[] = array('id_rune_element_rune' => $r['id_rune_element_rune'], 'id_butin' => $r['id_fk_butin_element_rune']);
								}*/
				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $r['x_element_rune'],
					"Y" => $r['y_element_rune'],
					'Type' => 'rune',
					'Quantité' => 1,
					'Label' => ' rune n°' . $r['id_rune_element_rune'],
					'IdType' => 0,
				);

			}
		}

		if ($buissons != null) {
			foreach ($buissons as $b) {
				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $b['x_buisson'],
					"Y" => $b['y_buisson'],
					'Type' => 'buisson',
					'Quantité' => 1,
					'Label' => $b['nom_type_buisson'],
					'IdType' => $b['id_type_buisson'],
				);
			}
		}

		if ($elementsMunitions != null) {
			foreach ($elementsMunitions as $m) {
				if ($m['quantite_element_munition'] > 0) {
					if ($m['quantite_element_munition'] > 1) {
						$label = $m['quantite_element_munition'] . ' ' . $m['nom_pluriel_type_munition'];
					} else {
						$label = $m['quantite_element_munition'] . ' ' . $m['nom_type_munition'];
					}
					$tableau["Vues"][0]["Objets"][] = array(
						"X" => $m['x_element_munition'],
						"Y" => $m['y_element_munition'],
						'Type' => 'munition',
						'Quantité' => $m['quantite_element_munition'],
						'Label' => $label,
						'IdType' => $m['id_type_munition'],
					);
				}
			}
		}

		if ($elementsEquipements != null) {
			foreach ($elementsEquipements as $e) {
				/*if ($display_x == $e['x_element_equipement'] && $display_y == $e['y_element_equipement']) {
									$tabElementsEquipements[] = array('id_equipement' => $e['id_element_equipement'],
										'nom' => Bral_Util_Equipement::getNomByIdRegion($e, $e['id_fk_region_equipement']),
										'qualite' => $e['nom_type_qualite'],
										'niveau' => $e['niveau_recette_equipement'],
										'suffixe' => $e['suffixe_mot_runique']);
								}
								*/

				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $e['x_element_equipement'],
					"Y" => $e['y_element_equipement'],
					'Type' => 'équipement',
					'Quantité' => 1,
					'Label' => Bral_Util_Equipement::getNomByIdRegion($e, $e['id_fk_region_equipement']),
					'IdType' => $e['id_type_equipement'],
				);
			}
		}

		if ($monstres != null) {
			foreach ($monstres as $m) {
				if ($m['genre_type_monstre'] == 'feminin') {
					$m_taille = $m['nom_taille_f_monstre'];
				} else {
					$m_taille = $m['nom_taille_m_monstre'];
				}

				$tableau["Vues"][0]["Monstres"][] = array(
					"X" => $m['x_monstre'],
					"Y" => $m['y_monstre'],
					"Id" => $m['id_monstre'],
					'Nom' => $m['nom_type_monstre'] . ' n°' . $m['id_monstre'],
					'Taille' => $m_taille,
					'Niveau' => $m['niveau_monstre'],
					'IdType' => $m['id_type_monstre'],
				);
			}
		}

		if ($elementsMateriels != null) {
			foreach ($elementsMateriels as $e) {
				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $e['x_element_materiel'],
					"Y" => $e['y_element_materiel'],
					'Type' => 'matériel',
					'Quantité' => 1,
					'Label' => $e['nom_type_materiel'] . ' n°' . $e['id_element_materiel'],
					'IdType' => $e['id_type_materiel'],
				);
			}
		}

		if ($elementsPotions != null) {
			foreach ($elementsPotions as $p) {
				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $p['x_element_potion'],
					"Y" => $p['y_element_potion'],
					'Type' => 'potion',
					'Quantité' => 1,
					'Label' => Bral_Util_Potion::getNomType($p['type_potion']) . ' n°' . $p['id_element_potion'],
					'IdType' => $p['id_type_potion'],
				);
			}
		}

		if ($elementsAliments != null) {
			foreach ($elementsAliments as $p) {
				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $p['x_element_aliment'],
					"Y" => $p['y_element_aliment'],
					'Type' => 'aliment',
					'Quantité' => 1,
					'Label' => $p['nom_type_aliment'] . ' (' . $p['nom_type_qualite'] . ') n°' . $p['id_element_aliment'],
					'IdType' => $p['id_type_aliment'],
				);
			}
		}


		if ($elementsGraines != null) {
			foreach ($elementsGraines as $p) {
				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $p['x_element_graine'],
					"Y" => $p['y_element_graine'],
					'Type' => 'graine',
					'Quantité' => $p['quantite_element_graine'],
					'Label' => $p['quantite_element_graine'] . ' graine' . Bral_Util_String::getPluriel($p['quantite_element_graine']) . ' ' . $p['prefix_type_graine'] . $p['nom_type_graine'],
					'IdType' => $p['id_type_graine'],
				);
			}
		}

		if ($elementsIngredients != null) {
			foreach ($elementsIngredients as $p) {

				if ($p['quantite_element_ingredient'] > 1) {
					$label = $p['quantite_element_ingredient'] . " " . $p["nom_pluriel_type_ingredient"];
				} else {
					$label = $p['quantite_element_ingredient'] . " " . $p["nom_type_ingredient"];
				}

				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $p['x_element_ingredient'],
					"Y" => $p['y_element_ingredient'],
					'Type' => 'ingrédient',
					'Quantité' => $p['quantite_element_ingredient'],
					'Label' => $label,
					'IdType' => $p['id_type_ingredient'],
				);
			}
		}

		if ($elementsMinerais != null) {
			foreach ($elementsMinerais as $m) {
				if ($m['quantite_brut_element_minerai'] > 0) {
					$tableau["Vues"][0]["Objets"][] = array(
						"X" => $m['x_element_minerai'],
						"Y" => $m['y_element_minerai'],
						'Type' => 'minerai',
						'Quantité' => $m['quantite_brut_element_minerai'],
						'Label' => $m['quantite_brut_element_minerai'] . ' minerai' . Bral_Util_String::getPluriel($m['quantite_brut_element_minerai']) . ' ' . $m['prefix_type_minerai'] . $m['nom_type_minerai'],
						'IdType' => $m['id_type_minerai'],
					);
				}

				if ($m['quantite_lingots_element_minerai'] > 0) {
					$tableau["Vues"][0]["Objets"][] = array(
						"X" => $m['x_element_minerai'],
						"Y" => $m['y_element_minerai'],
						'Type' => 'lingot',
						'Quantité' => $m['quantite_lingots_element_minerai'],
						'Label' => $m['quantite_lingots_element_minerai'] . ' lingot' . Bral_Util_String::getPluriel($m['quantite_lingots_element_minerai']) . ' ' . $m['prefix_type_minerai'] . $m['nom_type_minerai'],
						'IdType' => $m['id_type_minerai'],
					);
				}
			}
		}

		if ($elementsPartieplantes != null) {
			foreach ($elementsPartieplantes as $m) {
				if ($m['quantite_element_partieplante'] > 0) {
					$label = $m['quantite_element_partieplante'] . ' ' . $m['nom_type_partieplante'] . Bral_Util_String::getPluriel($m['quantite_element_partieplante']) . " " . $m['prefix_type_plante'] . $m['nom_type_plante'] . ' (plante brute)';

					$tableau["Vues"][0]["Objets"][] = array(
						"X" => $m['x_element_partieplante'],
						"Y" => $m['y_element_partieplante'],
						'Type' => 'plante',
						'Quantité' => $m['quantite_element_partieplante'],
						'Label' => $label,
						'IdType' => $m['id_type_partieplante'],
					);

				}

				if ($m['quantite_preparee_element_partieplante'] > 0) {
					$label = $m['quantite_preparee_element_partieplante'] . ' ' . $m['nom_type_partieplante'] . Bral_Util_String::getPluriel($m['quantite_preparee_element_partieplante']) . " " . $m['prefix_type_plante'] . $m['nom_type_plante'] . ' (plante préparée)';

					$tableau["Vues"][0]["Objets"][] = array(
						"X" => $m['x_element_partieplante'],
						"Y" => $m['y_element_partieplante'],
						'Type' => 'plante',
						'Quantité' => $m['quantite_preparee_element_partieplante'],
						'Label' => $label,
						'IdType' => $m['id_type_partieplante'],
					);
				}
			}
		}

		if ($elementsTabac != null) {
			foreach ($elementsTabac as $m) {
				if ($m['quantite_feuille_element_tabac'] > 0) {
					$tableau["Vues"][0]["Objets"][] = array(
						"X" => $m['x_element_tabac'],
						"Y" => $m['y_element_tabac'],
						'Type' => 'tabac',
						'Quantité' => $m['quantite_feuille_element_tabac'],
						'Label' => $m['quantite_feuille_element_tabac'] . " Feuille" . Bral_Util_String::getPluriel($m['quantite_feuille_element_tabac']) . " " . $m['nom_court_type_tabac'],
						'IdType' => $m['id_type_tabac'],
					);

				}
			}
		}

		if ($souleMatch != null) {
			foreach ($souleMatch as $s) {
				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $s['x_ballon_soule_match'],
					"Y" => $s['y_ballon_soule_match'],
					'Type' => 'ballon',
					'Quantité' => 1,
					'Label' => 'Ballon de soule',
					'IdType' => 0,
				);
			}
		}

		if ($nids != null) {
			foreach ($nids as $n) {
				$tableau["Vues"][0]["Objets"][] = array(
					"X" => $n['x_nid'],
					"Y" => $n['y_nid'],
					'Type' => 'nid',
					'Quantité' => 1,
					'Label' => $n['nom_nid_type_monstre'],
					'IdType' => 0,
				);
			}
		}

		if ($cadavres != null) {
			foreach ($cadavres as $c) {
				if ($c['genre_type_monstre'] == 'feminin') {
					$c_taille = $c['nom_taille_f_monstre'];
				} else {
					$c_taille = $c['nom_taille_m_monstre'];
				}

				$tableau["Vues"][0]["Cadavres"][] = array(
					"X" => $c['x_monstre'],
					"Y" => $c['y_monstre'],
					"Id" => $c['id_monstre'],
					'Nom' => $c['nom_type_monstre'] . ' n°' . $c['id_monstre'],
					'Taille' => $c_taille,
					'Niveau' => $c['niveau_monstre'],
					'IdType' => $c['id_type_monstre'],
				);
			}
		}

		if ($tabCommunaute != null && count($tabCommunaute) > 0) {
			foreach ($tabCommunaute as $c) {
				$tableau["Communautés"][$c['id_communaute']] = array(
					"Id" => $c['id_communaute'],
					'Nom' => $c['nom_communaute'],
				);
			}
		}

		/*

					  if ($view->centre_x == $display_x && $view->centre_y == $display_y) {
						  $view->centre_environnement = $nom_environnement;
					  }

					  if ($view->user->x_braldun == $display_x && $view->user->y_braldun == $display_y) {
						  $tabMarcher['case'] = null;
					  } else if ($marcher != null && $marcher['tableauValidationXY'] != null && array_key_exists($display_x, $marcher['tableauValidationXY']) && array_key_exists($display_y, $marcher['tableauValidationXY'][$display_x])) {
						  $tabMarcher['case'] = $marcher['tableauValidationXY'][$display_x][$display_y];
						  $tabMarcher['general'] = $marcher;
					  } else {
						  $tabMarcher['case'] = null;
					  }

					  $tableau[] = $tab;
					  if ($change_level) {
						  $change_level = false;
					  }
				  }
			  }
			  */

		$view->estSurLieu = $estSurLieu;
		$view->estSurEchoppe = $estSurEchoppe;
		$view->estSurChamp = $estSurChamp;

		return $tableau;
	}

	private static function addElement(&$tableau, $rowset, $type, $libelle, $colonne, $pluriel)
	{
		if ($rowset[$colonne] > 0) {
			$tableau["Vues"][0]["Objets"][] = array(
				"X" => $rowset['x_element'],
				"Y" => $rowset['y_element'],
				'Type' => $type,
				'Quantité' => $rowset[$colonne],
				'Label' => $rowset[$colonne] . ' ' . $libelle . Bral_Util_String::getPluriel($rowset[$colonne], $pluriel),
				'IdType' => 0,
			);
		}
	}
}
