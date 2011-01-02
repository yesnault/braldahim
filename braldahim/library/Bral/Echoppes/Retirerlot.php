<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Echoppes_Retirerlot extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_message_etal";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("Lot");

		$id_echoppe = $this->request->get("valeur_1");
		$id_lot = $this->request->get("valeur_2");

		if ($id_echoppe == "" || $id_echoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide=".$id_echoppe);
		}

		// on verifie que c'est bien l'echoppe du joueur
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdBraldun($this->view->user->id_braldun);

		$echoppeOk = false;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe &&
			$e["x_echoppe"] == $this->view->user->x_braldun &&
			$e["y_echoppe"] == $this->view->user->y_braldun) {
				$echoppeOk = true;
				break;
			}
		}

		if ($echoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Echoppe interdite=".$id_echoppe);
		}

		$lotTable = new Lot();
		$lots = $lotTable->findByIdEchoppe($id_echoppe, $id_lot);

		if ($lots == null || count($lots) != 1) {
			throw new Zend_Exception(get_class($this)." Lot invalide=".$id_lot." idEchoppe:".$id_echoppe);
		}
		
		$this->lot = $lots[0];
		$this->idEchoppe = $id_echoppe;

	}
	
	function prepareFormulaire() {
		throw new Zend_Exception(get_class($this)." Erreur appel");
	}

	function prepareResultat() {
		Zend_Loader::loadClass("Bral_Util_Lot");
		
		Bral_Util_Lot::transfertLot($this->lot["id_lot"], "echoppe", $this->idEchoppe);
	}

	private function calculRetirer($idLot) {

	}

	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_echoppe", "box_echoppes", "box_evenements");
	}
}