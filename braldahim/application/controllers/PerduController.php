<?php

class PerduController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass("Bral_Validate_Perdu_EmailHobbit");
		Zend_Loader::loadClass("Zend_Validate_EmailAddress");
		Zend_Loader::loadClass("Zend_Validate");
		Zend_Loader::loadClass("Zend_Mail");
		Zend_Loader::loadClass("Zend_Mail_Transport_Smtp");
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->view->title = "Mot de passe perdu";
		$this->email_hobbit = "aaa";
		$this->email_confirm_hobbit = "bbb";
		
		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$validateurEmail = new Bral_Validate_Perdu_EmailHobbit();
			
			$filter = new Zend_Filter_StripTags();
			$this->email_hobbit = trim($filter->filter(trim($this->_request->getPost('email_hobbit'))));
			$this->email_confirm_hobbit = trim($filter->filter(trim($this->_request->getPost('email_confirm_hobbit'))));
			
			$validEmail = $validateurEmail->isValid($this->email_hobbit);
			$validEmailConfirm = ($this->email_confirm_hobbit == $this->email_hobbit);
			
			if ($validEmailConfirm && $validEmail) {
				$this->view->emailGenerationOk = false;
				$hobbitTable = new Hobbit();
				$hobbit = $hobbitTable->findByEmail($this->email_hobbit);
				$this->view->email_hobbit = $this->email_hobbit;
				
				if (count($hobbit) > 0) {
					$this->view->emailGenerationOk = true;
					$this->nom_hobbit = $hobbit->nom_hobbit;
					$this->password_hobbit = $hobbit->password_hobbit;
					$this->id_hobbit = $hobbit->id;
					$this->envoiEmailGeneration();
				}
				echo $this->view->render("perdu/envoiEmailGeneration.phtml");
				return;
			} else {
				$tabEmail = null;
				foreach ($validateurEmail->getMessages() as $message) {
					 $tabEmail[] = $message;
				}
				if (!$validEmailConfirm) {
					$this->view->messagesEmailConfirm = "Les deux emails sont différents";
				}
				$this->view->messagesEmail = $tabEmail;
			}
		}
		
		$this->view->email_hobbit = $this->email_hobbit ;
		$this->view->email_confirm_hobbit = $this->email_confirm_hobbit ;
		
		$this->render();
	}
	
	function generationAction() {
		$this->view->title = "Génération d'un nouveau mot de passe";
		$this->view->generationOk = false;
		$this->view->emailMaitreJeu = $this->view->config->general->mail->from_email;
		$this->generationOk = false;
		
		$email_hobbit = $this->_request->get("e");
		$md5_nom_hobbit = $this->_request->get("h");
		$md5_password_hobbit = $this->_request->get("p");
		
		$hobbitTable = new Hobbit();
		$hobbit = $hobbitTable->findByEmail($email_hobbit);
		
		if (count($hobbit) > 0) {
			if ($md5_nom_hobbit == md5($hobbit->nom_hobbit) && ($md5_password_hobbit == $hobbit->password_hobbit)) {
				$this->view->generationOk = true;
				
				$new_password = "TODO";
				
				$data = array(
					'password_hobbit' => md5($new_password),
				);
				$where = "id=".$hobbit->id;
				$hobbitTable->update($data, $where);
				
				$this->envoiEmailNouveauPassword();
			}
		}
		$this->render();
	}
	
	private function envoiEmailGeneration() {
		$this->view->urlGeneration = $this->view->config->general->url;
		$this->view->adresseSupport = $this->view->config->general->adresseSupport;
		$this->view->urlGeneration .= "/perdu/generation?e=".$this->email_hobbit;
		$this->view->urlGeneration .= "&h=".md5($this->nom_hobbit);
		$this->view->urlGeneration .= "&p=".md5($this->password_hobbit);
		
		$this->view->nom_hobbit = $this->nom_hobbit;
		$this->view->id_hobbit = $this->id_hobbit;
		
		$contenuText = $this->view->render("perdu/mailGenerationText.phtml");
		$contenuHtml = $this->view->render("perdu/mailGenerationHtml.phtml");
		
		$transport = new Zend_Mail_Transport_Smtp($this->view->config->general->mail->smtp_server);
		Zend_Mail::setDefaultTransport($transport);
		$mail = new Zend_Mail();
		$mail->setFrom($this->view->config->general->mail->from_email, $this->view->config->general->mail->from_nom);
		$mail->addTo($this->email_hobbit, $this->nom_hobbit);
		$mail->setSubject($this->view->config->game->perdu->titre_mail);
		$mail->setBodyText($contenuText);
		if ($this->view->config->general->envoi_mail_html == true) {
			$mail->setBodyHtml($contenuHtml);
		}
		$mail->send();
	}
	
	private function envoiEmailNouveauPassword() {
		$this->view->urlValidation = $this->view->config->general->url;
		$this->view->adresseSupport = $this->view->config->general->adresseSupport;
		$this->view->urlValidation .= "/perdu/generation?e=".$this->email_hobbit;
		$this->view->urlValidation .= "&h=".md5($this->nom_hobbit);
		$this->view->urlValidation .= "&p=".md5($this->password_hobbit);
		
		$contenuText = $this->view->render("perdu/mailNouveauPasswordText.phtml");
		$contenuHtml = $this->view->render("perdu/mailNouveauPasswordHtml.phtml");
		
		$transport = new Zend_Mail_Transport_Smtp($this->view->config->general->mail->smtp_server);
		Zend_Mail::setDefaultTransport($transport);
		$mail = new Zend_Mail();
		$mail->setFrom($this->view->config->general->mail->from_email, $this->view->config->general->mail->from_nom);
		$mail->addTo($this->email_hobbit, $this->nom_hobbit);
		$mail->setSubject($this->view->config->game->perdu->titre_mail);
		$mail->setBodyText($contenuText);
		if ($this->view->config->generation->envoi_mail_html == true) {
			$mail->setBodyHtml($contenuHtml);
		}
		$mail->send();
	}
}

