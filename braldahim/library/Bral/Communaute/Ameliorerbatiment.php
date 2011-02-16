<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Ameliorerbatiment extends Bral_Communaute_Communaute {

	function getNomInterne() {
		return "box_action";
	}

	function getTitre() {
		return "Améliorer un bâtiment";
	}
	
	function prepareCommun() {
		if ($this->view->user->rangCommunaute > Bral_Util_Communaute::ID_RANG_TENANCIER) {
			throw new Zend_Exception(get_class($this)." Vous n'êtes pas tenancier de la communauté ". $this->view->user->rangCommunaute);
		}
		
		$this->view->nb_pa = 1;
		
		
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_lieu", "box_communaute", "box_evenements");
	}

}