<?php

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
			$tab[$c->id_competence]["pourcentage_max"] = $c->pourcentage_max_competence;
			$tab[$c->id_competence]["pa_utilisation"] = $c->pa_utilisation_competence;
			$tab[$c->id_competence]["type"] = $c->type_competence;
			
			$tab2[$c->nom_systeme_competence]["id_competence"] = $c->id_competence;
			
			if ($c->type_competence == 'basic') {
				$tabBasiques[] = array 
				( "id_competence" => $c->id_competence,
					"nom" => $c->nom_competence,
					"nom_systeme" => $c->nom_systeme_competence,
					"description" => $c->description_competence
				);
				$tabBasiquesId[$c->nom_systeme_competence]["id_competence"] = $c->id_competence;
			}
		}
		Zend_Registry::set('competences', $tab);
		Zend_Registry::set('competencesId', $tab2);
		Zend_Registry::set('competencesBasiques', $tabBasiques);
	}
	
	private static function chargementNomTour() {
		$tab[1] = "Latence";
		$tab[2] = "Milieu";
		$tab[3] = "Cumul";
		Zend_Registry::set('nomsTour', $tab);
	}
}
