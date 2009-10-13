<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
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
			$tabHobbitGarde = null;
			$gardiennageTable = new Gardiennage();
			$gardiennage = $gardiennageTable->findGardeEnCours($this->view->user->id_hobbit);
			
			$dateCourante = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
			
			$uneGardePossible = false;
			foreach($gardiennage as $g) {
				$dateOk = false;
				if ($g["date_debut_gardiennage"] <= $dateCourante && $g["date_fin_gardiennage"] > $dateCourante) {
					$dateOk = true;
					$uneGardePossible = true;
				}
				$tabHobbitGarde[] = array(
					"id_gardiennage" => $g["id_gardiennage"], 
					"id_hobbit" => $g["id_fk_hobbit_gardiennage"], 
					"nom_hobbit" => $g["nom_hobbit"],
					"prenom_hobbit" => $g["prenom_hobbit"],
					"date_debut" => $g["date_debut_gardiennage"],
					"nb_jours" => $g["nb_jours_gardiennage"],
					"commentaire" => $g["commentaire_gardiennage"],
					"date_ok" => $dateOk) ;
			}
			$this->view->tabHobbitGarde = $tabHobbitGarde;
			$this->view->uneGardePossible = $uneGardePossible;
		} else {
			$this->view->message = "Vous n'avez pas activé le gardiennage à la connexion";
		}
 		$this->render();
	}
	
	function gardeAction() {
		$id_garde = intval($this->_request->getPost('id_gardiennage'));
		$id_hobbit = null;
		$email_hobbit = null;
		
		if ($this->view->user->gardeEnCours === true) {
			// rien a faire
		} else if ($this->_request->isPost() && $id_garde > 0) {
			Zend_Loader::loadClass('Zend_Filter_StripTags'); 
            $f = new Zend_Filter_StripTags(); 
            
			// verification que le hobbit peut garder ce hobbit
			$gardiennageTable = new Gardiennage();
			$gardiennage = $gardiennageTable->findGardeEnCours($this->view->user->id_hobbit);
			$garde = false;
			$dateCourante = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
			
			foreach($gardiennage as $g) {
				if ($id_garde == $g["id_gardiennage"] 
					&& $g["date_debut_gardiennage"] <= $dateCourante
					&& $g["date_fin_gardiennage"] > $dateCourante) {
					$garde = true;
					$id_hobbit = $g["id_fk_hobbit_gardiennage"];
					$email_hobbit = $g["email_hobbit"];
				}
			}
			
			// s'il peut garder, on lance l'authentification 
			if ($garde === false || $id_hobbit == null || $email_hobbit == null) {
				$this->view->message = "Erreur. Garde inconnue $id_garde idHobbit=$id_hobbit emailHobbit=$email_hobbit";
			} else {
				Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable'); 
				Zend_Loader::loadClass('Session');
				
				// suppression de la session courante dans la table
	            $sessionTable = new Session();
				$where = "id_fk_hobbit_session = ".$this->view->user->id_hobbit; 
				$sessionTable->delete($where);
				
				Zend_Auth::getInstance()->clearIdentity();
				
	            $dbAdapter = Zend_Registry::get('dbAdapter'); 
	            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter); 
	            $authAdapter->setTableName('hobbit'); 
	            $authAdapter->setIdentityColumn('email_hobbit'); 
	            $authAdapter->setCredentialColumn('id_hobbit'); 
	             
	            // Set the input credential values to authenticate against 
	            $authAdapter->setIdentity($email_hobbit); 
	            $authAdapter->setCredential($id_hobbit); 
	            
	            // authentication  
	            $auth = Zend_Auth::getInstance(); 
	            $result = $auth->authenticate($authAdapter); 
	            if ($result->isValid()) {
	            	$hobbit = $authAdapter->getResultRowObject(null,'password_hobbit'); 
	            	if ($hobbit->est_compte_actif_hobbit == "oui" && $hobbit->est_en_hibernation_hobbit == "non") {
		                $auth->getStorage()->write($hobbit); 
						// activation du tour

						$sessionTable = new Session();
						$data = array("id_fk_hobbit_session" => $hobbit->id_hobbit, "id_php_session" => session_id(), "ip_session" => $_SERVER['REMOTE_ADDR'], "date_derniere_action_session" => date("Y-m-d H:i:s")); 
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

