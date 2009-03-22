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
class Bral_Quete_Voir extends Bral_Quete_Quete {

	function getNomInterne() {
		return "box_quete_interne";
	}

	function render() {
		return $this->view->render("quete/voir.phtml");
	}

	function getTitreAction() {}
	public function calculNbPa() {}

	function prepareCommun() {
		
		if ($this->request->get("id_quete") != "") {
			$this->idQueteEnCours =  Bral_Util_Controle::getValeurIntVerif($this->request->get("id_quete"));
		} else if ($this->idQueteDefaut != null) {
			$this->idQueteEnCours =  $this->idQueteDefaut;
		}
		if ($this->idQueteEnCours == null || $this->idQueteEnCours <= 0) {
			throw new Zend_Exception(get_class($this)." idQueteEnCours null".$this->request->get("id_quete"));
		}
		
		Zend_Loader::loadClass("Quete");
		$queteTable = new Quete();
		
		$quete = $queteTable->findByIdHobbitAndIdQuete($this->view->user->id_hobbit, $this->idQueteEnCours);
		
		if ($quete == null || count($quete) != 1) {
			throw new Zend_Exception(get_class($this)." quete invalide h:".$this->view->user->id_hobbit. " q:".$this->idQueteEnCours);
		}
		$this->view->quete = $quete[0];
		$this->prepareEtapes($this->idQueteEnCours);
		
	}

	function prepareFormulaire() {}
	function prepareResultat() {}
	function getListBoxRefresh() {}
	
	private function prepareEtapes($idQuete) {
		Zend_Loader::loadClass("Etape");
		$etapeTable = new Etape();
		$etapes = $etapeTable->findByIdQuete($idQuete);
		
		$this->view->etapes = $etapes;
	}
}