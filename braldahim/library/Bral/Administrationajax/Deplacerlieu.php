<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Administrationajax_Deplacerlieu extends Bral_Administrationajax_Administrationajax {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Admin : déplacer un lieu";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");

		$idLieu = Bral_Util_Controle::getValeurIntVerif($this->request->get("id_lieu"));

		$lieuTable = new Lieu();
		$lieuRowset = $lieuTable->findById($idLieu);
		
		$this->view->lieu = $lieuRowset;
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		$idLieu = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		$xLieu = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_2"));
		$yLieu = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_3"));
		
		$lieuTable = new Lieu();
		$lieuRowset = $lieuTable->findById($idLieu);
		
		$data = array(
			"x_lieu" => $xLieu,
			"y_lieu" => $yLieu,
		);
		$where = "id_lieu=".$idLieu;
		$lieuTable->update($data, $where);
	}

	function getListBoxRefresh() {
		return array("box_lieu", "box_vue");
	}
}