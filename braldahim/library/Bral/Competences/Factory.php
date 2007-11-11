<?php

class Bral_Competences_Factory {
	static function getAction($request, $view) {
		Zend_Loader::loadClass("Bral_Competences_Competence");
		
		Zend_Loader::loadClass("Bral_Competences_Abattrearbre");
		Zend_Loader::loadClass("Bral_Competences_Assaisonner");
		Zend_Loader::loadClass("Bral_Competences_Attaquer");
		Zend_Loader::loadClass("Bral_Competences_Chasser");
		Zend_Loader::loadClass("Bral_Competences_Courrir");
		Zend_Loader::loadClass("Bral_Competences_Cueillir");
		Zend_Loader::loadClass("Bral_Competences_Cuisiner");
		Zend_Loader::loadClass("Bral_Competences_Decalerdla");
		Zend_Loader::loadClass("Bral_Competences_Depiauter");
		Zend_Loader::loadClass("Bral_Competences_Distribuerpx");
		Zend_Loader::loadClass("Bral_Competences_Extraire");
		Zend_Loader::loadClass("Bral_Competences_Identifierrune");
		Zend_Loader::loadClass("Bral_Competences_Gardiennage");
		Zend_Loader::loadClass("Bral_Competences_Manger");
		Zend_Loader::loadClass("Bral_Competences_Marcher");
		Zend_Loader::loadClass("Bral_Competences_Monterpalissade");
		Zend_Loader::loadClass("Bral_Competences_Ramasser");
		Zend_Loader::loadClass("Bral_Competences_Rechercherplante");
		Zend_Loader::loadClass("Bral_Competences_Sonder");
		
		$matches = null;
		preg_match('/(.*)_competence_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeCompetence = $matches[2];
		$construct = null;
		$hobbitCompetence = null;
		
		// On regarde si c'est une competence basique
		$competencesBasiques = Zend_Registry::get('competencesBasiques');
		foreach($competencesBasiques as $c) {
			if ($c["nom_systeme"] == $nomSystemeCompetence) {
				$construct = "Bral_Competences_".$nomSystemeCompetence;
				$competence = $c;
				break;
			}
		}
		
		// verification que le joueur a accès à la compétence
		if ($construct == null) {
			Zend_Loader::loadClass("HobbitsCompetences");
			//$hobbitTable = new Hobbit();
			//$hobbitRowset = $hobbitTable->find($view->user->id_hobbit);
			//$hobbit = $hobbitRowset->current();
			//$hobbitCompetences = $hobbit->findCompetenceViaHobbitsCompetences();

			$hobbitsCompetencesTables = new HobbitsCompetences();
			$hobbitCompetences = $hobbitsCompetencesTables->findByIdHobbit($view->user->id_hobbit);
			
			$competences = Zend_Registry::get('competences');
			//$c = Zend_Registry::get('competencesId');
	
			foreach($hobbitCompetences as $c) {
				if ($c["nom_systeme_competence"] == $nomSystemeCompetence) {
					$construct = "Bral_Competences_".$nomSystemeCompetence;
					$competence = $competences[$c["id_competence"]];
					$hobbitCompetence = $c;
					break;
				}
			}
		}
		
	    // verification que la classe de la competence existe.            
		if (($construct != null) && (class_exists($construct))) {                
			return new $construct ($competence, $hobbitCompetence, $request, $view, $action);
		} else {
			throw new Zend_Exception("Comp&eacute;tence invalide: ".$nomSystemeCompetence);
		}
	}
}