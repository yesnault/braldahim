<?php

class Bral_Competences_Factory {
	static function getAction($request, $view) {
		$matches = null;
		preg_match('/(.*)_competence_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeCompetence = $matches[2];
		$construct = null;
		
		// verification que le joueur a accès à la compétence
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($view->user->id);
		$hobbit = $hobbitRowset->current();
		$hobbitCompetences = $hobbit->findCompetenceViaHobbitsCompetences();
		$competences = Zend_Registry::get('competences');
		$c = Zend_Registry::get('competencesId');

		foreach($hobbitCompetences as $c) {
			if ($c->nom_systeme_competence == $nomSystemeCompetence) {
				$construct = "Bral_Competences_".$nomSystemeCompetence;
				break;
			}
		}
		
	    // verification que la classe de la competence existe.            
		if (($construct != null) && (class_exists($construct))) {                
			return new $construct ($request, $view, $action);
		} else {
			throw new Zend_Exception("Comp&eacute;tence invalide: ".$nomSystemeCompetence);
		}
	}
}