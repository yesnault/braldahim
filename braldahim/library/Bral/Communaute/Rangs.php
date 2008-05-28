<?php

class Bral_Communaute_Rangs extends Bral_Communaute_Communaute {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass("RangCommunaute");
		Zend_Loader::loadClass("Communaute");

		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		$this->preparePage();
		$this->updateRang();
	}

	function getNomInterne() {
		return "box_communaute_action";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function preparePage() {
		$estGestionnaire = false;
		
		$communauteTable = new Communaute();
		$communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_hobbit);
		if (count($communauteRowset) == 1) {
			$communaute = $communauteRowset[0];
			if ($communaute["id_fk_hobbit_gestionnaire_communaute"] == $this->view->user->id_hobbit) {
				$estGestionnaire = true;
			}
		}
		
		if ($estGestionnaire == false) {
			throw new Zend_Exception(get_class($this)." Vos n'etes pas Gestionnaire");
		}
		if ($communaute == null) {
			throw new Zend_Exception(get_class($this)." Communaute Invalide");
		}
		$this->communaute = $communaute;
	}
	
	function render() {
		$rangCommunauteTable = new RangCommunaute();
		$rangsCommunauteRowset = $rangCommunauteTable->findByIdCommunaute($this->communaute["id_communaute"]);
		$tabRangs = null;

		foreach($rangsCommunauteRowset as $r) {
			$tabRangs[] = array(
				"id_rang" => $r["id_rang_communaute"],
				"nom" => $r["nom_rang_communaute"],
				"description" => $r["description_rang_communaute"],
				"ordre_rang_communaute" => $r["ordre_rang_communaute"],
			);
		}
		
		$this->view->tabRangs = $tabRangs;
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute/gerer/rangs.phtml");
	}
	
	private function updateRang() {
		if (($this->_request->get("caction") == "ask_communaute_rangs") && ($this->_request->get("valeur_1") != "") && ($this->_request->get("valeur_2") != "")) {
			$champ = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_1"));
			$idRang = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
			
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
			$valeur = $filter->filter($this->_request->get('valeur_3'));
		} else {
			return;
		}
		
		if ($champ == 1) {
			if (mb_strlen($valeur) > 40) {
				throw new Zend_Exception(get_class($this)." Valeur invalide : valeur=".$valeur);
			} else {
				$champSql = "nom_rang_communaute";
				$valeurSql = $valeur;
			}
		} elseif ($champ == 2) {
			if (mb_strlen($valeur) > 200) {
				throw new Zend_Exception(get_class($this)." Valeur invalide : valeur=".$valeur);
			} else {
				$champSql = "description_rang_communaute";
				$valeurSql = $valeur;
			}
		} else {
			throw new Zend_Exception(get_class($this)." Champ invalide : champ=".$champ);
		}
		
		$rangCommunauteTable = new RangCommunaute();
		
		$data = array($champSql => $valeurSql);
		$where = " id_rang_communaute=".intval($idRang);
		$where .= " AND id_fk_communaute_rang_communaute=".$this->communaute["id_communaute"];
		$rangCommunauteTable->update($data, $where);
	}
}
