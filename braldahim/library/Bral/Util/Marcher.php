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
class Bral_Util_Marcher {

	function __construct() {
	}

	function calcul($braldun, $selection = null, $construireRoute = false) {
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('Route');
		Zend_Loader::loadClass('Bosquet');
		Zend_Loader::loadClass('Eau');

		$retour["environnement"] = null;
		$retour["nb_cases"] = null;
		$retour["nb_pa"] = null;
		$retour["tableau"] = null;
		$retour["tableauValidation"] = null;
		$retour["assezDePa"] = false;
		$retour["effetMot"] = false;
		$retour["marcherPossible"] = false;
		$retour["tableauValidation"] = null;
		$retour["tableauValidationXY"] = null;
		$retour["estEngage"] = false;
		$retour["estSurRoute"] = false;
		$retour["estSurEau"] = false;

		$retour["x_min"] = null;
		$retour["x_max"] = null;
		$retour["y_min"] = null;
		$retour["y_max"] = null;

		$zoneTable = new Zone();
		$zone = $zoneTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);
		unset($zoneTable);

		// La requete ne doit renvoyer qu'une seule case
		if (count($zone) == 1) {
			$case = $zone[0];
		} else {
			throw new Zend_Exception(get_class($this)."::prepareFormulaire : Nombre de case invalide");
		}
		unset($zone);

		$bosquetTable = new Bosquet();
		$bosquets = $bosquetTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

		if (count($bosquets) == 1) {
			$environnement = $bosquets[0]["description_type_bosquet"];
			$case["nom_systeme_environnement"] = "bosquet";
		} else {
			$environnement = $case["nom_environnement"];
		}

		$routeTable = new Route();
		$routes = $routeTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

		if (count($routes) == 1 && $routes[0]["est_visible_route"] == "oui") {
			$retour["estSurRoute"] = true;
		}

		if ($braldun->est_engage_braldun == "oui") {
			$retour["estEngage"] = true;
		}

		$eauTable = new Eau();
		$eaux = $eauTable->findByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

		if (count($eaux) == 1) {
			$retour["estSurEau"] = true;
		}

		/*
		 * Si le Braldûn n'a pas de PA, on ne fait aucun traitement
		 */
		$assezDePa = $this->calculNbPa($braldun, $case["nom_systeme_environnement"], $retour["estSurRoute"], $construireRoute, $retour["estSurEau"]);
		$retour["nb_cases"] = $this->nb_cases;
		$retour["effetMot"] = $this->effetMot;
		$retour["nb_pa"] = $this->nb_pa;
		if ($assezDePa == false || $braldun->activation == false) {
			$retour["assezDePa"] = false;
			return $retour;
		} else {
			$retour["assezDePa"] = true;
		}

		$marcherPossible = false;

		$this->distance = $this->nb_cases;
		$x_min = $braldun->x_braldun - $this->distance;
		$x_max = $braldun->x_braldun + $this->distance;
		$y_min = $braldun->y_braldun - $this->distance;
		$y_max = $braldun->y_braldun + $this->distance;

