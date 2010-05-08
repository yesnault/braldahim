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
class AdministrationbraldunController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlRole(get_class($this));

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->render();
	}

	function braldunAction() {
		Zend_Loader::loadClass('Braldun');

		$this->modificationBraldun = false;

		if ($this->_request->isPost() && $this->_request->get('idbraldun') == $this->_request->getPost("id_braldun")) {
			$modification = "";

			if (Zend_Auth::getInstance()->getIdentity()->administrateur !== true) { // role testeur
				if ($this->_request->getPost("id_braldun") != $this->view->user->id_braldun) {
					throw new Zend_Exception("Braldun Invalide : (demande)".$this->_request->getPost("id_braldun") ."!= (courant)". $this->view->user->id_braldun);
				}
			}

			$tabPost = $this->_request->getPost();

			$braldunTable = new Braldun();
			$braldunRowset = $braldunTable->findById($this->_request->getPost('id_braldun'));
			$braldun = $braldunRowset->toArray();

			foreach ($tabPost as $key => $value) {
				if ($key != 'id_braldun' && mb_substr($key, -7) == "_braldun") {

					if ($braldun[$key] != $value) {
						$modification .= " ==> Valeur modifiée : ";
					}
					$modification .= "$key avant: ".$braldun[$key]. " apres:".$value;
					$modification .= PHP_EOL;

					if ($value == '') {
						$value = null;
						$data [$key] = $value;
					} else {
						$data [$key] = stripslashes($value);
					}
				}
			}

			$where = "id_braldun=" . $this->_request->getPost("id_braldun");
			$braldunTable->update($data, $where);
			$this->view->modificationBraldun = true;

			$config = Zend_Registry::get('config');
			if ($config->general->mail->exception->use == '1') {
				Zend_Loader::loadClass("Bral_Util_Mail");
				$mail = Bral_Util_Mail::getNewZendMail();

				$mail->setFrom($config->general->mail->administration->from, $config->general->mail->administration->nom);
				$mail->addTo($config->general->mail->administration->from, $config->general->mail->administration->nom);
				$mail->setSubject("[Braldahim-Admin Jeu] Administration Braldun ".$this->_request->getPost("id_braldun"));
				$texte = "--------> Utilisateur ".$this->view->user->prenom_braldun." ".$this->view->user->nom_braldun. " (".$this->view->user->id_braldun.")".PHP_EOL;
				$texte .= PHP_EOL.$modification;

				$mail->setBodyText($texte);
				$mail->send();
			}
		}

		$this->braldunPrepare();
		$this->render();
	}

	private function braldunPrepare() {

		$this->view->id_braldun = intval($this->_request->get('idbraldun'));

		if (!Zend_Auth::getInstance()->getIdentity()->administrateur == true) { // role testeur
			if ($this->view->id_braldun != $this->view->user->id_braldun) {
				throw new Zend_Exception("Braldun Invalide : (demande)".$this->view->id_braldun ."!= (courant)". $this->view->user->id_braldun);
			}
		} 

		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->findById($this->view->id_braldun);
		if (count($braldunRowset) == 1) {
			$this->view->braldun = $braldunRowset->toArray();
		} else {
			$this->view->braldun = null;
		}

		if ($this->_request->get('mode') == "" || $this->_request->get('mode') == "simple") {
			$this->view->mode = "simple";
			$keySimple [] = "id_braldun";
			$keySimple [] = "nom_braldun";
			$keySimple [] = "prenom_braldun";
			$keySimple [] = "x_braldun";
			$keySimple [] = "y_braldun";
			$keySimple [] = "z_braldun";
			$keySimple [] = "pa_braldun";
			$keySimple [] = "date_fin_tour_braldun";
			$keySimple [] = "castars_braldun";
			$keySimple [] = "balance_faim_braldun";
			$keySimple [] = "est_engage_braldun";
			$keySimple [] = "est_intangible_braldun";
			$this->view->keySimple = $keySimple;
		} else {
			Bral_Util_Securite::controlAdmin(); // uniquement pour les admin
			$this->view->mode = "complexe";
		}
	}

	public function usurpationAction() {
		Bral_Util_Securite::controlAdmin();

		Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
		$dbAdapter = Zend_Registry::get('dbAdapter');
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
		$authAdapter->setTableName('braldun');
		$authAdapter->setIdentityColumn('id_braldun');
		$authAdapter->setCredentialColumn('id_braldun');
			
		// Set the input credential values to authenticate against
		$authAdapter->setIdentity($this->_request->get('idbraldun'));
		$authAdapter->setCredential($this->_request->get('idbraldun'));

		if ($this->_request->get('activation') == "oui") {
			$activation = true;
		} else {
			$activation = false;
		}
			
		// authentication
		$auth = Zend_Auth::getInstance();
		$result = $auth->authenticate($authAdapter);
		if ($result->isValid()) {
			$braldun = $authAdapter->getResultRowObject(null,'password_braldun');
			if ($braldun->est_compte_actif_braldun == "oui") {
				$auth->getStorage()->write($braldun);
				// activation du tour

				Zend_Auth::getInstance()->getIdentity()->dateAuth = md5(date("Y-m-d H:i:s"));
				Zend_Auth::getInstance()->getIdentity()->initialCall = true;
				Zend_Auth::getInstance()->getIdentity()->activation = $activation;
				Zend_Auth::getInstance()->getIdentity()->gardiennage = false;
				Zend_Auth::getInstance()->getIdentity()->gardeEnCours = true;
				Zend_Auth::getInstance()->getIdentity()->administrateur = true;
				Zend_Auth::getInstance()->getIdentity()->gestion = ($braldun->sysgroupe_braldun == 'gestion');
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

