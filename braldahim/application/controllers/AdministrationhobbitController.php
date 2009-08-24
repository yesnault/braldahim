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

		Zend_Loader::loadClass("Bral_Util_Securite");
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
			$modification = "";
				
			$tabPost = $this->_request->getPost();

			$hobbitTable = new Hobbit();
			$hobbitRowset = $hobbitTable->findById($this->_request->getPost('id_hobbit'));
			$hobbit = $hobbitRowset->toArray();

			foreach ($tabPost as $key => $value) {
				if ($key != 'id_hobbit' && mb_substr($key, -7) == "_hobbit") {

					$modification .= "$key avant: ".$hobbit[$key]. " apres:".$value;
					if ($hobbit[$key] != $value) {
						$modification .= " ==> Valeur modifiée";
					}
					$modification .= PHP_EOL;

					if ($value == '') {
						$value = null;
						$data [$key] = $value;
					} else {
						$data [$key] = stripslashes($value);
					}
				}
			}

			$where = "id_hobbit=" . $this->_request->getPost("id_hobbit");
			$hobbitTable->update($data, $where);
			$this->view->modificationHobbit = true;
				
			$config = Zend_Registry::get('config');
			if ($config->general->mail->exception->use == '1') {
				Zend_Loader::loadClass("Bral_Util_Mail");
				$mail = Bral_Util_Mail::getNewZendMail();

				$mail->setFrom($config->general->mail->administration->from, $config->general->mail->administration->nom);
				$mail->addTo($config->general->mail->administration->from, $config->general->mail->administration->nom);
				$mail->setSubject("[Braldahim-Admin Jeu] Administration Hobbit ".$this->_request->getPost("id_hobbit"));
				$texte = "--------> Utilisateur ".$this->view->user->prenom_hobbit." ".$this->view->user->nom_hobbit. " (".$this->view->user->id_hobbit.")".PHP_EOL;
				$texte .= PHP_EOL.$modification;

				$mail->setBodyText($texte);
				$mail->send();
			}
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

		if ($this->_request->get('activation') == "oui") {
			$activation = true;
		} else {
			$activation = false;
		}
			
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
				Zend_Auth::getInstance()->getIdentity()->activation = $activation;
				Zend_Auth::getInstance()->getIdentity()->gardiennage = false;
				Zend_Auth::getInstance()->getIdentity()->gardeEnCours = true;
				Zend_Auth::getInstance()->getIdentity()->administrateur = true;
				Zend_Auth::getInstance()->getIdentity()->usurpationEnCours = true;
				Zend_Auth::getInstance()->getIdentity()->administrationvue = false;
				Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees = null;

				$sessionTable = new Session();
				$where = "id_php_session = '".session_id()."'";
				$sessionTable->delete($where);
					
				$this->_redirect('/');
			}
		}
	}
}

