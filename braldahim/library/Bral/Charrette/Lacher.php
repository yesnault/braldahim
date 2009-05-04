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
class Bral_Charrette_Lacher extends Bral_Charrette_Charrette {

	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Lâcher la charrette";
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");

		$tabCharrettes = null;
		$this->view->possedeCharrette = false;

		$charretteTable = new Charrette();

		$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);
		if ($charrette != null && count($charrette) > 0) {
			foreach ($charrette as $c) {
				$this->view->idCharrette = $c["id_charrette"];
				$this->view->possedeCharrette = true;
				break;
			}
		}
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification abattre arbre
		if ($this->view->possedeCharrette == false) {
			throw new Zend_Exception(get_class($this)." Possede aucune charrette ");
		}

		$this->calculLacherCharrette();
	}

	private function calculLacherCharrette() {
		$charretteTable = new Charrette();
		$dataUpdate = array(
			"id_fk_hobbit_charrette" => null,
			"x_charrette" => $this->view->user->x_hobbit,
			"y_charrette" => $this->view->user->y_hobbit,
		);
		$where = "id_fk_hobbit_charrette = ".$this->view->user->id_hobbit;
		$charretteTable->update($dataUpdate, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue"));
	}
}
