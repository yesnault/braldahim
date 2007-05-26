<?php

class Bral_Competences_Gardiennage extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("Gardiennage");
		
		$this->tabJoursDebut = null;
		
		for ($i=1; $i<=10; $i++) {
			$this->tabJoursDebut[] = 
				array("valeur" => date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+$i, date("Y"))),
					"affichage" => date("d/m/Y", mktime(0, 0, 0, date("m")  , date("d")+$i, date("Y"))));
			$this->tabJoursDebutValides[] = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+$i, date("Y")));
		}
	}
	
	function prepareFormulaire() {
		$tabGardiens = null;
		$gardiennageTable = new Gardiennage();
		$gardiens = $gardiennageTable->findGardiens($this->view->user->id_hobbit);
		$gardiennageEnCours = $gardiennageTable->findGardiennageEnCours($this->view->user->id_hobbit);
		
		foreach($gardiens as $gardien) {
			$tabGardiens[] = array(
				"id_gardien" => $gardien["id_gardien_gardiennage"], 
				"nom_gardien" => $gardien["nom_hobbit"]);
		}
		if (count($gardiennageEnCours) < $this->view->config->game->gardiennage->nb_max_en_cours) {
			$this->view->tabJoursDebut = $this->tabJoursDebut;
			$this->view->tabGardiens = $tabGardiens;
			$this->view->nbEnCours = count($gardiennageEnCours);
			$this->view->nbMax = $this->view->config->game->gardiennage->nb_max_en_cours;
		} else {
			$this->view->messageMax = "Vous avez d&eacute;j&agrave; deux gardiennages en cours<br><br> Vous ne pouvez plus en  cr&eacute;er";
		}
		
	}
	
	function prepareResultat() {
		if ($this->request->get("valeur_1") == "nouveau") {
			$this->nouveauGardiennage();
			$this->voirGardiennage();
		} elseif ($this->request->get("valeur_1") == "voir") {
			$this->voirGardiennage();
		} else {
			throw new Zend_Exception(get_class($this)." Action invalide : ".$this->request->get("valeur_1"));
		}
	}
	

	function getListBoxRefresh() {
		return null;
	}
	
	private function nouveauGardiennage() {
		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');
		Zend_Loader::loadClass('Zend_Filter_HtmlEntities');
		
		$filtreChaine = new Zend_Filter();
		$filtreChaine->addFilter(new Zend_Filter_StringTrim())
					->addFilter(new Zend_Filter_StripTags())
					->addFilter(new Zend_Filter_HtmlEntities());
					
		$premierJour = $this->request->get("valeur_2");
		$nbJour = $this->request->get("valeur_3");
		$idGardienExistant = trim($this->request->get("valeur_4"));
		$idNouveauGardien = $this->request->get("valeur_5");
		$commentaire = substr($filtreChaine->filter($this->request->get("valeur_6")), 0, 100);
		
		// Verification du premier jour
		if (!in_array($premierJour, $this->tabJoursDebutValides)) {
			throw new Zend_Exception(get_class($this)." Premier jour invalide : ".$premierJour);
		}
		
		// Verification du nombre de jour
		if (intval($nbJour) == 0 || intval($nbJour) < -1 || intval($nbJour) > 5) {
			throw new Zend_Exception(get_class($this)." Nombre de jour(s) invalide : ".$nbJour);
		}
		
		$idGardien = null;
		if (intval($idGardienExistant) != 0  && intval($idGardienExistant) != -1) {
			$idGardien = intval($idGardienExistant);
		} elseif (intval($idNouveauGardien) != 0) {
			$idGardien = intval($idNouveauGardien);
		} else {
			throw new Zend_Exception(get_class($this)." Gardien invalide : exitant:".$idGardienExistant. " nouveau:".$idNouveauGardien);
		}
		
		// Il ne faut pas que le gardien soit le joueur lui même
		if ($idGardien == $this->view->user->id_hobbit) {
			throw new Zend_Exception(get_class($this)." Gardien invalide : Vous ne devez pas être le gardien de vous même. Gardien:".$idGardien. " Vous:".$this->view->user->id_hobbit);
		}
		
		$break = explode("-", $premierJour);
		$jour = $break[2];
		$mois = $break[1];
		$annee = $break[0];
		$dernierJour = date("Y-m-d", mktime(0, 0, 0, $mois  , $jour+$nbJour, $annee));
		
		$gardiennageTable = new Gardiennage();
		$data = array (
			'id_hobbit_gardiennage' => $this->view->user->id_hobbit,
			'id_gardien_gardiennage' => $idGardien,
			'date_debut_gardiennage' => $premierJour,
			'date_fin_gardiennage' => $dernierJour,
			'nb_jours_gardiennage' => $nbJour,
			'commentaire_gardiennage' => $commentaire
		);
		$gardiennageTable->insert($data);
		$this->view->nouveauGardiennage = true;
	}
	
	private function voirGardiennage() {
		$this->view->nouveauGardiennage = false;
		
		$gardiennageTable = new Gardiennage();
		$gardiennages = $gardiennageTable->findGardiennageEnCours($this->view->user->id_hobbit);
		
		$tabGardiennage = null;
		
		foreach($gardiennages as $g) {
			$tabGardiennage[] = array(
				"id_gardien" => $g["id_gardien_gardiennage"], 
				"nom_gardien" => $g["nom_hobbit"],
				"date_debut" => $g["date_debut_gardiennage"],
				"nb_jours" => $g["nb_jours_gardiennage"],
				"commentaire" => $g["commentaire_gardiennage"]);
		}
		$this->view->tabGardiennage = $tabGardiennage;
	}
}