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
		$this->prepareTableau(false);
	}

	private function prepareTableau($supprimeMinerai) {

		$tabNiveauxValides[] = -10;
		$tabNiveauxValides[] = -11;
		$tabNiveauxValides[] = -12;
		$tabNiveauxValides[] = -13;

		if (!in_array($this->view->user->z_braldun, $tabNiveauxValides)) {
			$this->view->niveauValide = false;
			return;
		} else {
			$this->view->niveauValide = true;
		}

		Zend_Loader::loadClass("Bral_Util_Dijkstra");
		$dijkstra = new Bral_Util_Dijkstra();
		$dijkstra->calcul(1, $this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, null, false);

		$this->distance = 2;
		$x_min = $this->view->user->x_braldun - $this->distance;
		$x_max = $this->view->user->x_braldun + $this->distance;
		$y_min = $this->view->user->y_braldun - $this->distance;
		$y_max = $this->view->user->y_braldun + $this->distance;

		Zend_Loader::loadClass("Filon");
		$filonTable = new Filon();

		$tunnelTable = new Tunnel();
		$tunnels = $tunnelTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_braldun);

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
		$filonsSupprimes = 0;

		$numero = -1;

		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			for ($i = -$this->distance; $i <= $this->distance; $i ++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;
				$numero++;
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
					// on regarde la distance dijkstra
					if ($dijkstra->getDistance($numero) == 1) {
						$tabTunnelsPossibles[$x][$y] = true;
					}

				}
			}
		}


		$this->distance = 1;
		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			$change_level = true;
			for ($i = -$this->distance; $i <= $this->distance; $i ++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;
					
				$caseCourante = false;
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
					$caseCourante = true;
				}
					
				if ($x < $this->view->config->game->x_min || $x > $this->view->config->game->x_max
				|| $y < $this->view->config->game->y_min || $y > $this->view->config->game->y_max ) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
				}

				// si l'on a effectué l'action, on supprime les filons
				// où les cases sont non valides, où la construction de tunnel n'est pas possible
				// et où il n'y a pas déjà un tunnel
				if ($supprimeMinerai && !$valid && $tabTunnelsPossibles[$x][$y] == false && $tabTunnels[$x][$y] === false && !$caseCourante) {
					$nb = $filonTable->delete("x_filon = ".$x. " and y_filon = ".$y." and z_filon=".$this->view->user->z_braldun);
					$filonsSupprimes = $filonsSupprimes + $nb;
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

		$this->view->filonsSupprimes = $filonsSupprimes;
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

		if ($this->view->niveauValide == false) {
			throw new Zend_Exception(get_class($this)." Niveau invalide : ".$this->view->user->z_braldun);
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

		if (Bral_Util_De::get_1d20() == 10) {
			$nidDecouvert = $this->calculNid($x, $y, $this->view->user->z_braldun);
		}

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

		// Pour la suppression des filons invalides
		$this->prepareTableau(true);

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

	private function calculNid($x, $y, $z) {
		// on récupère l'entrée de mine la plus proche au niveau 0;
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Lieu");
		$lieuTable = new Lieu();

		$lieuRowset = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_MINE, $this->view->user->x_braldun, $this->view->user->y_braldun, "non");
		if ($lieuRowset == null || count($lieuRowset) < 1) {
			throw new Zend_Exception("Erreur nb mine x:".$this->view->user->x_braldun." y:".$this->view->user->y_braldun);
		}
		$lieu = $lieuRowset[0];
		$distance =  $lieu["distance"];

		if ($distance < 11) { // 11: distance = 5 + niveauMin x 3, avec niveauMin=2, distanceMin : 11
			return false; // pas de monstre à poper pour une distance < 11
		}

		Zend_Loader::loadClass("Bral_Batchs_Batch");
		Zend_Loader::loadClass("Bral_Batchs_CreationNids");
		Zend_Loader::loadClass("TypeMonstre");
		Zend_Loader::loadClass("ZoneNid");
		Zend_Loader::loadClass("CreationNids");
		Zend_Loader::loadClass("Nid");

		$nbMonstres = Bral_Util_De::get_de_specifique(Bral_Batchs_CreationNids::NB_MONSTRES_PAR_NID_MIN, Bral_Batchs_CreationNids::NB_MONSTRES_PAR_NID_MAX);
		$nbJours = Bral_Util_De::get_de_specifique(0, 4);

		// Recuperation de la zone de nid
		$zoneNidTable = new ZoneNid();
		$zones = $zoneNidTable->findByCase($x, $y, $z);
		if ($zones == null || count($zones) > 1) {
			throw new Zend_Exception("Creuser: Erreur Parametrage zone nid: x:".$x." y:".$y." z:".$z);
		}
		$idZoneNid = $zones[0]["id_zone_nid"];

		$typeMonstreTable = new TypeMonstre();
		$niveauMin = ($distance - 5) / 3;
		$niveauMax = ($distance + 15 - 5) / 3;
		if ($niveauMin > 18) {
			$niveauMin = 18;
		}
		if ($niveauMax > 39) {
			$niveauMax = 1000;
		}

		// Récupération des types de monstres associés à la zone de nid
		$creationNidsTable = new CreationNids();
		$typesMonstres = $creationNidsTable->findIdTypeMonstreNiveauMinMaxByIdZone($idZoneNid, $niveauMin, $niveauMax);

		if ($typesMonstres == null || count($typesMonstres) < 1) {
			throw new Zend_Exception("Creuser: Erreur Parametrage 2 zone nid: ".$idZoneNid." min:".$niveauMin. " max:".$niveauMax);
		}

		$idKey = Bral_Util_De::get_de_specifique(0, count($typesMonstres) - 1);
		$typeMonstre = $typesMonstres[$idKey];
		$idTypeMonstre = $typeMonstre["id_type_monstre"];

		if ($idTypeMonstre == TypeMonstre::ID_TYPE_BALROG) {
			Zend_Loader::loadClass("Monstre");
			$monstreTable = new Monstre();
			if ($monstreTable->countAllByType($idTypeMonstre) > 0) {
				$idTypeMonstre = $idTypeMonstre - 1;
			} else {
				$nbMonstres = 1;
				$nbJours = 0;
				Zend_Loader::loadClass("Bral_Util_Tracemail");
				Bral_Util_Tracemail::traite("Creation du Balrog en $x, $y, $z", $this->view, "Balrog !");
			}
		} else {
			Zend_Loader::loadClass("Bral_Util_Tracemail");
			Bral_Util_Tracemail::traite("Apparition monstre en mine en $x, $y, $z", $this->view, "Monstre en mine !");
		}

		$data = array(
			'x_nid' => $x,
			'y_nid' => $y,
			'z_nid' => $z,
			'nb_monstres_total_nid' => $nbMonstres,
			'nb_monstres_restants_nid' => $nbMonstres,
			'id_fk_zone_nid' => $idZoneNid,
			'id_fk_type_monstre_nid' => $idTypeMonstre,
			'date_creation_nid' => date("Y-m-d H:i:s"),
			'date_generation_nid' =>  Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), $nbJours),
		);
		$nidTable = new Nid();
		$nidTable->insert($data);
		return true;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue"));
	}
}
