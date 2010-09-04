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
class Bral_Lieux_Escaliers extends Bral_Lieux_Lieu {

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		$this->view->user->z_braldun = $this->view->user->z_braldun + 1;
		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_lieu", "box_blabla"));
	}
}