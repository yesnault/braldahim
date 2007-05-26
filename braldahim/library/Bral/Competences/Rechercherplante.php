<?php

class Bral_Competences_Rechercherplante extends Bral_Competences_Competence {
	
	function prepareCommun() {
	}
	
	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}
	
	function prepareResultat() {
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_vue", "box_competences_communes", "box_competences_basiques", "box_competences_metiers", "box_lieu", "box_laban");
	}
}