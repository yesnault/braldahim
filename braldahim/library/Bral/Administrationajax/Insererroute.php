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
class Bral_Administrationajax_Insererroute extends Bral_Administrationajax_Administrationajax {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Admin : insérer un route";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Route");

		$xyzRoute = $this->request->get("xyz_route");
		if ($xyzRoute != null) {
			list ($xRoute, $yRoute, $zRoute) = preg_split("/h/", $xyzRoute);
			Bral_Util_Controle::getValeurIntVerif($xRoute);
			Bral_Util_Controle::getValeurIntVerif($yRoute);
			Bral_Util_Controle::getValeurIntVerif($zRoute);
		}

		if ($xyzRoute != null) {
			$this->view->xRoute = $xRoute;
			$this->view->yRoute = $yRoute;
			$this->view->zRoute = $zRoute;
		}

		$tabTypesRoute = null;
		$tabTypesRoute["route"]["type"] = "route";
		$tabTypesRoute["route"]["selected"] = "";
		$tabTypesRoute["ville"]["type"] = "ville";
		$tabTypesRoute["ville"]["selected"] = "selected";
		$tabTypesRoute["balise"]["type"] = "balise";
		$tabTypesRoute["balise"]["selected"] = "";

		$this->view->typeRoute = $tabTypesRoute;
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		//$xyRoute = $this->request->get("xy_route");
		//list ($xRoute, $yRoute) = preg_split("/h/", $xyRoute);
		$this->calculRoute();
	}

	function calculRoute() {
		
		$xRoute = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		$yRoute = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_2"));
		$zRoute = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_3"));
		$typeRoute = $this->request->getPost("valeur_4");
		
		$routeTable = new Route();

		$data = array(
			"x_route" => $xRoute,	 	 	 	 	 	 	
			"y_route" => $yRoute,	 	
			"z_route" => $zRoute,		 	 	 	 	 	 	
			"id_fk_braldun_route" => null,	 	 	 	 	 	
			"date_creation_route" => date("Y-m-d H:i:s"), 	 	 	 	 	 	
			"id_fk_type_qualite_route"  => null, 	 	 	 	 	 	
			"type_route" => $typeRoute,	
			"est_visible_route" => 'oui',
			"id_fk_numero_route" => null,
		);

		$where = "x_route = ".$xRoute. " AND y_route=".$yRoute;
		$nb = $routeTable->delete($where);
		if ($nb == 0) {
			$idRoute = $routeTable->insert($data);
		}
		
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["x_position"] = $xRoute;
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["y_position"] = $yRoute;
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["z_position"] = $zRoute;

		$this->view->dataRoute = $data;

	}

	/*function calculResultat($xRoute, $yRoute) {
		$xRoute = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		$yRoute = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_2"));
		$typeRoute = $this->request->getPost("valeur_3");

		$routeTable = new Route();

		Zend_Loader::loadClass("Ville");
		$villeTable = new Ville();
		$villes = $villeTable->fetchall();
		foreach($villes as $v) {
		for($i = -3; $i<= 3; $i++) {
		for($j = -3; $j<= 3; $j++) {
		$xRoute = ($v["x_min_ville"] + (($v["x_max_ville"] - $v["x_min_ville"]) / 2) )+ $i;
		$yRoute = ( $v["y_min_ville"] + (($v["y_max_ville"] - $v["y_min_ville"]) / 2) )+ $j;

		$data = array(
		"x_route" => $xRoute,
		"y_route" => $yRoute,
		"z_route" => 0,
		"id_fk_braldun_route" => null,
		"date_creation_route" => date("Y-m-d H:i:s"),
		"id_fk_type_qualite_route"  => null,
		"type_route" => $typeRoute,
		"est_visible_route" => 'oui',
		);

		$where = "x_route = ".$xRoute. " AND y_route=".$yRoute;
		$routeTable->delete($where);
		$idRoute = $routeTable->insert($data);
		}
		}
		}

		$this->view->dataRoute = $data;
		$this->view->dataRoute["id_route"] = $idRoute;
		}
		*/

	function getListBoxRefresh() {
		return array("box_vue");
	}
}