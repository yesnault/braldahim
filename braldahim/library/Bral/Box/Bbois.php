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
class Bral_Box_Bbois extends Bral_Box_Boutique {
	
	public function getTitreOnglet() {
		return "Boutique Bois";
	}
	
	public function setDisplay($display) {
		$this->view->display = $display;
	}
	
	public function render() {
		$this->preRender();
		
		$prixBois = "PrixBois";
		
		$articles[] = array(
			"nom" => "Bois",
			"type" => "bois",
			"prix" => $prixBois,
		);
		$this->view->articles = $articles;
		return $this->view->render("interface/bbois.phtml");
	}
}
