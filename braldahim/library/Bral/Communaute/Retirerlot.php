<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Retirerlot extends Bral_Commuanute_Communaute {

	function getNomInterne() {
		return "box_message_communaute";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("Lot");

		$id_lot = $this->request->get("valeur_1");
		
		//TODO verifier les droits du braldûn

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