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

	function calcul($hobbit, $selection = null, $construireRoute = false) {
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass('Route');
		Zend_Loader::loadClass("Bosquet");

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

		$retour["x_min"] = null;
		$retour["x_max"] = null;
		$retour["y_min"] = null;
		$retour["y_max"] = null;

		$zoneTable = new Zone();
		$zone = $zoneTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->z_hobbit);
		unset($zoneTable);

		// La requete ne doit renvoyer qu'une seule case
		if (count($zone) == 1) {
			$case = $zone[0];
		} else {
			throw new Zend_Exception(get_class($this)."::prepareFormulaire : Nombre de case invalide");
		}
		unset($zone);

		$bosquetTable = new Bosquet();
		$bosquets = $bosquetTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->z_hobbit);

		if (count($bosquets) == 1) {
			$environnement = $bosquets[0]["description_type_bosquet"];
			$case["nom_systeme_environnement"] = "bosquet";
		} else {
			$environnement = $case["nom_environnement"];
		}

		$routeTable = new Route();
		$routes = $routeTable->findByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->z_hobbit);

		if (count($routes) == 1) {
			$retour["estSurRoute"] = true;
		}

		if ($hobbit->est_engage_hobbit == "oui") {
			$retour["estEngage"] = true;
		}

		/*
		 * Si le hobbit n'a pas de PA, on ne fait aucun traitement
		 */
		$assezDePa = $this->calculNbPa($hobbit, $case["nom_systeme_environnement"], $retour["estSurRoute"], $construireRoute);
		if ($assezDePa == false || $hobbit->activation == false) {
			$retour["assezDePa"] = false;
			return $retour;
		} else {
			$retour["assezDePa"] = true;
		}

		$marcherPossible = false;

		$this->distance = $this->nb_cases;
		$x_min = $hobbit->x_hobbit - $this->distance;
		$x_max = $hobbit->x_hobbit + $this->distance;
		$y_min = $hobbit->y_hobbit - $this->distance;
		$y_max = $hobbit->y_hobbit + $this->distance;

		Zend_Loader::loadClass("Bral_Util_Dijkstra");
		$dijkstra = new Bral_Util_Dijkstra();
		$dijkstra->calcul($this->nb_cases, $hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->z_hobbit);

		$defautChecked = false;
		$config = Zend_Registry::get('config');
		$numero = -1;
		for ($j = $this->nb_cases; $j >= -$this->nb_cases; $j --) {
			$change_level = true;
			for ($i = -$this->nb_cases; $i <= $this->nb_cases; $i ++) {
				$x = $hobbit->x_hobbit + $i;
				$y = $hobbit->y_hobbit + $j;

				$display = $x;
				$display .= " ; ";
				$display .= $y;
					
				$numero++;
					
				if (($j == 0 && $i == 0) == false) { // on n'affiche pas de boutons dans la case du milieu
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
			list ($selection_x, $selection_y) = split("h", $selection);
			if ($tab[$selection]["valid"] == true) {
				$tab[$selectionChecked]["default"] = "";
				$tab[$selection]["default"] = "checked";
			}
		}

		$retour["environnement"] = $environnement;
		$retour["nb_cases"] = $this->nb_cases;
		$retour["effetMot"] = $this->effetMot;
		$retour["nb_pa"] = $this->nb_pa;

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
	 * sur lequel le hobbit marche :
	 * Plaine : 1 PA jusqu'a 2 cases
	 * Bosquet : 1 PA pour 1 case
	 * Marais : 2 PA pour 1 case
	 * Montagneux : 2 PA pour 1 case
	 * Caverneux : 1 PA pour 1 case
	 */
	public function calculNbPa($hobbit, $nom_systeme_environnement, $estSurRoute, $construireRoute) {
		$this->effetMot = false;

		switch($nom_systeme_environnement) {
			case "plaine" :
				$this->nb_cases = 2;
				$this->nb_pa = 1;
				break;
			case "marais" :
				$this->nb_cases = 1;
				$this->nb_pa = 2;
				if (Bral_Util_Commun::getEquipementByNomSystemeMot($hobbit->id_hobbit, "mot_p") != null) {
					$this->effetMot = true;
					$this->nb_pa = 1;
				}
				break;
			case "montagne" :
				$this->nb_cases = 1;
				$this->nb_pa = 2;
				if (Bral_Util_Commun::getEquipementByNomSystemeMot($hobbit->id_hobbit, "mot_c") != null) {
					$this->effetMot = true;
					$this->nb_pa = 1;
				}
				break;
			case "bosquet" :
				$this->nb_cases = 1;
				$this->nb_pa = 1;
				if (Bral_Util_Commun::getEquipementByNomSystemeMot($hobbit->id_hobbit, "mot_t") != null) {
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

		if ($hobbit->est_engage_hobbit == "oui") {
			$this->nb_cases = 1;
			$this->nb_pa = 2;
		}

		if ($construireRoute) {
			$this->nb_cases = 1;
			$this->nb_pa = 0;
		}

		if ($hobbit->pa_hobbit - $this->nb_pa < 0) {
			return false;
		} else {
			return true;
		}
	}
}