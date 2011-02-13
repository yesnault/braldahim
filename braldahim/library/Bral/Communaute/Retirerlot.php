<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Retirerlot extends Bral_Communaute_Communaute {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("Lot");

		$id_lot = $this->_request->get("valeur_1");

		if ($this->view->user->rangCommunaute > Bral_Util_Communaute::ID_RANG_TENANCIER) {
			throw new Zend_Exception(get_class($this)." Vous n'êtes pas tenancier de la communauté");
		}

		$lotTable = new Lot();
		$lots = $lotTable->findByIdCommunaute($this->view->user->id_fk_communaute_braldun, $id_lot);

		if ($lots == null || count($lots) != 1) {
			throw new Zend_Exception(get_class($this)." Lot invalide=".$id_lot." idCommunaute:".$this->view->user->id_fk_communaute_braldun);
		}

		$this->lot = $lots[0];
	}

	function prepareFormulaire() {
		throw new Zend_Exception(get_class($this)." Erreur appel");
	}

	function prepareResultat() {
		Zend_Loader::loadClass("Bral_Util_Lot");
		Bral_Util_Lot::transfertLot($this->lot["id_lot"], "coffre", $this->view->user->id_fk_communaute_braldun);
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_lieu", "box_communaute", "box_evenements");
	}

}