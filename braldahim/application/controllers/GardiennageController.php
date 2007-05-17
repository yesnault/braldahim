<?php

class GardiennageController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/'); 
		}
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		$this->view->controleur = $this->_request->controller;
		Zend_Loader::loadClass("Gardiennage");
	}
	
	function indexAction() {
		// Si une garde est en cours, on redirige
		if ($this->view->user->gardeEnCours === true) {
			$this->_redirect('/gardiennage/garde'); 
		// Si le gardiennage est active
		} else if ($this->view->user->gardiennage === true) {
			$tabHobbitGarde = null;
			$gardiennageTable = new Gardiennage();
			$gardiennage = $gardiennageTable->findGardeEnCours($this->view->user->id);
			
			$dateCourante = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
			
			foreach($gardiennage as $g) {
				$dateOk = false;
				if ($g["date_debut_gardiennage"] <= $dateCourante) {
					$dateOk = true;
				}
				$tabHobbitGarde[] = array(
					"id_gardiennage" => $g["id"], 
					"id_hobbit" => $g["id_hobbit_gardiennage"], 
					"nom_hobbit" => $g["nom_hobbit"],
					"date_debut" => $g["date_debut_gardiennage"],
					"nb_jours" => $g["nb_jours_gardiennage"],
					"commentaire" => $g["commentaire_gardiennage"],
					"date_ok" => $dateOk) ;
			}
			$this->view->tabHobbitGarde = $tabHobbitGarde;
		} else {
			$this->view->message = "Vous n'avez pas activé le gardiennage à la connexion";
		}
 		$this->render();
	}
	
	function gardeAction() {
		$id_garde = intval($this->_request->getPost('id_gardiennage'));
		
		if ($this->view->user->gardeEnCours === true) {
			// rien a faire
		} else if ($this->_request->isPost() && $id_garde > 0) {
			Zend_Loader::loadClass('Zend_Filter_StripTags'); 
            $f = new Zend_Filter_StripTags(); 
            
			// verification que le hobbit peut garder ce hobbit
			$gardiennageTable = new Gardiennage();
			$gardiennage = $gardiennageTable->findGardeEnCours($this->view->user->id);
			$garde = false;
			$dateCourante = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
			
			foreach($gardiennage as $g) {
				if ($id_garde == $g["id"] 
					&& $g["date_debut_gardiennage"] <= $dateCourante
					&& $g["date_fin_gardiennage"] >= $dateCourante) {
					$garde = true;
					$id_hobbit = $g["id_hobbit_gardiennage"];
					$nom_hobbit = $g["nom_hobbit"];
				}
			}
			
			// s'il peut garde, on lance l'authentification 
			if ($garde === false) {
				$this->view->message = "Erreur. Garde inconnue $id_garde";
			} else {
				Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable'); 
	            $dbAdapter = Zend_Registry::get('dbAdapter'); 
	            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter); 
	            $authAdapter->setTableName('hobbit'); 
	            $authAdapter->setIdentityColumn('nom_hobbit'); 
	            $authAdapter->setCredentialColumn('id'); 
	             
	            // Set the input credential values to authenticate against 
	            $authAdapter->setIdentity($nom_hobbit); 
	            $authAdapter->setCredential($id_hobbit); 
	             
	            // authentication  
	            $auth = Zend_Auth::getInstance(); 
	            $result = $auth->authenticate($authAdapter); 
	            if ($result->isValid()) {
	            	$hobbit = $authAdapter->getResultRowObject(null,'password_hobbit'); 
	            	if ($hobbit->est_compte_actif_hobbit == "oui") {
		                $auth->getStorage()->write($hobbit); 
						// activation du tour
		                Zend_Auth::getInstance()->getIdentity()->activation = ($f->filter($this->_request->getPost('activation_tour_gardiennage')) == 'oui');
	            		Zend_Auth::getInstance()->getIdentity()->gardiennage = false;
	            		Zend_Auth::getInstance()->getIdentity()->gardeEnCours = true;
	            		$this->_redirect('/gardiennage/garde'); 
	            	}
	            }
			}
		}
		$this->render();
	}
}

