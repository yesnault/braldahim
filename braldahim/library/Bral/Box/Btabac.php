<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Btabac extends Bral_Box_Boutique {
	
	public function getTitreOnglet() {
		return "Boutique Tabac";
	}
	
	public function setDisplay($display) {
		$this->view->display = $display;
	}
	
	public function render() {
		$this->preRender();
		$this->prepareArticles();
		return $this->view->render("interface/btabac.phtml");
	}
	
	private function prepareArticles() {
		Zend_Loader::loadClass('Bral_Util_BoutiqueTabac');
		Zend_Loader::loadClass('Region');
		$regionTable = new Region();
		$idRegion = $regionTable->findIdRegionByCase($this->view->user->x_braldun, $this->view->user->y_braldun);
		$this->view->tabac = Bral_Util_BoutiqueTabac::construireTabPrix(false, $idRegion);
		if ($this->view->tabac == null) {
			Bral_Util_Log::erreur()->err("Bral_Box_Btabac - Erreur de prix dans la table stock_tabac, id_region=".$idRegion);
		}
	}
}
