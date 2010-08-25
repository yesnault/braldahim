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
class Bral_Administrationajax_Inserereau extends Bral_Administrationajax_Administrationajax {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Admin : insérer une eau";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Eau");

		$xyzEau = $this->request->get("xyz_eau");
		if ($xyzEau != null) {
			list ($xEau, $yEau, $zEau) = preg_split("/h/", $xyzEau);
			Bral_Util_Controle::getValeurIntVerif($xEau);
			Bral_Util_Controle::getValeurIntVerif($yEau);
			Bral_Util_Controle::getValeurIntVerif($zEau);
		}

		if ($xyzEau != null) {
			$this->view->xEau = $xEau;
			$this->view->yEau = $yEau;
			$this->view->zEau = $zEau;
		}

		$tabTypesEau = null;
		$tabTypesEau["peuProfonde"]["type"] = "peuProfonde";
		$tabTypesEau["peuProfonde"]["selected"] = "selected";
		$tabTypesEau["profonde"]["type"] = "profonde";
		$tabTypesEau["profonde"]["selected"] = "";
		$tabTypesEau["lac"]["type"] = "lac";
		$tabTypesEau["lac"]["selected"] = "";
		$tabTypesEau["mer"]["selected"] = "";
		$tabTypesEau["mer"]["type"] = "mer";

		$this->view->typeEau = $tabTypesEau;
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		//$xyEau = $this->request->get("xy_eau");
		//list ($xEau, $yEau) = preg_split("/h/", $xyEau);
		$this->calculEau();
	}

	function calculEau() {
		
		$xEau = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		$yEau = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_2"));
		$zEau = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_3"));
		$typeEau = $this->request->getPost("valeur_4");
		
		$eauTable = new Eau();

		$data = array(
			"x_eau" => $xEau,	 	 	 	 	 	 	
			"y_eau" => $yEau,	 	
			"z_eau" => $zEau,		 	 	 	 	 	 	
			"type_eau" => $typeEau,	
		);

		$where = "x_eau = ".$xEau. " AND y_eau=".$yEau;
		$nb = $eauTable->delete($where);
		if ($nb == 0) {
			$idEau = $eauTable->insert($data);
		}
		
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["x_position"] = $xEau;
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["y_position"] = $yEau;
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["z_position"] = $zEau;

		$this->view->dataEau = $data;

	}

	/*function calculResultat($xEau, $yEau) {
		$xEau = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		$yEau = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_2"));
		$typeEau = $this->request->getPost("valeur_3");

		$eauTable = new Eau();

		Zend_Loader::loadClass("Ville");
		$villeTable = new Ville();
		$villes = $villeTable->fetchall();
		foreach($villes as $v) {
		for($i = -3; $i<= 3; $i++) {
		for($j = -3; $j<= 3; $j++) {
		$xEau = ($v["x_min_ville"] + (($v["x_max_ville"] - $v["x_min_ville"]) / 2) )+ $i;
		$yEau = ( $v["y_min_ville"] + (($v["y_max_ville"] - $v["y_min_ville"]) / 2) )+ $j;

		$data = array(
		"x_eau" => $xEau,
		"y_eau" => $yEau,
		"z_eau" => 0,
		"id_fk_braldun_eau" => null,
		"date_creation_eau" => date("Y-m-d H:i:s"),
		"id_fk_type_qualite_eau"  => null,
		"type_eau" => $typeEau,
		"est_visible_eau" => 'oui',
		);

		$where = "x_eau = ".$xEau. " AND y_eau=".$yEau;
		$eauTable->delete($where);
		$idEau = $eauTable->insert($data);
		}
		}
		}

		$this->view->dataEau = $data;
		$this->view->dataEau["id_eau"] = $idEau;
		}
		*/

	function getListBoxRefresh() {
		return array("box_vue");
	}
}