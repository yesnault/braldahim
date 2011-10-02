<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Registre
{

	private function __construct()
	{
	}

	public static function get($key)
	{
		if (Zend_Registry::isRegistered($key) == false && ($key == "competencesBasiques" || $key == "competencesSoule" || $key == "competences")) {
			self::chargementCompetence();
		}
		return Zend_Registry::get($key);
	}

	public static function chargement()
	{
		self::chargementNomTour();
	}

	private static function chargementCompetence()
	{
		Zend_Loader::loadClass("Competence");
		$competenceTable = new Competence();
		$competences = $competenceTable->fetchall(null, "ordre_competence");
		$tab = null;
		$tab2 = null;
		$tabBasiques = null;
		$tabSoule = null;
		foreach ($competences as $c) {

			$pa_texte = $c->pa_utilisation_competence;
			if ($c->nom_systeme_competence == "marcher") {
				$pa_texte = "1 à 3";
			}

			if ($c["nom_systeme_competence"] == "transbahuter") {
				$pa_texte = "0 ou 1";
			}

			$tab[$c->id_competence]["nom"] = $c->nom_competence;
			$tab[$c->id_competence]["nom_systeme"] = $c->nom_systeme_competence;
			$tab[$c->id_competence]["description"] = $c->description_competence;
			$tab[$c->id_competence]["niveau_requis"] = $c->niveau_requis_competence;
			$tab[$c->id_competence]["pi_cout"] = $c->pi_cout_competence;
			$tab[$c->id_competence]["px_gain"] = $c->px_gain_competence;
			$tab[$c->id_competence]["balance_faim"] = $c->balance_faim_competence;
			$tab[$c->id_competence]["pourcentage_max"] = $c->pourcentage_max_competence;
			$tab[$c->id_competence]["pa_utilisation"] = $c->pa_utilisation_competence;
			$tab[$c->id_competence]["pa_texte"] = $pa_texte;
			$tab[$c->id_competence]["pa_manquee"] = $c->pa_manquee_competence;
			$tab[$c->id_competence]["type_competence"] = $c->type_competence;
			$tab[$c->id_competence]["id_fk_metier_competence"] = $c->id_fk_metier_competence;

			//$tab2[$c->nom_systeme_competence]["id_competence"] = $c->id_competence;

			if ($c->type_competence == 'basic' || $c->type_competence == 'soule') {
				$tabCompetence = array
				("id_competence" => $c->id_competence,
					"nom" => $c->nom_competence,
					"nom_systeme" => $c->nom_systeme_competence,
					"description" => $c->description_competence,
					"pa_utilisation" => $c->pa_utilisation_competence,
					"pa_texte" => $pa_texte,
					"type_competence" => $c->type_competence,
					"pourcentage_max" => $c->pourcentage_max_competence,
					"id_fk_metier_competence" => null,
					"balance_faim" => $c->balance_faim_competence,
					"px_gain" => $c->px_gain_competence,
					"pourcentage_init" => 100,
				);
				if ($c->type_competence == 'basic') {
					$tabBasiques[] = $tabCompetence;
				} elseif ($c->type_competence == 'soule') {
					$tabSoule[] = $tabCompetence;
				}

			}
		}
		Zend_Registry::set('competences', $tab);
		Zend_Registry::set('competencesBasiques', $tabBasiques);
		Zend_Registry::set('competencesSoule', $tabSoule);
	}

	private static function chargementNomTour()
	{
		$tab[1] = "Sommeil";
		$tab[2] = "Éveil";
		$tab[3] = "Activité";
		Zend_Registry::set('nomsTour', $tab);
	}

	public static function getNomUnite($unite, $systeme = false, $quantite = 0)
	{
		if (Zend_Registry::isRegistered("typesUnites") == false) {
			self::chargementTypeUnite();
		}
		$tabUnite = Zend_Registry::get('typesUnites');
		if ($unite != null && isset($tabUnite[$unite])) {
			if (!$systeme) {
				if ($quantite > 1) {
					return $tabUnite[$unite]["nom_pluriel"];
				} else {
					return $tabUnite[$unite]["nom"];
				}
			} else {
				return $tabUnite[$unite]["nom_systeme"];
			}
		}
	}

	private static function chargementTypeUnite()
	{
		Zend_Loader::loadClass("TypeUnite");
		$typeUniteTable = new TypeUnite();
		$typeUniteRowset = $typeUniteTable->fetchAll();
		$typeUniteRowset = $typeUniteRowset->toArray();
		foreach ($typeUniteRowset as $t) {
			$tabUnite[$t["id_type_unite"]] = array(
				"nom_systeme" => $t["nom_systeme_type_unite"],
				"nom" => $t["nom_type_unite"],
				"nom_pluriel" => $t["nom_pluriel_type_unite"],
			);
		}
		Zend_Registry::set('typesUnites', $tabUnite);
	}
}
