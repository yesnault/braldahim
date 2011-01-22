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
class Bral_Hotel_Voir extends Bral_Hotel_Box {

	function getNomInterne() {
		return "box_hotel_resultats";
	}

	function render() {
		return $this->view->render("hotel/voir/resultats.phtml");
	}

	public function getPreparedView() {
		Zend_Loader::loadClass("Bral_Util_Lot");
		$this->view->lots = Bral_Util_Lot::getLotsByHotel();
		
		return $this->view;
	}
}