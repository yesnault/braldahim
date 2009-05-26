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
class Bral_Hotel_Voir extends Bral_Hotel_Hotel {

	private $arBoutiqueBruts;
	private $arBoutiqueTransformes;

	function getNomInterne() {
		return "box_lieu";
	}

	public function getTitreAction() {
		return null;
	}
	
	function render() {
		return $this->view->render("hotel/voir.phtml");
	}

	function prepareCommun() {
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}
}