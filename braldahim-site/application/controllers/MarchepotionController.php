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
class MarchepotionController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		Zend_Loader::loadClass('EchoppePotion');
		Zend_Loader::loadClass('EchoppePotionMinerai');
		Zend_Loader::loadClass('EchoppePotionPartiePlante');
		Zend_Loader::loadClass('Bral_Xml_GridDhtmlx');
		Zend_Loader::loadClass('Bral_Helper_DetailPotion');
		Zend_Loader::loadClass('Bral_Util_BBParser');
		Zend_Loader::loadClass('Bral_Helper_Tooltip');
		Zend_Loader::loadClass('Bral_Util_Poids');

		$f = new Zend_Filter_StripTags();
		$anneeCourante = date("Y");
		$typebmSelect = $f->filter($this->_request->get("typebmselect"));

		if ($typebmSelect == 'malus') {
			$typebmSelect = 'malus';
		} elseif ($typebmSelect == 'malus') {
			$typebmSelect = 'bonus';
		} else {
			$typebmSelect = -1;
		}
		$this->view->typebmSelect = $typebmSelect;

		$regionSelect = intval($f->filter($this->_request->get("regionselect")));
		if ($regionSelect <= 0) {
			$regionSelect = -1;
		}
		$this->view->regionSelect = $regionSelect;

		$potionSelect = intval($f->filter($this->_request->get("potionselect")));
		if ($potionSelect <= 0) {
			$potionSelect = -1;
		}
		$this->view->potionSelect = $potionSelect;
	}

	function indexAction() {
		Zend_Loader::loadClass('TypePotion');
		Zend_Loader::loadClass("Bral_Util_Potion");
		
		$typePotion = new TypePotion();
		$rowset = $typePotion->findDistinctType();
		$types[-1] = "Tous";
		foreach ($rowset as $r) {
			$types[$r["bm_type_potion"]] = $r["bm_type_potion"];
		}
		$this->view->types = $types;

		Zend_Loader::loadClass('Region');
		$regionTable = new Region();
		$rowset = $regionTable->fetchAll(null, "nom_region");
		$regions[-1] = "Toutes";
		foreach ($rowset as $r) {
			$regions[$r["id_region"]] = $r["nom_region"];
		}
		$this->view->regions = $regions;

		Zend_Loader::loadClass('TypePotion');
		$typePotion = new TypePotion();
		$rowset = $typePotion->fetchAll(null, array("type_potion","nom_type_potion"));

		$potions[-1] = "Tous";
		foreach ($rowset as $r) {
			$potions[$r["id_type_potion"]] = Bral_Util_Potion::getNomType($r["type_potion"]). " ".$r["nom_type_potion"];
		}
		$this->view->potions = $potions;

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
				$ordre = "date_echoppe_potion ".$direct;
				break;
			case 2:
				$ordre = "id_braldun ".$direct;
				break;
			case 3:
				$ordre = "nom_systeme_metier ".$direct;
				break;
			case 4:
				$ordre = "bm_type_potion ".$direct;
				break;
			case 5:
				$ordre = "id_type_potion ".$direct;
				break;
			case 6:
				$ordre = "id_type_potion ".$direct;
				break;
			case 7:
				$ordre = "niveau_potion ".$direct;
				break;
		}

		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByCriteres($ordre, $posStart, $count, $this->view->regionSelect, $this->view->typebmSelect, $this->view->potionSelect);

		$idPotions = null;

		foreach ($potions as $p) {
			$idPotions[] = $p["id_echoppe_potion"];
		}

		if (count($idPotions) > 0) {
			$echoppPotionMineraiTable = new EchoppePotionMinerai();
			$echoppePotionMinerai = $echoppPotionMineraiTable->findByIdsPotion($idPotions);

			$echoppePotionPartiePlanteTable = new EchoppePotionPartiePlante();
			$echoppePotionPartiePlante = $echoppePotionPartiePlanteTable->findByIdsPotion($idPotions);
		}

		$dhtmlxGrid = new Bral_Xml_GridDhtmlx();

		Zend_Loader::loadClass("Bral_Util_Potion");

		if (count($potions) > 0) {
			foreach($potions as $p) {
				$minerai = null;
				if (count($echoppePotionMinerai) > 0) {
					foreach($echoppePotionMinerai as $r) {
						if ($r["id_fk_echoppe_potion_minerai"] == $p["id_echoppe_potion"]) {
							$minerai[] = array(
								"prix_echoppe_potion_minerai" => $r["prix_echoppe_potion_minerai"],
								"nom_type_minerai" => $r["nom_type_minerai"],
							);
						}
					}
				}

				$partiesPlantes = null;
				if (count($echoppePotionPartiePlante) > 0) {
					foreach($echoppePotionPartiePlante as $a) {
						if ($a["id_fk_echoppe_potion_partieplante"] == $p["id_echoppe_potion"]) {
							$partiesPlantes[] = array(
								"prix_echoppe_potion_partieplante" => $a["prix_echoppe_potion_partieplante"],
								"nom_type_plante" => $a["nom_type_plante"],
								"nom_type_partieplante" => $a["nom_type_partieplante"],
								"prefix_type_plante" => $a["prefix_type_plante"],
							);
						}
					}
				}

				$potion = array(
					"id_potion" => $p["id_echoppe_potion"],
					"nom" => $p["nom_type_potion"],
					"id_type_potion" => $p["id_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"caracteristique2" => $p["caract2_type_potion"],
					"bm2_type" => $p["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
					"prix_1_vente_echoppe_potion" => $p["prix_1_vente_echoppe_potion"],
					"prix_2_vente_echoppe_potion" => $p["prix_2_vente_echoppe_potion"],
					"prix_3_vente_echoppe_potion" => $p["prix_3_vente_echoppe_potion"],
					"unite_1_vente_echoppe_potion" => $p["unite_1_vente_echoppe_potion"],
					"unite_2_vente_echoppe_potion" => $p["unite_2_vente_echoppe_potion"],
					"unite_3_vente_echoppe_potion" => $p["unite_3_vente_echoppe_potion"],
					"commentaire_vente_echoppe_potion" => $p["commentaire_vente_echoppe_potion"],
					"prix_minerais" => $minerai,
					"prix_parties_plantes" => $partiesPlantes,
				);
					
				$tab = null;
				$tab[] = $p["nom_region"];
				$tab[] = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y',$p["date_echoppe_potion"]);
				$braldun = $p["prenom_braldun"]." ".$p["nom_braldun"]." (".$p["id_braldun"].")";
				$braldun .= "^javascript:ouvrirWin(\"".$this->view->config->url->game."/voir/braldun/?braldun=".$p["id_braldun"]."\");^_self";
				$tab[] = $braldun;
				if ($p["sexe_braldun"] == "masculin") {
					$tab[] = $p["nom_masculin_metier"]. "<br>(".$p["x_echoppe"].", ".$p["y_echoppe"].")";
				} else {
					$tab[] = $p["nom_feminin_metier"]. "<br>(".$p["x_echoppe"].", ".$p["y_echoppe"].")";
				}

				if ($potion["bm_type"] == null) {
					$tab[] = "État équipement";
				} else {
					$type = $potion["bm_type"]." ".$potion["caracteristique"];
					if ($potion["bm2_type"] != null) {
						$type .= "<br>".$potion["bm2_type"]." ".$potion["caracteristique2"];
					}
					$tab[] = $type;
				}
				 
				$tab[] = "<div class='braltip'>";
				$tab[] = Bral_Helper_DetailPotion::afficherTooltip($potion);
				$tab[] = "<img src='/public/styles/braldahim_defaut/images/type_potion/type_potion_".$potion["id_type_potion"].".png' alt=\"".htmlspecialchars($potion["nom"]) ."\"/>";
				$tab[] = "</div>";
				$tab[] = $p["nom_type_potion"]." de qualité ".$p["nom_type_qualite"];
				$tab[] = $p["niveau_potion"];
				$tab[] = Bral_Helper_DetailPotion::afficherPrix($potion);
				$tab[] = Bral_Util_BBParser::bbcodeReplace($potion["commentaire_vente_echoppe_potion"]);
				$dhtmlxGrid->addRow($p["id_echoppe_potion"], $tab);
			}
		}

		$total = $echoppePotionTable->countByCriteres($this->view->regionSelect, $this->view->typebmSelect, $this->view->potionSelect);
		$this->view->grid = $dhtmlxGrid->render($total, $posStart);
	}
}