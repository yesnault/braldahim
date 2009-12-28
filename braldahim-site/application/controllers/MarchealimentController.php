<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class MarchealimentController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		Zend_Loader::loadClass('EchoppeAliment');
		Zend_Loader::loadClass('EchoppeAlimentMinerai');
		Zend_Loader::loadClass('EchoppeAlimentPartiePlante');
		Zend_Loader::loadClass('Bral_Xml_GridDhtmlx');
		Zend_Loader::loadClass('Bral_Helper_DetailAliment');
		Zend_Loader::loadClass('Bral_Util_BBParser');
		Zend_Loader::loadClass('Bral_Helper_Tooltip');
		Zend_Loader::loadClass('Bral_Util_Aliment');
		
		$f = new Zend_Filter_StripTags();
		
		$regionSelect = intval($f->filter($this->_request->get("regionselect")));
		if ($regionSelect <= 0) {
			$regionSelect = -1;
		}
		$this->view->regionSelect = $regionSelect;
		
		$alimentSelect = intval($f->filter($this->_request->get("alimentselect")));
		if ($alimentSelect <= 0) {
			$alimentSelect = -1;
		}
		$this->view->alimentSelect = $alimentSelect;
	}

	function indexAction() {
		Zend_Loader::loadClass('Region');
		$regionTable = new Region();
		$rowset = $regionTable->fetchAll(null, "nom_region");
		$regions[-1] = "Toutes";
		foreach ($rowset as $r) {
			$regions[$r["id_region"]] = $r["nom_region"];
		}
		$this->view->regions = $regions;
		
		Zend_Loader::loadClass('TypeAliment');
		$typeAliment = new TypeAliment();
		$rowset = $typeAliment->fetchAll(null, "nom_type_aliment");
		
		$aliments[-1] = "Tous";
		foreach ($rowset as $r) {
			$aliments[$r["id_type_aliment"]] = $r["nom_type_aliment"];
		}
		$this->view->aliments = $aliments;
		
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
				$ordre = "date_echoppe_aliment ".$direct;
				break;
			case 2:
				$ordre = "id_hobbit ".$direct;
				break;
			case 3:
				$ordre = "nom_systeme_metier ".$direct;
				break;
			case 4:
				$ordre = "nom_type_aliment ".$direct;
				break;
			case 5:
				$ordre = "id_type_aliment ".$direct;
				break;
		}
		
		$echoppeAlimentTable = new EchoppeAliment();
		$aliments = $echoppeAlimentTable->findByCriteres($ordre, $posStart, $count, $this->view->regionSelect, $this->view->alimentSelect);
		
		$idAliments = null;
		foreach ($aliments as $e) {
			$idAliments[] = $e["id_echoppe_aliment"];
		}
		
		if ($idAliments != null && count($idAliments) > 0) {
			$echoppeAlimentMineraiTable = new EchoppeAlimentMinerai();
			$echoppeAlimentMinerai = $echoppeAlimentMineraiTable->findByIdsAliment($idAliments);
				
			$echoppeAlimentPartiePlanteTable = new EchoppeAlimentPartiePlante();
			$echoppeAlimentPartiePlante = $echoppeAlimentPartiePlanteTable->findByIdsAliment($idAliments);
		}
		
		$dhtmlxGrid = new Bral_Xml_GridDhtmlx();
		
		if ($idAliments != null && count($aliments) > 0) {
			foreach($aliments as $e) {
			
				$minerai = null;
				if (count($echoppeAlimentMinerai) > 0) {
					foreach($echoppeAlimentMinerai as $r) {
						if ($r["id_fk_echoppe_aliment_minerai"] == $e["id_echoppe_aliment"]) {
							$minerai[] = array(
								"prix_echoppe_aliment_minerai" => $r["prix_echoppe_aliment_minerai"],
								"nom_type_minerai" => $r["nom_type_minerai"],
							);
						}
					}
				}
				
				$partiesPlantes = null;
				if (count($echoppeAlimentPartiePlante) > 0) {
					foreach($echoppeAlimentPartiePlante as $p) {
						if ($p["id_fk_echoppe_aliment_partieplante"] == $e["id_echoppe_aliment"]) {
							$partiesPlantes[] = array(
								"prix_echoppe_aliment_partieplante" => $p["prix_echoppe_aliment_partieplante"],
								"nom_type_plante" => $p["nom_type_plante"],
								"nom_type_partieplante" => $p["nom_type_partieplante"],
								"prefix_type_plante" => $p["prefix_type_plante"],
							);
						}
					}
				}
				
				$aliment = array(
					"id_aliment" => $e["id_echoppe_aliment"],
					'id_type_aliment' => $e["id_type_aliment"],
					'nom_systeme_type_aliment' => $e["nom_systeme_type_aliment"],
					'nom' =>$e["nom_type_aliment"],
					'poids' => $e["poids_unitaire_type_aliment"],
					"qualite" => $e["nom_aliment_type_qualite"],
					"bbdf" => $e["bbdf_aliment"],
					"recette" => Bral_Util_Aliment::getNomType($e["type_bbdf_type_aliment"]),
					"prix_1_vente_echoppe_aliment" => $e["prix_1_vente_echoppe_aliment"],
					"prix_2_vente_echoppe_aliment" => $e["prix_2_vente_echoppe_aliment"],
					"prix_3_vente_echoppe_aliment" => $e["prix_3_vente_echoppe_aliment"],
					"unite_1_vente_echoppe_aliment" => $e["unite_1_vente_echoppe_aliment"],
					"unite_2_vente_echoppe_aliment" => $e["unite_2_vente_echoppe_aliment"],
					"unite_3_vente_echoppe_aliment" => $e["unite_3_vente_echoppe_aliment"],
					"commentaire_vente_echoppe_aliment" => $e["commentaire_vente_echoppe_aliment"],
					"prix_minerais" => $minerai,
					"prix_parties_plantes" => $partiesPlantes,
					"nom_region" => $e["nom_region"],
				);
				
				$tab = null;
				$tab[] = $aliment["nom_region"];
				$tab[] = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y',$e["date_echoppe_aliment"]);
				$hobbit = $e["prenom_hobbit"]." ".$e["nom_hobbit"]." (".$e["id_hobbit"].")";
				$hobbit .= "^javascript:ouvrirWin(\"".$this->view->config->url->game."/voir/hobbit/?hobbit=".$e["id_hobbit"]."\");^_self";
				$tab[] = $hobbit;
				if ($e["sexe_hobbit"] == "masculin") {
					$tab[] = $e["nom_masculin_metier"]. "<br>(".$e["x_echoppe"].", ".$e["y_echoppe"].")";
				} else {
					$tab[] = $e["nom_feminin_metier"]. "<br>(".$e["x_echoppe"].", ".$e["y_echoppe"].")";
				}
				$tab[] = "<img src='/public/styles/braldahim_defaut/images/type_aliment/type_aliment_".$aliment["id_type_aliment"].".png' alt=\"".htmlspecialchars($aliment["nom"]) ."\"/>";
				$tab[] = $e["nom_type_aliment"];
				$tab[] = $aliment["qualite"];
				$tab[] = $aliment["recette"];
				$tab[] = '+'.$aliment["bbdf"].'%';
				$tab[] = Bral_Helper_DetailAliment::afficherPrix($aliment);
				$tab[] = Bral_Util_BBParser::bbcodeReplace($aliment["commentaire_vente_echoppe_aliment"]);
				$dhtmlxGrid->addRow($e["id_echoppe_aliment"], $tab);
			}
		}
		
		$total = $echoppeAlimentTable->countByCriteres($this->view->regionSelect, $this->view->alimentSelect);
		$this->view->grid = $dhtmlxGrid->render($total, $posStart);
	}
}