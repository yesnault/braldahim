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
class AdministrationController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->view->md5_value = "";
		if ($this->_request->get("md5_source") != "") {
			$this->view->md5_source = $this->_request->get("md5_source");
			$this->view->md5_value = md5($this->_request->get("md5_source"));
		}
//		$this->sqlAction();
		$this->render();
	}


	function sqlAction() {
		$nom_image="image.png";
		$image=imagecreatefrompng($nom_image);

		Zend_Loader::loadClass("Eau");
		$eauTable = new Eau();
		$eauTable->delete(false);

		Zend_Loader::loadClass("Route");
		$routeTable = new Route();
		
		Zend_Loader::loadClass("Bosquet");
		$bosquetTable = new Bosquet();

		Zend_Loader::loadClass("Buisson");
		$buissonTable = new Buisson();

		Zend_Loader::loadClass("Monstre");
		$monstreTable = new Monstre();

		Zend_Loader::loadClass("Plante");
		$planteTable = new Plante();

		Zend_Loader::loadClass("Filon");
		$filonTable = new Filon();

		for ($x = 0; $x < 1600; $x++) {
			for ($y = 0; $y <1000; $y++) {
				$couleur=imagecolorat($image,$x,$y);
				$couleursRVB=imagecolorsforindex($image,$couleur);
				$typeEau = null;

				$xEau = $x - 800;
				$yEau = 500 - $y;

				if ($couleursRVB["red"] == 255 && $couleursRVB["green"] == 0 && $couleursRVB["blue"] == 0) { // rouge, fleuve
					printf("fleuve : x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $xEau, $yEau, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
					$typeEau = "profonde";
				} elseif ($couleursRVB["red"] == 0 && $couleursRVB["green"] == 0 && $couleursRVB["blue"] == 255) { // bleu, lac
					printf("lac : x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $xEau, $yEau, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
					$typeEau = "lac";
				} elseif ($couleursRVB["red"] == 255 && $couleursRVB["green"] == 255 && $couleursRVB["blue"] == 0) { // jaune, mer
					printf("lac : x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $xEau, $yEau, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
					$typeEau = "mer";
				} elseif ($couleursRVB["red"] == 0 && $couleursRVB["green"] == 255 && $couleursRVB["blue"] == 255) { // gue
					printf("lac : x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $xEau, $yEau, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
					$typeEau = "gue";
				} else {
					//printf("x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $x, $y, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
				}

				if ($typeEau != null) {
					$route = $routeTable->findByCaseHorsBalise($xEau, $yEau, 0);
					if ($route == null) {
						$data = array(
						"x_eau" => $xEau, 	 	 	
						"y_eau" => $yEau,
						"z_eau" => 0,		 	 	 	 	 	 	
						"type_eau" => $typeEau,	
						);
						$eauTable->insert($data);
						
						$this->deleteEltEau($xEau, $yEau, $routeTable, $bosquetTable, $buissonTable, $monstreTable, $planteTable, $filonTable);
					}
				}
			}
		}

		$where = "type_eau not like 'gue'";
		$eaux = $eauTable->fetchAll($where);

		foreach($eaux as $e) {

			for ($x = -1; $x <= 1; $x++) {
				for ($y = -1; $y <=1; $y++) {
					$xEau = $e["x_eau"] + $x;
					$yEau = $e["y_eau"] + $y;
					$eau = $eauTable->findByCase($xEau, $yEau, 0);
					$route = $routeTable->findByCaseHorsBalise($xEau, $yEau, 0);
					if ($eau == null && $route == null && 
					$xEau > -800 && $xEau < 800 &&
					$yEau > -500 && $yEau < 500 
						) {
						$data = array(
							"x_eau" => $xEau,
							"y_eau" => $yEau,
							"z_eau" => 0,		 	 	 	 	 	 	
							"type_eau" => "peuprofonde",	
						);
						$eauTable->insert($data);

					//	$this->deleteEltEau($xEau, $yEau, $routeTable, $bosquetTable, $buissonTable, $monstreTable, $planteTable, $filonTable);
					}
				}
			}
		}
	}

	private function deleteEltEau($xEau, $yEau, $routeTable, $bosquetTable, $buissonTable, $monstreTable, $planteTable, $filonTable) {
		$where = "x_route=".$xEau." and y_route=".$yEau. " and z_route=0 and type_route like 'type_route'"; // suppression des balises
		$routeTable->delete($where);

		$where = "x_bosquet=".$xEau." and y_bosquet=".$yEau. " and z_bosquet=0 "; // suppression des bosquets
		$bosquetTable->delete($where);

		$where = "x_buisson=".$xEau." and y_buisson=".$yEau. " and z_buisson=0 "; // suppression des buissons
		$buissonTable->delete($where);

		$where = "x_monstre=".$xEau." and y_monstre=".$yEau. " and z_monstre=0 "; // suppression des monstres
		$monstreTable->delete($where);

		$where = "x_plante=".$xEau." and y_plante=".$yEau. " and z_plante=0 "; // suppression des plantes
		$planteTable->delete($where);

		$where = "x_filon=".$xEau." and y_filon=".$yEau. " and z_filon=0 "; // suppression des filons
		$filonTable->delete($where);
	}

	function md5Action() {
		$this->render();
	}
}