		Zend_Loader::loadClass("Bral_Util_Dijkstra");
		$dijkstra = new Bral_Util_Dijkstra();
		$dijkstra->calcul($this->nb_cases, $braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

		$defautChecked = false;
		$config = Zend_Registry::get('config');
		$numero = -1;
		for ($j = $this->nb_cases; $j >= -$this->nb_cases; $j --) {
			$change_level = true;
			for ($i = -$this->nb_cases; $i <= $this->nb_cases; $i ++) {
				$x = $braldun->x_braldun + $i;
				$y = $braldun->y_braldun + $j;

				$display = $x;
				$display .= " ; ";
				$display .= $y;
					
				$numero++;
					
				if (($j == 0 && $i == 0) == false || $construireRoute == true) { // on n'affiche pas de boutons dans la case du milieu
					$valid = true;
				} else {
					$valid = false;
				}
					
				if ($x < $config->game->x_min || $x > $config->game->x_max
				|| $y < $config->game->y_min || $y > $config->game->y_max ) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
				}

				// on regarde la distance
				if ($dijkstra->getDistance($numero) > $this->nb_cases) {
					$valid = false;
				}

				if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
					$marcherPossible = true;
					$selectionChecked = $i."h".$j;
				} else {
					$default = "";
				}
					
				$tab[$i."h".$j] = array ("x_offset" => $i,
				 	"y_offset" => $j,
				 	"default" => $default,
				 	"display" => $display,
				 	"change_level" => $change_level, // nouvelle ligne dans le tableau
					"valid" => $valid
				);
					
				$tabValidation[$i][$j] = $valid;
				$tabValidationXY[$x][$y] = array("valid" => $valid,
			 									 "offset" => $i."h".$j,
				);

				if ($change_level) {
					$change_level = false;
				}
			}
		}

		if ($selection != null) {
			list ($selection_x, $selection_y) = preg_split("/h/", $selection);
			if ($tab[$selection]["valid"] == true) {
				$tab[$selectionChecked]["default"] = "";
				$tab[$selection]["default"] = "checked";
			}
		}

		$retour["environnement"] = $environnement;

		$retour["tableau"] = $tab;
		$retour["tableauValidation"] = $tabValidation;
		$retour["tableauValidationXY"] = $tabValidationXY;
		$retour["marcherPossible"] = $marcherPossible;

		$retour["x_min"] = $x_min;
		$retour["x_max"] = $x_max;
		$retour["y_min"] = $y_min;
		$retour["y_max"] = $y_max;

		return $retour;

	}

	/* Pour marcher, le nombre de PA utilise est variable suivant l'environnement
	 * sur lequel le Braldûn marche :
	 * Plaine : 1 PA jusqu'a 2 cases
	 * Bosquet : 1 PA pour 1 case
	 * Marais : 2 PA pour 1 case
	 * Montagneux : 2 PA pour 1 case
	 * Caverneux : 1 PA pour 1 case
	 */
	private function calculNbPa($braldun, $nom_systeme_environnement, $estSurRoute, $construireRoute, $estSurEau) {
		$this->effetMot = false;

		switch($nom_systeme_environnement) {
			case "plaine" :
				$this->nb_cases = 2;
				$this->nb_pa = 1;
				break;
			case "marais" :
				$this->nb_cases = 1;
				$this->nb_pa = 2;
				if (Bral_Util_Commun::getEquipementByNomSystemeMot($braldun->id_braldun, "mot_p") != null) {
					$this->effetMot = true;
					$this->nb_pa = 1;
				}
				break;
			case "montagne" :
				$this->nb_cases = 1;
				$this->nb_pa = 2;
				if (Bral_Util_Commun::getEquipementByNomSystemeMot($braldun->id_braldun, "mot_c") != null) {
					$this->effetMot = true;
					$this->nb_pa = 1;
				}
				break;
			case "bosquet" :
				$this->nb_cases = 1;
				$this->nb_pa = 1;
				if (Bral_Util_Commun::getEquipementByNomSystemeMot($braldun->id_braldun, "mot_t") != null) {
					$this->effetMot = true;
					$this->nb_cases = 2;
				}
				break;
			case "caverne" :
				$this->nb_cases = 1;
				$this->nb_pa = 1;
				break;
			case "gazon" :
				$this->nb_cases = 1;
				$this->nb_pa = 1;
				break;
			default:
				throw new Zend_Exception(get_class($this)."::environnement invalide :".$this->nom_systeme_environnement);
		}

		if ($estSurRoute) {
			$this->nb_cases = 3;
			$this->nb_pa = 1;
		}
		
		if ($estSurEau) {
			// 2 PA + 1 Pa si charrette + 1 PA/tranche de 10Kg porté (équipé +laban)
			Zend_Loader::loadClass("Charrette");
			$charretteTable = new Charrette();
			$this->nb_cases = 1;
			$this->nb_pa = 2;
			$nombre = $charretteTable->countByIdBraldun($braldun->id_braldun);
			if ($nombre > 0) {
				$this->nb_pa = $this->nb_pa + 1;
			}
			
			$n = intval($braldun->poids_transporte_braldun / 10);
			if ($n > 0) {
				$this->nb_pa = $this->nb_pa + $n;
			}
		}

		if ($braldun->est_engage_braldun == "oui") {
			$this->nb_cases = 1;
			$this->nb_pa = 2;
		}

		if ($construireRoute) {
			$this->nb_cases = 1;
			$this->nb_pa = 0;
		}
			
		if ($braldun->bm_marcher_braldun != 0) {
			$this->nb_pa = $this->nb_pa - $braldun->bm_marcher_braldun;
		}

		if ($braldun->pa_braldun - $this->nb_pa < 0) {
			return false;
		} else {
			return true;
		}
	}
}