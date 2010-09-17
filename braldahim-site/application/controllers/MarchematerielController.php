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
class MarchematerielController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
		Zend_Loader::loadClass('EchoppeMateriel');
		Zend_Loader::loadClass('EchoppeMaterielMinerai');
		Zend_Loader::loadClass('EchoppeMaterielPartiePlante');
		Zend_Loader::loadClass('Bral_Xml_GridDhtmlx');
		Zend_Loader::loadClass('Bral_Helper_DetailMateriel');
		Zend_Loader::loadClass('Bral_Util_BBParser');
		Zend_Loader::loadClass('Bral_Helper_Tooltip');
		
		$f = new Zend_Filter_StripTags();
		
		$regionSelect = intval($f->filter($this->_request->get("regionselect")));
		if ($regionSelect <= 0) {
			$regionSelect = -1;
		}
		$this->view->regionSelect = $regionSelect;
		
		$materielSelect = intval($f->filter($this->_request->get("materielselect")));
		if ($materielSelect <= 0) {
			$materielSelect = -1;
		}
		$this->view->materielSelect = $materielSelect;
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
		
		Zend_Loader::loadClass('TypeMateriel');
		$typeMateriel = new TypeMateriel();
		$rowset = $typeMateriel->fetchAll(null, "nom_type_materiel");
		
		$materiels[-1] = "Tous";
		foreach ($rowset as $r) {
			$materiels[$r["id_type_materiel"]] = $r["nom_type_materiel"];
		}
		$this->view->materiels = $materiels;
		
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
				$ordre = "date_echoppe_materiel ".$direct;
				break;
			case 2:
				$ordre = "id_braldun ".$direct;
				break;
			case 3:
				$ordre = "nom_systeme_metier ".$direct;
				break;
			case 4:
				$ordre = "nom_type_materiel ".$direct;
				break;
			case 5:
				$ordre = "id_type_materiel ".$direct;
				break;
		}
		
		$echoppeMaterielTable = new EchoppeMateriel();
		$materiels = $echoppeMaterielTable->findByCriteres($ordre, $posStart, $count, $this->view->regionSelect, $this->view->materielSelect);
		
		$idMateriels = null;
		foreach ($materiels as $e) {
			$idMateriels[] = $e["id_echoppe_materiel"];
		}
		
		if ($idMateriels != null && count($idMateriels) > 0) {
			$echoppeMaterielMineraiTable = new EchoppeMaterielMinerai();
			$echoppeMaterielMinerai = $echoppeMaterielMineraiTable->findByIdsMateriel($idMateriels);
				
			$echoppeMaterielPartiePlanteTable = new EchoppeMaterielPartiePlante();
			$echoppeMaterielPartiePlante = $echoppeMaterielPartiePlanteTable->findByIdsMateriel($idMateriels);
		}
		
		$dhtmlxGrid = new Bral_Xml_GridDhtmlx();
		
		if ($idMateriels != null && count($materiels) > 0) {
			foreach($materiels as $e) {
			
				$minerai = null;
				if (count($echoppeMaterielMinerai) > 0) {
					foreach($echoppeMaterielMinerai as $r) {
						if ($r["id_fk_echoppe_materiel_minerai"] == $e["id_echoppe_materiel"]) {
							$minerai[] = array(
								"prix_echoppe_materiel_minerai" => $r["prix_echoppe_materiel_minerai"],
								"nom_type_minerai" => $r["nom_type_minerai"],
							);
						}
					}
				}
				
				$partiesPlantes = null;
				if (count($echoppeMaterielPartiePlante) > 0) {
					foreach($echoppeMaterielPartiePlante as $p) {
						if ($p["id_fk_echoppe_materiel_partieplante"] == $e["id_echoppe_materiel"]) {
							$partiesPlantes[] = array(
								"prix_echoppe_materiel_partieplante" => $p["prix_echoppe_materiel_partieplante"],
								"nom_type_plante" => $p["nom_type_plante"],
								"nom_type_partieplante" => $p["nom_type_partieplante"],
								"prefix_type_plante" => $p["prefix_type_plante"],
							);
						}
					}
				}
				
				$materiel = array(
					"id_materiel" => $e["id_echoppe_materiel"],
					'id_type_materiel' => $e["id_type_materiel"],
					'nom_systeme_type_materiel' => $e["nom_systeme_type_materiel"],
					'nom' =>$e["nom_type_materiel"],
					'capacite' => $e["capacite_type_materiel"], 
					'durabilite' => $e["durabilite_type_materiel"], 
					'usure' => $e["usure_type_materiel"], 
					'poids' => $e["poids_type_materiel"], 
					"prix_1_vente_echoppe_materiel" => $e["prix_1_vente_echoppe_materiel"],
					"prix_2_vente_echoppe_materiel" => $e["prix_2_vente_echoppe_materiel"],
					"prix_3_vente_echoppe_materiel" => $e["prix_3_vente_echoppe_materiel"],
					"unite_1_vente_echoppe_materiel" => $e["unite_1_vente_echoppe_materiel"],
					"unite_2_vente_echoppe_materiel" => $e["unite_2_vente_echoppe_materiel"],
					"unite_3_vente_echoppe_materiel" => $e["unite_3_vente_echoppe_materiel"],
					"commentaire_vente_echoppe_materiel" => $e["commentaire_vente_echoppe_materiel"],
					"prix_minerais" => $minerai,
					"prix_parties_plantes" => $partiesPlantes,
					"nom_region" => $e["nom_region"],
				);
				
				$tab = null;
				$tab[] = $materiel["nom_region"];
				$tab[] = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y',$e["date_echoppe_materiel"]);
				$braldun = $e["prenom_braldun"]." ".$e["nom_braldun"]." (".$e["id_braldun"].")";
				$braldun .= "^javascript:ouvrirWin(\"".$this->view->config->url->game."/voir/braldun/?braldun=".$e["id_braldun"]."\");^_self";
				$tab[] = $braldun;
				if ($e["sexe_braldun"] == "masculin") {
					$tab[] = $e["nom_masculin_metier"]. "<br />(".$e["x_echoppe"].", ".$e["y_echoppe"].")";
				} else {
					$tab[] = $e["nom_feminin_metier"]. "<br />(".$e["x_echoppe"].", ".$e["y_echoppe"].")";
				}
				$tab[] = "<div class='braltip'>".Bral_Helper_DetailMateriel::afficherTooltip($materiel)."<img src='/public/styles/braldahim_defaut/images/type_materiel/type_materiel_".$materiel["id_type_materiel"].".png' alt=\"".htmlspecialchars($materiel["nom"]) ."\"/></div>";
				$tab[] = $e["nom_type_materiel"];
				$tab[] = Bral_Helper_DetailMateriel::afficherPrix($materiel);
				$tab[] = Bral_Util_BBParser::bbcodeReplace($materiel["commentaire_vente_echoppe_materiel"]);
				$dhtmlxGrid->addRow($e["id_echoppe_materiel"], $tab);
			}
		}
		
		$total = $echoppeMaterielTable->countByCriteres($this->view->regionSelect, $this->view->materielSelect);
		$this->view->grid = $dhtmlxGrid->render($total, $posStart);
	}
}