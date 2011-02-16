<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Description extends Bral_Communaute_Communaute {

	function getTitre() {
		return null;
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass("Communaute");

		$this->preparePage();

		$this->view->isUpdateDescription = false;
		$this->view->isUpdateSiteWeb = false;

		if ($this->_request->get("caction") == "do_communaute_description") {
			if ($this->_request->getPost("valeur_1") == "1") {
				$this->updateSiteWeb();
			} elseif ($this->_request->getPost("valeur_1") == "2") {
				$this->updateDescription();
			}
		}
	}

	function prepareFormulaire() {}
	function prepareResultat() {}

	function getNomInterne() {
		return "box_communaute_action";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function preparePage() {
		Zend_Loader::loadClass('Bral_Util_Communaute');

		$communauteTable = new Communaute();
		$communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
		if (count($communauteRowset) == 1) {
			$communaute = $communauteRowset[0];
		}

		if ($this->view->user->rangCommunaute > Bral_Util_Communaute::ID_RANG_ADJOINT) {
			throw new Zend_Exception(get_class($this)." Vos n'etes pas gestionnaire ou adjoint");
		}
		if ($communaute == null) {
			throw new Zend_Exception(get_class($this)." Communaute Invalide");
		}

		$this->communaute = $communaute;
	}

	function render() {
		$c = array(
			"description" => $this->communaute["description_communaute"], 
			"site_web" => $this->communaute["site_web_communaute"]
		);
		$this->view->communaute = $c;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute/gerer/description.phtml");
	}

	private function updateSiteWeb() {
		$champ = $this->_request->getPost("valeur_2");

		$communauteTable = new Communaute();
		$data = array("site_web_communaute" => $champ);

		$where = " id_communaute=".$this->communaute["id_communaute"];
		$communauteTable->update($data, $where);

		$this->view->isUpdateSiteWeb = true;
	}

	private function updateDescription() {
		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');

		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_StringTrim())
		->addFilter(new Zend_Filter_StripTags());

		$valeur = stripslashes($filter->filter($this->_request->getPost("valeur_3")));

		$champ = Bral_Util_BBParser::bbcodeStripPlus($valeur);

		$communauteTable = new Communaute();
		$data = array("description_communaute" => $champ);

		$where = " id_communaute=".$this->communaute["id_communaute"];
		$communauteTable->update($data, $where);

		$this->view->isUpdateDescription = true;
	}
}
