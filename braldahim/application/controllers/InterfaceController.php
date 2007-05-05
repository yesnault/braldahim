<?php

class InterfaceController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}
	
	function clearAction() {
		echo $this->view->render("interface/clear.phtml");
	}

	function indexAction() {
		$this->view->title = "Profil";
 		$this->render();
	}

	function profilAction() {
		$this->init();
		$xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_entry->set_valeur("box_profil");
		$box = new Bral_Box_Profil($this->_request, $this->view);
		$xml_entry->set_data($box->render());
		$xml_response->add_entry($xml_entry);
		$xml_response->render();
	}	
	
	function equipementAction() {
		$this->init();
		$xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_entry->set_valeur("box_equipement");
		$box = new Bral_Box_Equipement($this->_request, $this->view);
		$xml_entry->set_data($box->render());
		$xml_response->add_entry($xml_entry);
		$xml_response->render();
	}	
		
	function vueAction() {
		$this->init();
		$this->view->affichageInterne = true;
		$xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_entry->set_valeur("box_vue");
		$box = Bral_Box_Factory::getVue($this->_request, $this->view);
		$xml_entry->set_data($box->render());
		$xml_response->add_entry($xml_entry);
		$xml_response->render();
	}
	
	function boxesAction() {
		$this->addBox(Bral_Box_Factory::getEquipement($this->_request, $this->view), "boite_a");
		$this->addBox(Bral_Box_Factory::getProfil($this->_request, $this->view), "boite_a");
		
		$this->addBox(Bral_Box_Factory::getCompetences($this->_request, $this->view, "commun"), "boite_b");
		$this->addBox(Bral_Box_Factory::getCompetences($this->_request, $this->view, "metier"), "boite_b");
		$this->addBox(Bral_Box_Factory::getCompetences($this->_request, $this->view, "basic"), "boite_b");
		
		$this->addBox(Bral_Box_Factory::getVue($this->_request, $this->view), "boite_c");
		
		$xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_entry->set_valeur("racine");
		$xml_entry->set_data($this->getBoxesData());
		
		$xml_response->add_entry($xml_entry);
		$xml_response->render();
	}
	
	private function addBox($p, $position = "aucune") {
   		$this->m_list[$position][] = $p;
	}
	
	private function getBoxesData() {
		$r = $this->getDataList("boite_a");
		$r .= $this->getDataList("boite_b");
		$r .= $this->getDataList("boite_c");
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
		 		
		 		$tab = array ("titre" => $l[$i]->getTitreOnglet(), "nom" => $l[$i]->getNomInterne(), "css" => $css);	
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
		 	 return $this->view->render("interface/box_onglets.phtml");
		 }
	
	}
}

