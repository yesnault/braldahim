<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Brasserie_Match extends Bral_Brasserie_Box {

	function getTitreOnglet() {
		return "Match";
	}

	function getNomInterne() {
		return "box_brasserie_match";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		Zend_Loader::loadClass("Bral_Helper_Tooltip");
		Zend_Loader::loadClass("Bral_Util_Lien");
		Zend_Loader::loadClass("Hobbit");
		$this->view->nom_interne = $this->getNomInterne();
		$this->prepareMatch();
		$this->prepareEquipes();
		$this->prepareEvenements();
		return $this->view->render("brasserie/match.phtml");
	}

	private function prepareMatch() {
		Zend_Loader::loadClass('SouleMatch');

		if (((int)$this->request->get("id_match").""!=$this->request->get("id_match")."")) {
			throw new Zend_Exception(get_class($this)." match invalide : ".$this->request->get("id_match"));
		} else {
			$idMatch = (int)$this->request->get("id_match");
		}
		
		$souleMatchTable = new SouleMatch();
		$matchs = $souleMatchTable->findByIdMatch($idMatch);
		if (count($matchs) != 1) {
			throw new Zend_Exception(get_class($this)." match invalide  2: ".$this->request->get("id_match"));
		}
		
		$this->match = $matchs[0];
		
		$porteur = null;
		if ($this->match != null && $this->match["id_fk_joueur_ballon_soule_match"] != null) {
			$idPorteur = $this->match["id_fk_joueur_ballon_soule_match"];
			$hobbitTable = new Hobbit();
			$hobbit = $hobbitTable->findById($idPorteur);
			if ($hobbit != null) {
				$porteur = $hobbit->toArray();
			}
		}

		$this->view->porteur = $porteur;
		$this->view->matchEnCours = $this->match;
	}

	private function prepareEquipes() {
		Zend_Loader::loadClass('SouleEquipe');
		
		$equipes["equipea"] = array('nom_equipe' => 'équipe A', "joueurs" => null, "plaquages" => 0, "plaques" => 0, "px" => 0);
		$equipes["equipeb"] = array('nom_equipe' => 'équipe B', "joueurs" => null, "plaquages" => 0, "plaques" => 0, "px" => 0);

		$souleEquipeTable = new SouleEquipe();

		$joueurs = $souleEquipeTable->findByIdMatch($this->match["id_soule_match"]);
		$equipes["equipea"]["nom_equipe"] = $this->match["nom_equipea_soule_match"];
		$equipes["equipeb"]["nom_equipe"] = $this->match["nom_equipeb_soule_match"];
			
		$equipes["equipea"]["px"] = $this->match["px_equipea_soule_match"];
		$equipes["equipeb"]["px"] = $this->match["px_equipeb_soule_match"];

		$equipes["equipea"]["plaquages"] = 0;
		$equipes["equipea"]["plaques"] = 0;
		$equipes["equipeb"]["plaquages"] = 0;
		$equipes["equipeb"]["plaques"] = 0;

		if ($joueurs != null && count($joueurs) > 0) {
			foreach($joueurs as $j) {
				if ($j["camp_soule_equipe"] == 'a') {
					$equipes["equipea"]["joueurs"][] = $j;
					$equipes["equipea"]["plaquages"] = $equipes["equipea"]["plaquages"] + $j["nb_hobbit_plaquage_soule_equipe"];
					$equipes["equipea"]["plaques"] = $equipes["equipea"]["plaques"] + $j["nb_plaque_soule_equipe"];
				} else {
					$equipes["equipeb"]["joueurs"][] = $j;
					$equipes["equipeb"]["plaquages"] = $equipes["equipeb"]["plaquages"] + $j["nb_hobbit_plaquage_soule_equipe"];
					$equipes["equipeb"]["plaques"] = $equipes["equipeb"]["plaques"] + $j["nb_plaque_soule_equipe"];
				}
			}
		}

		$this->view->equipes = $equipes;
		$this->view->joueurs = $joueurs;
	}

	private function prepareEvenements() {
		Zend_Loader::loadClass("Evenement");
		$evenementTable = new Evenement();
		$rowset = $evenementTable->findByIdMatch($this->match["id_soule_match"]);

		$tab = null;
		foreach($rowset as $r) {
			$hobbit = $r["prenom_hobbit"]." ".$r["nom_hobbit"]." (".$r["id_hobbit"].")";
			$tab[] = array ("date_evenement" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y à H:i:s ',$r["date_evenement"]),
							"hobbit_evenement" => $hobbit,
							"details_evenement" => $r["details_evenement"]);
		}
		$this->view->evenements = $tab;
	}
}