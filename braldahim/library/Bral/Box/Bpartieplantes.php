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
class Bral_Box_Bpartieplantes extends Bral_Box_Boutique {
	
	public function getTitreOnglet() {
		return "Boutique Plantes";
	}
	
	public function setDisplay($display) {
		$this->view->display = $display;
	}
	
	public function render() {
		$this->preRender();
		$this->prepareArticles();
		return $this->view->render("interface/bpartieplantes.phtml");
	}
	
	private function prepareArticles() {
		Zend_Loader::loadClass('Bral_Util_BoutiquePlantes');
		$this->view->typePlantesBruts = Bral_Util_BoutiquePlantes::construireTabPrix(false);
	}
}
