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
class Bral_Filature_Annuler extends Bral_Filature_Filature {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Annuler la filature en cours";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Filature");

		$this->refresh = false;

		$tableFilature = new Filature();
		$filatureEnCours = $tableFilature->findEnCoursByIdBraldun($this->view->user->id_braldun);
		if ($filatureEnCours != null || count($filatureEnCours) == 1) {
			$this->view->filerEnCours = $filatureEnCours[0];
		} else {
			$this->view->filerEnCours = null;
		}

	}

	function prepareFormulaire() {}

	function prepareResultat() {
		if ($this->view->filerEnCours == null) {
			throw new Zend_Exception(get_class($this)." Annuler invalide");
		}

		$this->annuler();
		$this->refresh = true;
	}

	private function annuler() {
		$tableFilature = new Filature();
		$data = array('date_fin_filature' => date("Y-m-d H:i:s"));
		$where = "id_filature = ".$this->view->filerEnCours["id_filature"];
		$tableFilature->update($data, $where);
	}

	function getListBoxRefresh() {
		return array("box_filatures");
	}

}