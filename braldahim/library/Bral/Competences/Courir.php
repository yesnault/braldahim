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
class Bral_Competences_Courir extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass("Charrette");

		$charretteTable = new Charrette();
		$nombreCharrette = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);

		/*
		 * Si le hobbit n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa = false) {
			return;
		}

		$this->view->possedeCharrette = false;
		if ($nombreCharrette == 1) {
			$this->view->possedeCharrette = true;
				
			Zend_Loader::loadClass("Bral_Util_Charrette");
			$this->view->courirPossible = Bral_Util_Charrette::calculCourrirChargerPossible($this->view->user->id_hobbit);
			if ($this->view->courirPossible == false) {
				return;
			}
		} else if ($nombreCharrette > 1) {
			throw new Zend_Exception(get_class($this)." NB Charrette invalide idh:".$this->view->user->id_hobbit);
		}
			
		$this->view->estEngage = false;
		if ($this->view->user->est_engage_hobbit == "oui") {
			$this->view->courirPossible = false;
			$this->view->estEngage = true;
			return;
		}

		$this->view->courirPossible = false;

		$environnement = Bral_Util_Commun::getEnvironnement($this->view->user->x_hobbit, $this->view->user->y_hobbit);

		$this->view->nb_cases = 1;
		if ($environnement == "plaine") {
			$this->distance = 12;
		} else {
			$this->distance = 6;
		}

		$this->x_min = $this->view->user->x_hobbit - $this->distance;
		$this->x_max = $this->view->user->x_hobbit + $this->distance;
		$this->y_min = $this->view->user->y_hobbit - $this->distance;
		$this->y_max = $this->view->user->y_hobbit + $this->distance;

		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->x_min, $this->y_min, $this->x_max, $this->y_max);

		$this->tabValidationPalissade = null;
		for ($j = $this->distance; $j >= -$this->distance; $j--) {
			for ($i = -$this->distance; $i <= $this->distance; $i++) {
				$x = $this->view->user->x_hobbit + $i;
				$y = $this->view->user->y_hobbit + $j;
				$this->tabValidationPalissade[$x][$y] = true;
			}
		}
		foreach($palissades as $p) {
			$this->tabValidationPalissade[$p["x_palissade"]][$p["y_palissade"]] = false;
		}

		$defautChecked = false;

		for ($j = $this->view->nb_cases; $j >= -$this->view->nb_cases; $j --) {
			$change_level = true;
			for ($i = -$this->view->nb_cases; $i <= $this->view->nb_cases; $i ++) {
				$x = $this->view->user->x_hobbit + $i;
				$y = $this->view->user->y_hobbit + $j;

				if ($j == 1 && $i == 0) {
					$display = " Vers le Nord";
				} elseif ($j == -1 && $i == 0) {
					$display = " Vers le Sud";
				} elseif ($j == 1 && $i == 1) {
					$display = " Vers le Nord Est";
				} elseif ($j == 1 && $i == -1) {
					$display = " Vers le Nord Ouest";
				} elseif ($j == 0 && $i == 1) {
					$display = " Vers l'Est";
				} elseif ($j == 0 && $i == -1) {
					$display = " Vers l'Ouest";
				} elseif ($j == -1 && $i == 1) {
					$display = " Vers le Sud Est";
				} elseif ($j == -1 && $i == -1) {
					$display = " Vers le Sud Ouest";
				} else {
					$display = "";
				}
					
				if (($j == 0 && $i == 0) == false) { // on n'affiche pas de boutons dans la case du milieu
					$valid = true;
				} else {
					$valid = false;
				}
					
				if ($x < $this->view->config->game->x_min || $x > $this->view->config->game->x_max
				|| $y < $this->view->config->game->y_min || $y > $this->view->config->game->y_max ) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
				}

				// on regarde s'il n'y a pas de palissade
				if ($this->tabValidationPalissade[$x][$y] === false) {
					$valid = false;
				}

				if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
					$this->view->courirPossible = true;
				} else {
					$default = "";
				}
					
				$tab[] = array ("x_offset" => $i,
			 	"y_offset" => $j,
			 	"default" => $default,
			 	"display" => $display,
			 	"change_level" => $change_level, // nouvelle ligne dans le tableau
				"valid" => $valid);
					
				$tabValidation[$i][$j] = $valid;

				if ($change_level) {
					$change_level = false;
				}
			}
		}
		$this->view->tableau = $tab;
		$this->tableauValidation = $tabValidation;
		$this->view->distance = $this->distance;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = split("h", $x_y);

		if ($offset_x < -$this->view->nb_cases || $offset_x > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this)." Deplacement X impossible : ".$offset_x);
		}

		if ($offset_y < -$this->view->nb_cases || $offset_y > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this)." Deplacement Y impossible : ".$offset_y);
		}

		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this)." Deplacement XY impossible : ".$offset_x.$offset_y);
		}

		$this->calculPalissade($offset_x, $offset_y);

		$this->view->user->x_hobbit = $this->view->user->x_hobbit + $this->offset_x_calcul;
		$this->view->user->y_hobbit = $this->view->user->y_hobbit + $this->offset_y_calcul;

		$id_type = $this->view->config->game->evenements->type->deplacement;
		$details = "[h".$this->view->user->id_hobbit."] a couru";
		$this->setDetailsEvenement($details, $id_type);
		$this->setEvenementQueSurOkJet1(false);

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculFinMatchSoule();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		$tab = array("box_vue", "box_lieu");
		if ($this->view->user->est_soule_hobbit == "oui") {
			$tab[] = "box_soule";
		}
		return $this->constructListBoxRefresh($tab);
	}

	private function calculPalissade($offset_x, $offset_y) {

		$x = $this->view->user->x_hobbit;
		$y = $this->view->user->y_hobbit;

		$k = 0;
		$this->view->palissadeRencontree = false;

		for ($i = 1; $i <= $this->distance; $i++) {
			if ($this->tabValidationPalissade[$x + $i * $offset_x][$y + $i * $offset_y] == false
			|| $x + $i*$offset_x < $this->view->config->game->x_min
			|| $x + $i*$offset_x > $this->view->config->game->x_max
			|| $y + $i*$offset_y < $this->view->config->game->y_min
			|| $y + $i*$offset_y > $this->view->config->game->y_max) {
				$k = $i-1;
				$this->view->palissadeRencontree = true;
				break;
			} else {
				$k = $i;
			}
		}
		if ($k <> 0 ) {
			$this->offset_x_calcul = $k * $offset_x;
			$this->offset_y_calcul = $k * $offset_y;
		} else {
			$this->offset_x_calcul = $offset_x;
			$this->offset_y_calcul = $offset_y;
		}
	}
}