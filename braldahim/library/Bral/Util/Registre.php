<?php

class Bral_Util_Registre {

	private function __construct(){}

	public static function chargement() {
		self::chargementCompetence();
	}
	
	private static function chargementCompetence() {
		$competence = new Competence();
		$competences = $competence->fetchall();
		
		foreach ($competences as $c) {
			$tab[$c->id]["nom"] = $c->nom_competence;
			$tab[$c->id]["nom_systeme"] = $c->nom_systeme_competence;
			$tab[$c->id]["description_systeme"] = $c->description_competence;
			$tab[$c->id]["niveau_requis"] = $c->niveau_requis_competence;
			$tab[$c->id]["pi_cout"] = $c->pi_cout_competence;
			$tab[$c->id]["px_gain"] = $c->px_gain_competence;
			$tab[$c->id]["pourcentage_max"] = $c->pourcentage_max_competence;
			$tab[$c->id]["pa_utilsation"] = $c->pa_utilisation_competence;
			$tab[$c->id]["type"] = $c->type_competence;
		}
		Zend_Registry::set('competences', $tab);
	}
}
