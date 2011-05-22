<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Competences extends Bral_Box_Box {

	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;

		$this->chargementInBoxes = false;
		$this->nomInterne = "box_competences";
		$this->render = "interface/competences.phtml";
		$this->titreOnglet = "Compétences";
		/*$this->titreOnglet .= "<span class='couleurTitre' title=\"PA : Point d'Action\">PA ".$this->view->user->pa_braldun."</span> ";
		$this->titreOnglet .= " - <span class='couleurTitre' title=\"DLA : Date Limite d'Action\">DLA ";
		$this->titreOnglet .= Bral_Util_ConvertDate::get_datetime_mysql_datetime('<b>H:i:s</b>',$this->view->user->date_fin_tour_braldun);
		$this->titreOnglet .= "</span>";
		$this->titreOnglet .= " - Mes Compétences";*/
	}

	function getTitreOnglet() {
		return $this->titreOnglet;
	}

	function getChargementInBoxes() {
		return $this->chargementInBoxes;
	}

	function getNomInterne() {
		return $this->nomInterne;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne || $this->chargementInBoxes === true) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render($this->render);
	}

	function data() {
		
		Zend_Loader::loadClass("BraldunsCompetencesFavorites");
		$favoritesTable = new BraldunsCompetencesFavorites();
		$favoritesRowset = $favoritesTable->findByIdBraldun($this->view->user->id_braldun);
		
		$tabFavorites = array();
		
		if ($favoritesRowset != null) {
			foreach($favoritesRowset as $f) {
				$tabFavorites[] = $f["id_competence"];
			}	
		}
		
		$tabCompetences = null;
		$this->view->nom_interne = $this->getNomInterne();

		$tabCompetences["basiques"] = array(
				//"nom_onglet" => "Compétences Basiques",
				"nom_onglet" => "Basique",
				"nom_systeme_onglet" => "basiques",
				"competences" => Bral_Util_Registre::get('competencesBasiques')
		);
			
		Zend_Loader::loadClass("BraldunsCompetences");
		Zend_Loader::loadClass("BraldunsMetiers");

		$braldunsCompetencesTables = new BraldunsCompetences();
		$braldunCompetences = $braldunsCompetencesTables->findByIdBraldun($this->view->user->id_braldun);
		$competence = null;
		foreach($braldunCompetences as $c) {
			if ($c["type_competence"] == "commun") {
				$pa_texte = $c["pa_utilisation_competence"];
				if ($c["nom_systeme_competence"] == "marcher") {
					$pa_texte = "1 ou 2";
				}

				$competence[] = array(
					"id_competence" => $c["id_fk_competence_hcomp"],
					"nom" => $c["nom_competence"],
					"pa_utilisation" => $c["pa_utilisation_competence"],
					"pa_texte" => $pa_texte,
					"pourcentage" => Bral_Util_Commun::getPourcentage($c, $this->view->config),
					"nom_systeme" => $c["nom_systeme_competence"],
					"pourcentage_init" => $c["pourcentage_init_competence"],
				);
			}
			if ($competence != null) {
				$tabCompetences["communes"] = array(
					//"nom_onglet" => "Compétences communes",
					"nom_onglet" => "Commune",
					"nom_systeme_onglet" => "communes",
					"competences" => $competence,
				);
			}
		}

		$tabCompetences["soule"] = array(
				"nom_onglet" => "Match de Soule",
				"nom_systeme_onglet" => "soule",
				"competences" => Bral_Util_Registre::get('competencesSoule')
		);

		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($this->view->user->id_braldun);

		foreach($braldunsMetierRowset as $m) {
			if ($this->view->user->sexe_braldun == 'feminin') {
				$nom_metier = $m["nom_feminin_metier"];
			} else {
				$nom_metier = $m["nom_masculin_metier"];
			}
			$competence = null;
			foreach($braldunCompetences as $c) {
				if ($c["type_competence"] == "metier" && $m["id_metier"] == $c["id_fk_metier_competence"]) {

					$pa_texte = $c["pa_utilisation_competence"];
					if ($c["nom_systeme_competence"] == "cuisiner") {
						$pa_texte = "2 ou 4";
					}

					$competence[] = array("id_competence" => $c["id_fk_competence_hcomp"],
						"nom" => $c["nom_competence"],
						"pa_utilisation" => $c["pa_utilisation_competence"],
						"pa_texte" => $pa_texte,
						"pourcentage" => Bral_Util_Commun::getPourcentage($c, $this->view->config),
						"nom_systeme" => $c["nom_systeme_competence"],
						"pourcentage_init" => $c["pourcentage_init_competence"],
					);
				}
			}

			$tabCompetences[$m["nom_systeme_metier"]] = array(
				"nom_onglet" => "Métier : ".$nom_metier,
				"nom_systeme_onglet" => $m["nom_systeme_metier"],
				"competences" => $competence
			);
		}

		$this->view->competences = $tabCompetences;
		$this->view->favorites = $tabFavorites;
		$this->view->metiers = $braldunsMetierRowset;
	}

	public function getTablesHtmlTri() {
		$tab[] = "idCompetencesTable";
		return $tab;
	}
}
