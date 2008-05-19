<?php

class InterfaceController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		if (!Zend_Auth::getInstance()->hasIdentity() || !isset($this->view->user) || !isset($this->view->user->email_hobbit)) {
			$this->_redirect('/');
		} else {
			Zend_Loader::loadClass('Bral_Util_BralSession');
			if (Bral_Util_BralSession::refreshSession() == false) {
				$this->_redirect('/');
			} 
		}
		$this->view->config = Zend_Registry::get('config');
		$this->view->controleur = $this->_request->controller;

		$this->infoTour = false;
		
		if ($this->_request->action != 'index') {
			$this->xml_response = new Bral_Xml_Response();
			$t = Bral_Box_Factory::getTour($this->_request, $this->view, false);
			if ($t->activer()) {
				$xml_entry = new Bral_Xml_Entry();
				$xml_entry->set_type("display");
				$xml_entry->set_valeur("informations");
				$xml_entry->set_data($t->render());
				$this->xml_response->add_entry($xml_entry);
				unset($xml_entry);
				$this->infoTour = true;
				
				if ($this->_request->action != 'boxes') {
					$this->refreshAll();
				}
			}
			unset($t);
		}
	}

	function clearAction() {
		if ($this->infoTour == false) {
			$this->render();
		} else {
			$this->xml_response->render();
		}
	}

	function indexAction() {
		$this->render();
	}

	function loadAction() {
		$this->view->affichageInterne = true;
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$nomBox = $this->_request->get("box");
		$box = Bral_Box_Factory::getBox($nomBox, $this->_request, $this->view, true);
		$xml_entry->set_data($box->render());
		$xml_entry->set_valeur($box->getNomInterne());
		$this->xml_response->add_entry($xml_entry);
		unset($xml_entry);
		$this->xml_response->render();
	}
	
	function boxesAction() {
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('HobbitsMetiers');
		
		try {
			$this->addBox(Bral_Box_Factory::getProfil($this->_request, $this->view, false), "boite_a");
			$this->addBox(Bral_Box_Factory::getMetier($this->_request, $this->view, false), "boite_a");
			$this->addBox(Bral_Box_Factory::getEquipement($this->_request, $this->view, false), "boite_a");
			$this->addBox(Bral_Box_Factory::getFamille($this->_request, $this->view, false), "boite_a");
	
			$this->addBox(Bral_Box_Factory::getCompetencesCommun($this->_request, $this->view, false), "boite_b");
			$this->addBox(Bral_Box_Factory::getCompetencesBasic($this->_request, $this->view, false), "boite_b");
			$this->addBox(Bral_Box_Factory::getCompetencesMetier($this->_request, $this->view, false), "boite_b");
	
			$this->addBox(Bral_Box_Factory::getVue($this->_request, $this->view, false), "boite_c");
			$this->addBox(Bral_Box_Factory::getLieu($this->_request, $this->view, false), "boite_c");
			
			// uniquement s'il possède un metier dans les metiers possedant des echoppes
			$hobbitsMetiers = new HobbitsMetiers();
			$possibleEchoppe = $hobbitsMetiers->peutPossederEchoppeIdHobbit($this->view->user->id_hobbit);
			if ($possibleEchoppe === true) {
				$this->addBox(Bral_Box_Factory::getEchoppes($this->_request, $this->view, false), "boite_c");
			}
			unset($hobbitsMetiers);
			
			$this->addBox(Bral_Box_Factory::getLaban($this->_request, $this->view, false), "boite_c");
			
			$charretteTable = new Charrette();
			$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
			if ($nombre > 0) {
				$this->addBox(Bral_Box_Factory::getCharrette($this->_request, $this->view, false), "boite_c");
			}
			unset($charretteTable);
			
			$this->addBox(Bral_Box_Factory::getEvenements($this->_request, $this->view, false), "boite_c");
			$this->addBox(Bral_Box_Factory::getMessagerie($this->_request, $this->view, false), "boite_c");
			$this->addBox(Bral_Box_Factory::getCommunaute($this->_request, $this->view, false), "boite_c");
	
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("display");
			$xml_entry->set_valeur("racine");
			$xml_entry->set_data($this->getBoxesData());
		
		} catch (Zend_Exception $e) {
			$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_valeur($b->getNomInterne());
			$xml_entry->set_data($b->render());
			$xml_response->add_entry($xml_entry);
		}
		
		$this->xml_response->add_entry($xml_entry);
		unset($xml_entry);
		$this->xml_response->render();
	}

	private function addBox($p, $position) {
		$this->m_list[$position][] = $p;
	}

	private function getBoxesData() {
		$r = "<table width='100%'><tr valign='top'><td width='30%'>";
		$r .= $this->getDataList("boite_a");
		$r .= $this->getDataList("boite_b");
		$r .= "</td><td width='70%'>";
		$r .= $this->getDataList("boite_c");
		$r .= "</td></tr></table>";
		return $r;
	}

	private function getDataList($nom) {
		$l = $this->m_list[$nom];
		$liste = "";
		$data = "";
		$onglets = null;

		if ($nom != "aucune") {
			for ($i = 0; $i < count($l); $i ++) {
				if ($i == 0) {
					$css = "actif";
				} else {
					$css = "inactif";
				}
				$tab = array ("titre" => $l[$i]->getTitreOnglet(), "nom" => $l[$i]->getNomInterne(), "css" => $css, "chargementInBoxes" => $l[$i]->getChargementInBoxes());
				$onglets[] = $tab;
				$liste .= $l[$i]->getNomInterne();
				if ($i < count($l)-1 ) {
					$liste .= ",";
				}
			}

			for ($i = 0; $i < count($l); $i ++) {
				if ($i == 0) {
					$display = "block";
				} else {
					$display = "none";
				}

				$l[$i]->setDisplay($display);
				$data .= $l[$i]->render();
			}

			$this->view->onglets = $onglets;
			$this->view->liste = $liste;
			$this->view->data = $data;
			$this->view->conteneur = $nom;
			unset($onglets);
			unset($liste);
			unset($data);
			unset($nom);
			return $this->view->render("interface/box_onglets.phtml");
		}
	}

	private function refreshAll() {
		$boxToRefresh = array("box_profil", "box_metier", "box_equipement", "box_vue", "box_lieu", "box_competences_communes", "box_competences_basiques", "box_competences_metiers", "box_laban", "box_messagerie");
		for ($i=0; $i<count($boxToRefresh); $i++) {
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("display");
			$c = Bral_Box_Factory::getBox($boxToRefresh[$i], $this->_request, $this->view, true);
			$xml_entry->set_valeur($c->getNomInterne());
			$xml_entry->set_data($c->render());
			$this->xml_response->add_entry($xml_entry);
			unset($xml_entry);
			unset($c);
		}
	}
	
	public function reloadAction() {
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("action");
		$xml_entry->set_valeur("goto");
		$xml_entry->set_data("/interface/");
		$this->xml_response->add_entry($xml_entry);
		unset($xml_entry);
		$this->xml_response->render();
	}
}
