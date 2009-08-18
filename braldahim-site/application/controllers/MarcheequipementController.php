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
class MarcheequipementController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		Zend_Loader::loadClass('Bral_Util_Equipement');
		Zend_Loader::loadClass('EchoppeEquipement');
		Zend_Loader::loadClass('EchoppeEquipementMinerai');
		Zend_Loader::loadClass('EchoppeEquipementPartiePlante');
		Zend_Loader::loadClass('EquipementBonus');
		Zend_Loader::loadClass('EquipementRune');
		Zend_Loader::loadClass('Bral_Xml_GridDhtmlx');
		Zend_Loader::loadClass('Bral_Helper_DetailEquipement');
		Zend_Loader::loadClass('Bral_Util_BBParser');
		Zend_Loader::loadClass('Bral_Helper_Tooltip');
		
		$f = new Zend_Filter_StripTags();
		
		$emplacementSelect = intval($f->filter($this->_request->get("emplacementselect")));
		if ($emplacementSelect <= 0) {
			$emplacementSelect = -1;
		}
		$this->view->emplacementSelect = $emplacementSelect;
		
		$regionSelect = intval($f->filter($this->_request->get("regionselect")));
		if ($regionSelect <= 0) {
			$regionSelect = -1;
		}
		$this->view->regionSelect = $regionSelect;
		
		$equipementSelect = intval($f->filter($this->_request->get("equipementselect")));
		if ($equipementSelect <= 0) {
			$equipementSelect = -1;
		}
		$this->view->equipementSelect = $equipementSelect;
	}

	function indexAction() {
		Zend_Loader::loadClass('TypeEmplacement');
		$typeEmplacement = new TypeEmplacement();
		$rowset = $typeEmplacement->fetchAll(null, "nom_type_emplacement");
		$emplacements[-1] = "Tous";
		foreach ($rowset as $r) {
			$emplacements[$r["id_type_emplacement"]] = $r["nom_type_emplacement"];
		}
		$this->view->emplacements = $emplacements;
		
		Zend_Loader::loadClass('Region');
		$regionTable = new Region();
		$rowset = $regionTable->fetchAll(null, "nom_region");
		$regions[-1] = "Toutes";
		foreach ($rowset as $r) {
			$regions[$r["id_region"]] = $r["nom_region"];
		}
		$this->view->regions = $regions;
		
		Zend_Loader::loadClass('TypeEquipement');
		$typeEquipement = new TypeEquipement();
		$rowset = $typeEquipement->fetchAll(null, "nom_type_equipement");
		
		$equipements[-1] = "Tous";
		foreach ($rowset as $r) {
			$equipements[$r["id_type_equipement"]] = $r["nom_type_equipement"];
		}
		$this->view->equipements = $equipements;
		
		$this->render();
	}
	
	function renderxmlAction() {
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		
		$f = new Zend_Filter_StripTags();
		$posStart = intval($f->filter($this->_request->get("posStart")));
		$count = intval($f->filter($this->_request->get("count")));
		
		$ordreRecu = intval($f->filter($this->_request->get("orderby")));
		$direct = $f->filter($this->_request->get("direct"));
		 
		$ordre = null;
		if ($direct == "asc") {
			$direct = "ASC";
		} else {
			$direct = "DESC";
		}
		
		if ($posStart == null || $posStart <= 0) {
			$posStart = 0;
		}
		
		if ($count == null || $count <= 0) {
			$count = 100;
		}
		
		switch($ordreRecu) {
			case 0:
				$ordre = "nom_region ".$direct;
				break;
			case 1:
				$ordre = "date_echoppe_equipement ".$direct;
				break;
			case 2:
				$ordre = "id_hobbit ".$direct;
				break;
			case 3:
				$ordre = "nom_systeme_metier ".$direct;
				break;
			case 4:
				$ordre = "nom_type_emplacement ".$direct;
				break;
			case 5:
				$ordre = "nom_type_equipement ".$direct;
				break;
			case 6:
				$ordre = "id_type_equipement ".$direct;
				break;
			case 7:
				$ordre = "niveau_recette_equipement ".$direct;
				break;
		}
		
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByCriteres($ordre, $posStart, $count, $this->view->regionSelect, $this->view->emplacementSelect, $this->view->equipementSelect);
		
		$idEquipements = null;
		foreach ($equipements as $e) {
			$idEquipements[] = $e["id_echoppe_equipement"];
		}
		
		if ($idEquipements != null && count($idEquipements) > 0) {
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);
			
			$equipementBonusTable = new EquipementBonus();
			$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);
			
			$echoppeEquipementMineraiTable = new EchoppeEquipementMinerai();
			$echoppeEquipementMinerai = $echoppeEquipementMineraiTable->findByIdsEquipement($idEquipements);
				
			$echoppeEquipementPartiePlanteTable = new EchoppeEquipementPartiePlante();
			$echoppeEquipementPartiePlante = $echoppeEquipementPartiePlanteTable->findByIdsEquipement($idEquipements);
		}
		
		$dhtmlxGrid = new Bral_Xml_GridDhtmlx();
		
		if ($idEquipements != null && count($equipements) > 0) {
			foreach($equipements as $e) {
			
				$runes = null;
				if (count($equipementRunes) > 0) {
					foreach($equipementRunes as $r) {
						if ($r["id_equipement_rune"] == $e["id_echoppe_equipement"]) {
							$runes[] = array(
								"id_rune_equipement_rune" => $r["id_rune_equipement_rune"],
								"id_fk_type_rune_equipement_rune" => $r["id_fk_type_rune_equipement_rune"],
								"nom_type_rune" => $r["nom_type_rune"],
								"image_type_rune" => $r["image_type_rune"],
								"effet_type_rune" => $r["effet_type_rune"],
							);
						}
					}
				}
				
				$bonus = null;
				if (count($equipementBonus) > 0) {
					foreach($equipementBonus as $b) {
						if ($b["id_equipement_bonus"] == $e["id_echoppe_equipement"]) {
							$bonus = $b;
							break;
						}
					}
				}

				$minerai = null;
				if (count($echoppeEquipementMinerai) > 0) {
					foreach($echoppeEquipementMinerai as $r) {
						if ($r["id_fk_echoppe_equipement_minerai"] == $e["id_echoppe_equipement"]) {
							$minerai[] = array(
								"prix_echoppe_equipement_minerai" => $r["prix_echoppe_equipement_minerai"],
								"nom_type_minerai" => $r["nom_type_minerai"],
							);
						}
					}
				}
				
				$partiesPlantes = null;
				if (count($echoppeEquipementPartiePlante) > 0) {
					foreach($echoppeEquipementPartiePlante as $p) {
						if ($p["id_fk_echoppe_equipement_partieplante"] == $e["id_echoppe_equipement"]) {
							$partiesPlantes[] = array(
								"prix_echoppe_equipement_partieplante" => $p["prix_echoppe_equipement_partieplante"],
								"nom_type_plante" => $p["nom_type_plante"],
								"nom_type_partieplante" => $p["nom_type_partieplante"],
								"prefix_type_plante" => $p["prefix_type_plante"],
							);
						}
					}
				}
				
				$equipement = array(
					"id_equipement" => $e["id_echoppe_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
					"nom_standard" => $e["nom_type_equipement"],
					"id_type_equipement" => $e["id_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"emplacement" => $e["nom_type_emplacement"],
					"id_type_emplacement" => $e["id_type_emplacement"],
					"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
					"nom_systeme_type_piece" => $e["nom_systeme_type_piece"],
					"nb_runes" => $e["nb_runes_equipement"],
					"id_fk_recette_equipement" => $e["id_fk_recette_equipement"],
					"armure" => $e["armure_recette_equipement"],
					"force" => $e["force_recette_equipement"],
					"agilite" => $e["agilite_recette_equipement"],
					"vigueur" => $e["vigueur_recette_equipement"],
					"sagesse" => $e["sagesse_recette_equipement"],
					"vue" => $e["vue_recette_equipement"],
					"bm_attaque" => $e["bm_attaque_recette_equipement"],
					"bm_degat" => $e["bm_degat_recette_equipement"],
					"bm_defense" => $e["bm_defense_recette_equipement"],
					"poids" => $e["poids_recette_equipement"],
					"suffixe" => $e["suffixe_mot_runique"],
					"id_fk_mot_runique" => $e["id_fk_mot_runique_equipement"],
					"nom_systeme_mot_runique" => $e["nom_systeme_mot_runique"],
					"etat_courant" => $e["etat_courant_equipement"],
					"etat_initial" => $e["etat_initial_equipement"],
					"vernis_bm_vue" => $e["vernis_bm_vue_equipement_bonus"],
					"vernis_bm_armure" => $e["vernis_bm_armure_equipement_bonus"],
					"vernis_bm_poids" => $e["vernis_bm_poids_equipement_bonus"],
					"vernis_bm_agilite" => $e["vernis_bm_agilite_equipement_bonus"],
					"vernis_bm_force" => $e["vernis_bm_force_equipement_bonus"],
					"vernis_bm_sagesse" => $e["vernis_bm_sagesse_equipement_bonus"],
					"vernis_bm_vigueur" => $e["vernis_bm_vigueur_equipement_bonus"],
					"vernis_bm_attaque" => $e["vernis_bm_attaque_equipement_bonus"],
					"vernis_bm_degat" => $e["vernis_bm_degat_equipement_bonus"],
					"vernis_bm_defense" => $e["vernis_bm_defense_equipement_bonus"],
					"prix_1_vente_echoppe_equipement" => $e["prix_1_vente_echoppe_equipement"],
					"prix_2_vente_echoppe_equipement" => $e["prix_2_vente_echoppe_equipement"],
					"prix_3_vente_echoppe_equipement" => $e["prix_3_vente_echoppe_equipement"],
					"unite_1_vente_echoppe_equipement" => $e["unite_1_vente_echoppe_equipement"],
					"unite_2_vente_echoppe_equipement" => $e["unite_2_vente_echoppe_equipement"],
					"unite_3_vente_echoppe_equipement" => $e["unite_3_vente_echoppe_equipement"],
					"commentaire_vente_echoppe_equipement" => $e["commentaire_vente_echoppe_equipement"],
					"runes" => $runes,
					"bonus" => $bonus,
					"prix_minerais" => $minerai,
					"prix_parties_plantes" => $partiesPlantes,
					"nom_region" => $e["nom_region"],
				);
				
				$tab = null;
				$tab[] = $equipement["nom_region"];
				$tab[] = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y',$e["date_echoppe_equipement"]);
				$hobbit = $e["prenom_hobbit"]." ".$e["nom_hobbit"]." (".$e["id_hobbit"].")";
				$hobbit .= "^javascript:ouvrirWin(\"".$this->view->config->url->game."/voir/hobbit/?hobbit=".$e["id_hobbit"]."\");^_self";
				$tab[] = $hobbit;
				if ($e["sexe_hobbit"] == "masculin") {
					$tab[] = $e["nom_masculin_metier"]. "<br>(".$e["x_echoppe"].", ".$e["y_echoppe"].")";
				} else {
					$tab[] = $e["nom_feminin_metier"]. "<br>(".$e["x_echoppe"].", ".$e["y_echoppe"].")";
				}
				$tab[] = $e["nom_type_emplacement"];
				$tab[] = "<img src='/public/styles/braldahim_defaut/images/type_equipement/type_equipement_".$equipement["id_type_equipement"].".png' alt=\"".htmlspecialchars($equipement["nom"]) ."\" ".Bral_Helper_DetailEquipement::afficherJs($equipement)."/>";
				$tab[] = $e["nom_type_equipement"]." ".addslashes($e["suffixe_mot_runique"])." de qualité ".$e["nom_type_qualite"];
				$tab[] = $equipement["niveau"];
				$tab[] = Bral_Helper_DetailEquipement::afficherPrix($equipement);
				$tab[] = Bral_Util_BBParser::bbcodeReplace($equipement["commentaire_vente_echoppe_equipement"]);
				$dhtmlxGrid->addRow($e["id_echoppe_equipement"], $tab);
			}
		}
		
		$total = $echoppeEquipementTable->countByCriteres($this->view->regionSelect, $this->view->emplacementSelect, $this->view->equipementSelect);
		$this->view->grid = $dhtmlxGrid->render($total, $posStart);
	}
}