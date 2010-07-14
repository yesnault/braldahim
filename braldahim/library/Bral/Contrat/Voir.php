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
class Bral_Contrat_Voir extends Bral_Contrat_Contrat {

	function getNomInterne() {
		return "box_contrat_voir";
	}

	function render() {
		return $this->view->render("contrat/voir.phtml");
	}

	function getTitreAction() {}
	public function calculNbPa() {}

	function prepareCommun() {

		Zend_Loader::loadClass("Bral_Util_Lien");
		
		if ($this->request->get("id_contrat") != "") {
			$this->idContratEnCours =  Bral_Util_Controle::getValeurIntVerif($this->request->get("id_contrat"));
		} else if ($this->idContratDefaut != null) {
			$this->idContratEnCours =  $this->idContratDefaut;
		}
		if ($this->idContratEnCours == null || $this->idContratEnCours <= 0) {
			throw new Zend_Exception(get_class($this)." idContratEnCours null".$this->request->get("id_contrat"));
		}

		Zend_Loader::loadClass("Contrat");
		$contratTable = new Contrat();
		$contrat = $contratTable->findByIdBraldunAndIdContrat($this->view->user->id_braldun, $this->idContratEnCours);

		if ($contrat == null || count($contrat) != 1) {
			throw new Zend_Exception(get_class($this)." contrat invalide h:".$this->view->user->id_braldun. " q:".$this->idContratEnCours);
		}
		
		$this->view->contrat = $contrat[0];

	}
	
	function prepareFormulaire() {}
	function prepareResultat() {}
	function getListBoxRefresh() {}

}