<?php

class Bral_Communaute_Description {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass("HobbitCommunaute");
		Zend_Loader::loadClass("Communaute");
		
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
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

	function getNomInterne() {
		return "box_communaute_action";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function preparePage() {
		$estGestionaire = false;
		
		$hobbitCommunauteTable = new HobbitCommunaute();
		$communauteRowset = $hobbitCommunauteTable->findByIdHobbit($this->view->user->id_hobbit);
		if (count($communauteRowset) > 0) {
			foreach ($communauteRowset as $c) {
				$communaute = $c;
				if ($c["id_fk_rang_communaute_hobbit_communaute"] == 1) { // rang 1 : gestionaire
					$estGestionaire = true;
				}
				break;
			}
		}
		
		if ($estGestionaire == false) {
			throw new Zend_Exception(get_class($this)." Vos n'etes pas Gestionaire");
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
		$champ = $this->_request->getPost("valeur_3");
		
		$communauteTable = new Communaute();
		$data = array("description_communaute" => $champ);
		
		$where = " id_communaute=".$this->communaute["id_communaute"];
		$communauteTable->update($data, $where);
		
		$this->view->isUpdateDescription = true;
	}
}
