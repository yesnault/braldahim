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
		throw new Zend_Exception("Boutique fermee");
		$this->preRender();
		
		Zend_Loader::loadClass('Bral_Util_BoutiqueBois');
		Zend_Loader::loadClass("Region");
		
		$regionTable = new Region();
		$idRegion = $regionTable->findIdRegionByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$tabStockPrix = Bral_Util_BoutiqueBois::construireTabStockPrix($idRegion);
		if ($tabStockPrix == null) {
			Bral_Util_Log::erreur()->err("Bral_Box_Bbois - Erreur de prix dans la table stock_bois, id_region=".$idRegion);
		}
		$this->view->tabStockPrix = $tabStockPrix;
		return $this->view->render("interface/bbois.phtml");
	}
}
