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
		Zend_Loader::loadClass('Region');
		$regionTable = new Region();
		$idRegion = $regionTable->findIdRegionByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$this->view->typePlantesBruts = Bral_Util_BoutiquePlantes::construireTabPrix(false, $idRegion);
		
		if ($this->view->typePlantesBruts == null) {
			Bral_Util_Log::erreur()->err("Bral_Box_Bpartieplantes - Erreur de prix dans la table stock_partieplante, id_region=".$idRegion);
		}
	}
}
