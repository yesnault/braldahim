<?php

class InterfaceController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
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
		$this->outputXmlReponse($xml_response);
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
		$this->outputXmlReponse($xml_response);
	}	
	
	function vueAction() {
		$this->init();
		$this->view->affichageInterne = true;
		$xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_entry->set_valeur("box_vue");
		$box = new Bral_Box_Vue($this->_request, $this->view);
		$xml_entry->set_data($box->render());
		$xml_response->add_entry($xml_entry);
		$this->outputXmlReponse($xml_response);
	}
	
	private function outputXmlReponse($xml_response) {
		header("Content-Type: text/xml");
		echo $xml_response->get_xml();
	}
	
	function boxesAction() {
		$this->addBox(new Bral_Box_Equipement($this->_request, $this->view), "boite_a");
		$this->addBox(new Bral_Box_Profil($this->_request, $this->view), "boite_a");
		
		$this->addBox(new Bral_Box_CompetencesBasiques($this->_request, $this->view), "boite_b");
		
		$this->addBox(new Bral_Box_Vue($this->_request, $this->view), "boite_c");
		
		$xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_entry->set_valeur("racine");
		$xml_entry->set_data($this->getBoxesData());
		
		$xml_response->add_entry($xml_entry);
		$this->outputXmlReponse($xml_response);
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

