<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Voir.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Filature_Voir extends Bral_Filature_Filature {

	function getNomInterne() {
		return "box_filature_voir";
	}

	function render() {
		return $this->view->render("filature/voir.phtml");
	}

	function getTitreAction() {}
	public function calculNbPa() {}

	function prepareCommun() {

		Zend_Loader::loadClass("Bral_Util_Lien");
		
		if ($this->request->get("id_filature") != "") {
			$this->idFilatureEnCours =  Bral_Util_Controle::getValeurIntVerif($this->request->get("id_filature"));
		} else if ($this->idFilatureDefaut != null) {
			$this->idFilatureEnCours =  $this->idFilatureDefaut;
		}
		if ($this->idFilatureEnCours == null || $this->idFilatureEnCours <= 0) {
			throw new Zend_Exception(get_class($this)." idFilatureEnCours null".$this->request->get("id_filature"));
		}

		Zend_Loader::loadClass("Filature");
		$filatureTable = new Filature();
		$filature = $filatureTable->findByIdBraldunAndIdFilature($this->view->user->id_braldun, $this->idFilatureEnCours);

		if ($filature == null || count($filature) != 1) {
			throw new Zend_Exception(get_class($this)." filature invalide h:".$this->view->user->id_braldun. " q:".$this->idFilatureEnCours);
		}
		
		$this->view->filature = $filature[0];
		$this->prepareHistorique($this->view->filature["id_filature"]);

	}
	
	private function prepareHistorique($idFilature) {
		Zend_Loader::loadClass("HistoriqueFilature");
		$historiqueFilatureTable = new HistoriqueFilature();
		$historique = $historiqueFilatureTable->findByIdFilature($idFilature);

		$this->view->historique = $historique;
	}

	function prepareFormulaire() {}
	function prepareResultat() {}
	function getListBoxRefresh() {}

}