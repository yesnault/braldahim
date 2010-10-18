<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Creuser extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Tunnel');
		Zend_Loader::loadClass("Bral_Util_Metier");

		$this->view->creuserOk = false;

		$this->distance = 2;
		$this->view->x_min = $this->view->user->x_braldun - $this->distance;
		$this->view->x_max = $this->view->user->x_braldun + $this->distance;
		$this->view->y_min = $this->view->user->y_braldun - $this->distance;
		$this->view->y_max = $this->view->user->y_braldun + $this->distance;

		$tunnelTable = new Tunnel();
		$tunnels = $tunnelTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);

		$defautChecked = false;

		$tabTunnels = null;
		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			for ($i = -$this->distance; $i <= $this->distance; $i ++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;
				$tabTunnels[$x][$y] = false;
				$tabTunnelsPossibles[$x][$y] = false;
			}
		}
			
		foreach($tunnels as $t) {
			$tabTunnels[$t["x_tunnel"]][$t["y_tunnel"]] = true;
		}

		$this->distance = 1;

		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			for ($i = -$this->distance; $i <= $this->distance; $i ++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;

				$nDecouverte = 0;
				if ($tabTunnels[$x-1][$y-1] === true) $nDecouverte++;
				if ($tabTunnels[$x-1][$y] === true) $nDecouverte++;
				if ($tabTunnels[$x-1][$y+1] === true) $nDecouverte++;
				if ($tabTunnels[$x][$y+1] === true) $nDecouverte++;
				if ($tabTunnels[$x][$y-1] === true) $nDecouverte++;
				if ($tabTunnels[$x+1][$y+1] === true) $nDecouverte++;
				if ($tabTunnels[$x+1][$y] === true) $nDecouverte++;
				if ($tabTunnels[$x+1][$y-1] === true) $nDecouverte++;

				//La case à creuser doit avoir au minimum 4 cases adjacentes non évidées
				if ($nDecouverte < 3) {
					$tabTunnelsPossibles[$x][$y] = true;
				}
			}
		}


		$this->distance = 1;
		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			$change_level = true;
			for ($i = -$this->distance; $i <= $this->distance; $i ++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;
					
				$display = $x;
				$display .= " ; ";
				$display .= $y;
					
				$tunnelTrouve = false;
				$valid = false;

				if ($tabTunnelsPossibles[$x][$y] === true && $tabTunnels[$x][$y] === false) {
					$valid = true;
				}

				if (($j == 0 && $i == 0) == true) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
				}
					
				if ($x < $this->view->config->game->x_min || $x > $this->view->config->game->x_max
				|| $y < $this->view->config->game->y_min || $y > $this->view->config->game->y_max ) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
				}

				if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
					$this->view->creuserOk = true;
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
		$this->view->tableau = $tab;
		$this->tableauValidation = $tabValidation;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		if ($this->view->creuserOk == false) {
			throw new Zend_Exception(get_class($this)." Monter Palissade interdit");
		}

		// on verifie que l'on peut monter une tunnel sur la case
		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = preg_split("/h/", $x_y);
		if ($offset_x < -$this->distance || $offset_x > $this->distance) {
			throw new Zend_Exception(get_class($this)." Creuser X impossible : ".$offset_x);
		}

		if ($offset_y < -$this->distance || $offset_y > $this->distance) {
			throw new Zend_Exception(get_class($this)." Creuser Y impossible : ".$offset_y);
		}

		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this)." Creuser XY impossible : ".$offset_x.$offset_y);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculCreuser($this->view->user->x_braldun + $offset_x, $this->view->user->y_braldun + $offset_y);
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	private function calculCreuser($x, $y) {

		$this->view->user->x_braldun = $x;
		$this->view->user->y_braldun = $y;

		$data = array(
			"x_tunnel"  => $x,
			"y_tunnel" => $y,
			"z_tunnel" => $this->view->user->z_braldun,
			"date_tunnel" => date("Y-m-d H:00:00"),
			"est_eboulable_tunnel" => 'oui',
		);

		$tunnelTable = new Tunnel();
		$tunnelTable->insert($data);
		unset($tunnelTable);

		Zend_Loader::loadClass("StatsFabricants");
		$statsFabricants = new StatsFabricants();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataFabricants["niveau_braldun_stats_fabricants"] = $this->view->user->niveau_braldun;
		$dataFabricants["id_fk_braldun_stats_fabricants"] = $this->view->user->id_braldun;
		$dataFabricants["mois_stats_fabricants"] = date("Y-m-d", $moisEnCours);
		$dataFabricants["nb_piece_stats_fabricants"] = 1;
		$dataFabricants["id_fk_metier_stats_fabricants"] = Bral_Util_Metier::METIER_MINEUR_ID;
		$statsFabricants->insertOrUpdate($dataFabricants);

		$this->view->tunnel = $data;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue"));
	}
}
