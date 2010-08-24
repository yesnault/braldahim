<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Voir.php 2841 2010-08-14 10:03:36Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-08-14 12:03:36 +0200 (sam., 14 août 2010) $
 * $LastChangedRevision: 2841 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Carnet_Enregistre extends Bral_Carnet_Voir {


	function render() {
		return $this->view->render("carnet/enregistre.phtml");
	}

	function getNomInterne() {
		return "competence_resultat";
	}

	public function getListBoxRefresh() {
		return array('box_carnet');
	}

}