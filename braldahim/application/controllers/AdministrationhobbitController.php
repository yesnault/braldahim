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
class AdministrationhobbitController extends Zend_Controller_Action {
	
	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}
	
	function indexAction() {
		$this->render();
	}
	
	function hobbitAction() {
		Zend_Loader::loadClass('Hobbit');
		
		$this->modificationHobbit = false;
		
		if ($this->_request->isPost() && $this->_request->get('idhobbit') == $this->_request->getPost("id_hobbit")) {
			$tabPost = $this->_request->getPost();
			foreach ($tabPost as $key => $value) {
				if ($key != 'id_hobbit' && mb_substr($key, -7) == "_hobbit") {
					if ($value == '') {
						$value = null;
						$data [$key] = $value;
					} else {
						$data [$key] = stripslashes($value);
					}
				}
			}
			
			$hobbitTable = new Hobbit();
			$where = "id_hobbit=" . $this->_request->getPost("id_hobbit");
			$hobbitTable->update($data, $where);
			$this->view->modificationHobbit = true;
		}
		
		$this->hobbitPrepare();
		$this->render();
	}
	
	private function hobbitPrepare() {
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->findById($this->_request->get('idhobbit'));
		if (count($hobbitRowset) == 1) {
			$this->view->hobbit = $hobbitRowset->toArray();
		} else {
			$this->view->hobbit = null;
		}
		$this->view->id_hobbit = $this->_request->get('idhobbit');
		
		if ($this->_request->get('mode') == "" || $this->_request->get('mode') == "simple") {
			$this->view->mode = "simple";
			$keySimple [] = "id_hobbit";
			$keySimple [] = "nom_hobbit";
			$keySimple [] = "prenom_hobbit";
			$keySimple [] = "x_hobbit";
			$keySimple [] = "y_hobbit";
			$keySimple [] = "pa_hobbit";
			$keySimple [] = "date_fin_tour_hobbit";
			$keySimple [] = "castars_hobbit";
			$this->view->keySimple = $keySimple;
		} else {
			$this->view->mode = "complexe";
		}
	}
	
	public function usurpationAction() {
		Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable'); 
		$dbAdapter = Zend_Registry::get('dbAdapter'); 
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter); 
		$authAdapter->setTableName('hobbit'); 
		$authAdapter->setIdentityColumn('id_hobbit'); 
		$authAdapter->setCredentialColumn('id_hobbit'); 
             
		// Set the input credential values to authenticate against 
		$authAdapter->setIdentity($this->_request->get('idhobbit')); 
		$authAdapter->setCredential($this->_request->get('idhobbit')); 
             
		// authentication  
		$auth = Zend_Auth::getInstance(); 
		$result = $auth->authenticate($authAdapter); 
		if ($result->isValid()) {
			$hobbit = $authAdapter->getResultRowObject(null,'password_hobbit'); 
			if ($hobbit->est_compte_actif_hobbit == "oui") {
				$auth->getStorage()->write($hobbit); 
				// activation du tour

				Zend_Auth::getInstance()->getIdentity()->dateAuth = md5(date("Y-m-d H:i:s"));
				Zend_Auth::getInstance()->getIdentity()->initialCall = true;
				Zend_Auth::getInstance()->getIdentity()->activation = false;
	            Zend_Auth::getInstance()->getIdentity()->gardiennage = false;
	            Zend_Auth::getInstance()->getIdentity()->gardeEnCours = true;
	            Zend_Auth::getInstance()->getIdentity()->administrateur = true;
	            Zend_Auth::getInstance()->getIdentity()->usurpationEnCours = true;
	            $this->_redirect('/'); 
			}
		}
	}
}

