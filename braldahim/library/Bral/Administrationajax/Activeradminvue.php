<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Administrationajax_Activeradminvue extends Bral_Administrationajax_Administrationajax {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Admin : activer / désactiver Administrationvue";
	}

	function prepareCommun() {
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if (Zend_Auth::getInstance()->getIdentity()->administrationvue === false) {
			Zend_Auth::getInstance()->getIdentity()->administrationvue = true;
		} else {
			Zend_Auth::getInstance()->getIdentity()->administrationvue = false;
		}
	}

	function getListBoxRefresh() {
		return array("box_vue");
	}
}