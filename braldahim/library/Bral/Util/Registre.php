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
class Bral_Util_Registre {

	private function __construct(){}

	public static function chargement() {
		self::chargementCompetence();
		self::chargementNomTour();
	}
	
	private static function chargementCompetence() {
		$competenceTable = new Competence();
		$competences = $competenceTable->fetchall();
		$tab = null;
		$tab2 = null;
		$tabBasiques = null;
		$tabBasiquesId = null;
		foreach ($competences as $c) {
			$tab[$c->id_competence]["nom"] = $c->nom_competence;
			$tab[$c->id_competence]["nom_systeme"] = $c->nom_systeme_competence;
			$tab[$c->id_competence]["description"] = $c->description_competence;
			$tab[$c->id_competence]["niveau_requis"] = $c->niveau_requis_competence;
			$tab[$c->id_competence]["pi_cout"] = $c->pi_cout_competence;
			$tab[$c->id_competence]["px_gain"] = $c->px_gain_competence;
			$tab[$c->id_competence]["balance_faim"] = $c->balance_faim_competence;
			$tab[$c->id_competence]["pourcentage_max"] = $c->pourcentage_max_competence;
			$tab[$c->id_competence]["pa_utilisation"] = $c->pa_utilisation_competence;
			$tab[$c->id_competence]["pa_manquee"] = $c->pa_manquee_competence;
			$tab[$c->id_competence]["type_competence"] = $c->type_competence;
			$tab[$c->id_competence]["id_fk_metier_competence"] = $c->id_fk_metier_competence;
			
			//$tab2[$c->nom_systeme_competence]["id_competence"] = $c->id_competence;
			
			if ($c->type_competence == 'basic') {
				$tabBasiques[] = array 
				( "id_competence" => $c->id_competence,
					"nom" => $c->nom_competence,
					"nom_systeme" => $c->nom_systeme_competence,
					"description" => $c->description_competence,
					"pa_utilisation" => $c->pa_utilisation_competence,
					"type_competence" => $c->type_competence,
					"pourcentage_max" => $c->pourcentage_max_competence,
					"id_fk_metier_competence" => null,
				);
				$tabBasiquesId[$c->nom_systeme_competence]["id_competence"] = $c->id_competence;
			}
		}
		Zend_Registry::set('competences', $tab);
		//Zend_Registry::set('competencesId', $tab2);
		Zend_Registry::set('competencesBasiques', $tabBasiques);
	}
	
	private static function chargementNomTour() {
		$tab[1] = "Latence";
		$tab[2] = "Milieu";
		$tab[3] = "Cumul";
		Zend_Registry::set('nomsTour', $tab);
	}
	
	public static function getNomUnite($unite, $systeme = false, $quantite = 0) {
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
	
	private static function chargementTypeUnite() {
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
