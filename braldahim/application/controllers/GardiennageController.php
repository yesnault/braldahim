<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class GardiennageController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/'); 
		}
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		if ($this->view->config->general->actif != 1) {
			$this->_redirect('/');
		}
		
		$this->view->controleur = $this->_request->controller;
		Zend_Loader::loadClass("Gardiennage");
	}
	
	function indexAction() {
		// Si une garde est en cours, on redirige
		if ($this->view->user->gardeEnCours === true) {
			$this->_redirect('/Gardiennage/garde'); 
		// Si le gardiennage est active
		} else if ($this->view->user->gardiennage === true) {
			$tabBraldunGarde = null;
			$gardiennageTable = new Gardiennage();
			$gardiennage = $gardiennageTable->findGardeEnCours($this->view->user->id_braldun);
			
			$dateCourante = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
			
			$uneGardePossible = false;
			foreach($gardiennage as $g) {
				$dateOk = false;
				if ($g["date_debut_gardiennage"] <= $dateCourante && $g["date_fin_gardiennage"] >= $dateCourante) {
					$dateOk = true;
					$uneGardePossible = true;
				}
				$tabBraldunGarde[] = array(
					"id_gardiennage" => $g["id_gardiennage"], 
					"id_braldun" => $g["id_fk_braldun_gardiennage"], 
					"nom_braldun" => $g["nom_braldun"],
					"prenom_braldun" => $g["prenom_braldun"],
					"date_debut" => $g["date_debut_gardiennage"],
					"nb_jours" => $g["nb_jours_gardiennage"],
					"commentaire" => $g["commentaire_gardiennage"],
					"date_ok" => $dateOk) ;
			}
			$this->view->tabBraldunGarde = $tabBraldunGarde;
			$this->view->uneGardePossible = $uneGardePossible;
		} else {
			$this->view->message = "Vous n'avez pas activé le gardiennage à la connexion";
		}
 		$this->render();
	}
	
	function gardeAction() {
		$id_garde = intval($this->_request->getPost('id_gardiennage'));
		$id_braldun = null;
		$email_braldun = null;
		
		if ($this->view->user->gardeEnCours === true) {
			// rien a faire
		} else if ($this->_request->isPost() && $id_garde > 0) {
			Zend_Loader::loadClass('Zend_Filter_StripTags'); 
            $f = new Zend_Filter_StripTags(); 
            
			// verification que le Braldûn peut garder ce braldun
			$gardiennageTable = new Gardiennage();
			$gardiennage = $gardiennageTable->findGardeEnCours($this->view->user->id_braldun);
			$garde = false;
			$dateCourante = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
			
			foreach($gardiennage as $g) {
				if ($id_garde == $g["id_gardiennage"] 
					&& $g["date_debut_gardiennage"] <= $dateCourante
					&& $g["date_fin_gardiennage"] >= $dateCourante) {
					$garde = true;
					$id_braldun = $g["id_fk_braldun_gardiennage"];
					$email_braldun = $g["email_braldun"];
				}
			}
			
			// s'il peut garder, on lance l'authentification 
			if ($garde === false || $id_braldun == null || $email_braldun == null) {
				$this->view->message = "Erreur. Garde inconnue $id_garde idBraldun=$id_braldun emailBraldun=$email_braldun";
			} else {
				Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable'); 
				Zend_Loader::loadClass('Session');
				
				// suppression de la session courante dans la table
	            $sessionTable = new Session();
				$where = "id_fk_braldun_session = ".$this->view->user->id_braldun; 
				$sessionTable->delete($where);
				
				Zend_Auth::getInstance()->clearIdentity();
				
	            $dbAdapter = Zend_Registry::get('dbAdapter'); 
	            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter); 
	            $authAdapter->setTableName('braldun'); 
	            $authAdapter->setIdentityColumn('email_braldun'); 
	            $authAdapter->setCredentialColumn('id_braldun'); 
	             
	            // Set the input credential values to authenticate against 
	            $authAdapter->setIdentity($email_braldun); 
	            $authAdapter->setCredential($id_braldun); 
	            
	            // authentication  
	            $auth = Zend_Auth::getInstance(); 
	            $result = $auth->authenticate($authAdapter); 
	            if ($result->isValid()) {
	            	$braldun = $authAdapter->getResultRowObject(null,'password_braldun'); 
	            	if ($braldun->est_compte_actif_braldun == "oui" && $braldun->est_en_hibernation_braldun == "non") {
		                $auth->getStorage()->write($braldun); 
						// activation du tour

						$sessionTable = new Session();
						$data = array("id_fk_braldun_session" => $braldun->id_braldun, "id_php_session" => session_id(), "ip_session" => $_SERVER['REMOTE_ADDR'], "date_derniere_action_session" => date("Y-m-d H:i:s")); 
						$sessionTable->insertOrUpdate($data);
					
		                Zend_Auth::getInstance()->getIdentity()->dateAuth = md5(date("Y-m-d H:i:s"));
						Zend_Auth::getInstance()->getIdentity()->initialCall = true;
		                Zend_Auth::getInstance()->getIdentity()->activation = ($f->filter($this->_request->getPost('activation_tour_gardiennage')) == 'oui');
	            		Zend_Auth::getInstance()->getIdentity()->gardiennage = false;
	            		Zend_Auth::getInstance()->getIdentity()->gardeEnCours = true;
	            		Zend_Auth::getInstance()->getIdentity()->administrateur = false;
	            		Zend_Auth::getInstance()->getIdentity()->gestion = false;
	            		Zend_Auth::getInstance()->getIdentity()->usurpationEnCours = false;
	            		Zend_Auth::getInstance()->getIdentity()->administrationvue = false;
	            		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees = null;
	            		$this->_redirect('/Gardiennage/garde'); 
	            	}
	            }
			}
		}
		$this->render();
	}
}

