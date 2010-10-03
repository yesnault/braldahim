<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Soule_Message extends Bral_Soule_Soule {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Message de Soule";
	}

	function prepareCommun() {
	}


	function prepareFormulaire() {
	}

	function prepareResultat() {
		Zend_Loader::loadClass("Zend_Filter_StripTags");
		$filter = new Zend_Filter_StripTags();
		$message = stripslashes(Bral_Util_BBParser::bbcodeStripPlus($this->request->get('valeur_1')));

		if ($message != "") {
			$this->calculMessage($message);
		} else {
			throw new Zend_Exception("message invalide : ".$this->request->get("valeur_1"));
		}
		$this->setEstEvenementAuto(false);
	}

	public function calculNbPa() {
		$this->view->nb_pa = 0;
		$this->view->assezDePa = true;
	}

	private function calculMessage($message) {

		Zend_Loader::loadClass("SouleMessage");
		$souleMessageTable = new SouleMessage();

		$data = array(
			'id_fk_match_soule_message' => $this->view->user->id_fk_soule_match_braldun,
			'id_fk_braldun_soule_message' => $this->view->user->id_braldun,
			'camp_soule_message' => $this->view->user->soule_camp_braldun,
			'date_soule_message' => date("Y-m-d H:i:s"),
			'message_soule_message' => $message, 
		);
		$souleMessageTable->insert($data);
	}

	function getListBoxRefresh() {
		$tab = array("box_soule");
		return $this->constructListBoxRefresh($tab);
	}
}