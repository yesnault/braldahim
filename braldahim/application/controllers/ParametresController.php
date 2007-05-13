<?php

class ParametresController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/'); 
		}
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->config = Zend_Registry::get('config');
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->controleur = $this->_request->controller;
	}

	function indexAction() {
		$this->render();
	}
	
	function motdepasseAction() {
		if ($this->_request->isPost()) {
			Zend_Loader::loadClass("Bral_Validate_StringLength");
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			
			$validateurPasswordNouveau = new Bral_Validate_StringLength(5, 20);
			
			$filter = new Zend_Filter_StripTags();
			$this->password_actuel_hobbit = trim($filter->filter(trim($this->_request->getPost('password_actuel_hobbit'))));
			$this->password_nouveau_hobbit = trim($filter->filter(trim($this->_request->getPost('password_nouveau_hobbit'))));
			$this->password_confirm_hobbit = trim($filter->filter(trim($this->_request->getPost('password_confirm_hobbit'))));
			
			$validPasswordActuel = false;
			$hobbitTable = new Hobbit();
			$hobbitRowset = $hobbitTable->find($this->view->user->id);
			$hobbit = $hobbitRowset->current();
			
			$validPasswordActuel = (md5($this->password_actuel_hobbit) == $hobbit->password_hobbit);
			$validPasswordNouveau = $validateurPasswordNouveau->isValid($this->password_nouveau_hobbit);
			$validPasswordConfirm = ($this->password_confirm_hobbit == $this->password_nouveau_hobbit);
			
			if (($validPasswordActuel) && ($validPasswordNouveau) && ($validPasswordConfirm)) {

				$data = array(
					'password_hobbit' => md5($this->password_nouveau_hobbit),
				);
				$where = "id=".$hobbit->id;
				$hobbitTable->update($data, $where);
				$this->view->message = "Votre mot de passe est modifi&eacute;";
				echo $this->view->render("parametres/index.phtml");
				return;
			} else {
				$tabPassword = null;
				
				if (!$validPasswordActuel) {
					$this->view->messagesPasswordActuel = "Ce n'est pas votre mot de passe actuel !";
				}
				foreach ($validateurPasswordNouveau->getMessages() as $message) {
					 $tabPassword[] = $message;
				}
				if (!$validPasswordConfirm) {
					$this->view->messagesPasswordConfirm = "Les deux mots de passe sont différents";
				}
				$this->view->messagesPasswordNouveau = $tabPassword;
			}
		}
		
		$this->render();
	}
	
	function emailAction() {
		$this->email_actuel_hobbit = null;
		$this->email_nouveau_hobbit = null;
		$this->email_confirm_hobbit =  null;
		
		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass("Bral_Validate_StringLength");
			Zend_Loader::loadClass("Bral_Validate_Inscription_EmailHobbit");
			Zend_Loader::loadClass("Zend_Validate_EmailAddress");
			
			$validateurEmailNouveau = new Bral_Validate_Inscription_EmailHobbit();
			
			$filter = new Zend_Filter_StripTags();
			$this->password_hobbit = trim($filter->filter(trim($this->_request->getPost('email_password_actuel_hobbit'))));
			$this->email_actuel_hobbit = trim($filter->filter(trim($this->_request->getPost('email_actuel_hobbit'))));
			$this->email_nouveau_hobbit = trim($filter->filter(trim($this->_request->getPost('email_nouveau_hobbit'))));
			$this->email_confirm_hobbit = trim($filter->filter(trim($this->_request->getPost('email_confirm_hobbit'))));
			
			$validEmailActuel = false;
			$hobbitTable = new Hobbit();
			$hobbitRowset = $hobbitTable->find($this->view->user->id);
			$hobbit = $hobbitRowset->current();
			
			$validPassword = (md5($this->password_hobbit) == $hobbit->password_hobbit);
			$validEmailActuel = ($this->email_actuel_hobbit == $hobbit->email_hobbit);
			$validEmailNouveau = $validateurEmailNouveau->isValid($this->email_nouveau_hobbit);
			$validEmailConfirm = ($this->email_confirm_hobbit == $this->email_nouveau_hobbit);

			if (($validPassword) && ($validEmailActuel) && ($validEmailNouveau) && ($validEmailConfirm)) {
	
				$data = array(
					'email_hobbit' => $this->email_nouveau_hobbit,
				);
				$where = "id=".$hobbit->id;
				$hobbitTable->update($data, $where);
				$this->view->message = "L'adresse ".$this->email_actuel_hobbit." est bien prise en compte";
				echo $this->view->render("parametres/index.phtml");
				return;
			} else {
				$tabEmail = null;
				
				if (!$validPassword) {
					$this->view->messagesPassword = "Ce n'est pas votre mot de passe !";
				}

				if (!$validEmailActuel) {
					$this->view->messagesEmailActuel = "Ce n'est pas votre adresse mail actuelle !";
				}

				foreach ($validateurEmailNouveau->getMessages() as $message) {
					 $tabEmail[] = $message;
				}
				if (!$validEmailConfirm) {
					$this->view->messagesEmailConfirm = "Les deux adresses sont différentes";
				}
				$this->view->messagesEmailNouveau = $tabEmail;
			}
		}
		
		$this->view->email_actuel_hobbit = $this->email_actuel_hobbit;
		$this->view->email_nouveau_hobbit = $this->email_nouveau_hobbit;
		$this->view->email_confirm_hobbit = $this->email_confirm_hobbit;
		
		$this->render();
	}
}