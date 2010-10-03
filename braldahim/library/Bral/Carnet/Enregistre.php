<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Carnet_Enregistre extends Bral_Carnet_Voir {


	function render() {
		return $this->view->render("carnet/enregistre.phtml");
	}

	function getNomInterne() {
		if ($this->request->get("msg")) {
			return $this->request->get("msg");
		} else {
			return "competence_resultat";
		}
	}

	public function getListBoxRefresh() {
		return array('box_carnet');
	}

}