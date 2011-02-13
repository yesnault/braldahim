<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Construirebatiment extends Bral_Communaute_Communaute {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		if ($this->view->user->rangCommunaute > Bral_Util_Communaute::ID_RANG_TENANCIER) {
			throw new Zend_Exception(get_class($this)." Vous n'êtes pas tenancier de la communauté ". $this->view->user->rangCommunaute);
		}

		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('Palissade');

		$this->distance = 3;
		$this->view->x_min = $this->view->user->x_braldun - $this->distance;
		$this->view->x_max = $this->view->user->x_braldun + $this->distance;
		$this->view->y_min = $this->view->user->y_braldun - $this->distance;
		$this->view->y_max = $this->view->user->y_braldun + $this->distance;

		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);

		$defautChecked = false;

		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			$change_level = true;
			for ($i = -$this->distance; $i <= $this->distance; $i ++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;
				$z = $this->view->user->z_braldun;
					
				$display = $x;
				$display .= " ; ";
				$display .= $y;
					
				if (($j == 0 && $i == 0) == false) { // on n'affiche pas de boutons dans la case du milieu
					$valid = true;
				} else {
					$valid = false;
				}
					
				if ($x < $this->view->config->game->x_min || $x > $this->view->config->game->x_max
				|| $y < $this->view->config->game->y_min || $y > $this->view->config->game->y_max ) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
				}

				foreach($lieux as $l) {
					if ($x == $l["x_lieu"] && $y == $l["y_lieu"] && $z == $l["z_lieu"]) {
						$valid = false;
						break;
					}
				}

				foreach($palissades as $p) {
					if ($x == $p["x_palissade"] && $y == $p["y_palissade"] && $z == $p["z_palissade"]) {
						$valid = false;
						break;
					}
				}
					
				if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
					$this->view->monterPalissadeOk = true;
				} else {
					$default = "";
				}

				$tab[] = array ("x_offset" => $i,
				 	"y_offset" => $j,
				 	"default" => $default,
				 	"display" => $display,
				 	"change_level" => $change_level, // nouvelle ligne dans le tableau
					"valid" => $valid
				);

				$tabValidation[$i][$j] = $valid;
					
				if ($change_level) {
					$change_level = false;
				}
			}
		}

		// on selectionne tous les lieux de la communaute

		Zend_Loader::loadClass('TypeLieu');
		$typeLieuTable = new TypeLieu();
		$typesLieux = $typeLieuTable->fetchAll("est_communaute_type_lieu = 'oui'", "nom_type_lieu");

		$tabTypesLieux = null;
		foreach($typesLieux as $t) {
			$display = true;
			foreach($lieux as $l) {
				if ($t["id_type_lieu"] == $l["id_fk_type_lieu"] && $l["id_fk_communaute_lieu"] == $this->view->user->id_fk_communaute_braldun) {
					$display = false;
				}
			}
			if ($display) {
				$tabTypesLieux[$t["id_type_lieu"]]["type"] = $t;
				$tabTypesLieux[$t["id_type_lieu"]]["selected"] = "";
			}
		}

		$this->view->tableau = $tab;
		$this->tableauValidation = $tabValidation;
		$this->view->typeLieux = $tabTypesLieux;

		$this->view->nb_pa = 1;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_lieu", "box_communaute", "box_evenements");
	}

}