<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Competences
{

	public static function getJsonData(&$view)
	{
		return self::data($view);
	}

	private static function data(&$view)
	{
		Zend_Loader::loadClass("BraldunsCompetencesFavorites");
		$favoritesTable = new BraldunsCompetencesFavorites();
		$favoritesRowset = $favoritesTable->findByIdBraldun($view->user->id_braldun);

		$tabFavorites = array();

		if ($favoritesRowset != null) {
			foreach ($favoritesRowset as $f) {
				$tabFavorites[$f["id_competence"]] = $f["id_competence"];
			}
		}

		$tabCompetences = null;

		/*
		 *
		 * TODO
		$tabCompetences["basiques"] = array(
			//"nom_onglet" => "Compétences Basiques",
			"nom_onglet" => "Basique",
			"nom_systeme_onglet" => "basiques",
			"competences" => Bral_Util_Registre::get('competencesBasiques')
		);
		*/

		Zend_Loader::loadClass("BraldunsCompetences");
		Zend_Loader::loadClass("BraldunsMetiers");

		$braldunsCompetencesTables = new BraldunsCompetences();
		$braldunCompetences = $braldunsCompetencesTables->findByIdBraldun($view->user->id_braldun);
		$competence = null;
		foreach ($braldunCompetences as $c) {
			if ($c["type_competence"] == "commun") {
				$pa_texte = $c["pa_utilisation_competence"];

				$tabCompetences[$c["id_fk_competence_hcomp"]] = array(
					"id_competence" => $c["id_fk_competence_hcomp"],
					"nom" => $c["nom_competence"],
					"pa_utilisation" => $c["pa_utilisation_competence"],
					"pa_texte" => $pa_texte,
					"pourcentage" => Bral_Util_Commun::getPourcentage($c, $view->config),
					"nom_systeme" => $c["nom_systeme_competence"],
					"pourcentage_init" => $c["pourcentage_init_competence"],
					"type" => "Communes",
					"favorite" => array_key_exists($c["id_fk_competence_hcomp"], $tabFavorites),
					"active" => ($view->user->pa_braldun >= $c["pa_utilisation_competence"]),
				);
			}
		}

		/*
		 * TODO
		$tabCompetences["soule"] = array(
			"nom_onglet" => "Match de Soule",
			"nom_systeme_onglet" => "soule",
			"competences" => Bral_Util_Registre::get('competencesSoule')
		);

		*/
		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($view->user->id_braldun);

		foreach ($braldunsMetierRowset as $m) {
			if ($view->user->sexe_braldun == 'feminin') {
				$nom_metier = $m["nom_feminin_metier"];
			} else {
				$nom_metier = $m["nom_masculin_metier"];
			}
			foreach ($braldunCompetences as $c) {
				if ($c["type_competence"] == "metier" && $m["id_metier"] == $c["id_fk_metier_competence"]) {

					$pa_texte = $c["pa_utilisation_competence"];
					if ($c["nom_systeme_competence"] == "cuisiner") {
						$pa_texte = "2 ou 4";
					}

					$tabCompetences[$c["id_fk_competence_hcomp"]] = array("id_competence" => $c["id_fk_competence_hcomp"],
						"nom" => $c["nom_competence"],
						"pa_utilisation" => $c["pa_utilisation_competence"],
						"pa_texte" => $pa_texte,
						"pourcentage" => Bral_Util_Commun::getPourcentage($c, $view->config),
						"nom_systeme" => $c["nom_systeme_competence"],
						"pourcentage_init" => $c["pourcentage_init_competence"],
						"type" => "Métier : " . $nom_metier,
						"favorite" => array_key_exists($c["id_fk_competence_hcomp"], $tabFavorites),
						"active" => ($view->user->pa_braldun >= $c["pa_utilisation_competence"]),
					);
				}
			}
		}

		/*
		"Nom" => 'Attaquer une palissade',
					"PA" => 4,
					"Active" => true,
					"Favorite" => true,
					"Tag" => 'Basiques'
		*/

		return $tabCompetences;
	}

}
