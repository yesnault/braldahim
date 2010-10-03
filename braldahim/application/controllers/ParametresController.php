<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class ParametresController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		$this->initView();
		$this->view->estMobile = Zend_Registry::get("estMobile");

		$this->view->config = Zend_Registry::get('config');
		if ($this->view->config->general->actif != 1) {
			$this->_redirect('/');
		}

		// Fonction non accessible pour les gardiens
		if (Zend_Auth::getInstance()->getIdentity()->gardeEnCours === true) {
			$this->_redirect('/erreur/gardiennage');
		}

		if (Zend_Auth::getInstance()->getIdentity()->est_charte_validee_braldun == "non") {
			$this->_redirect('/charte');
		}

		Zend_Loader::loadClass('Bral_Util_BralSession');
		if (Bral_Util_BralSession::refreshSession() == false) {
			$this->_redirect('/');
		}

		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->controleur = $this->_request->controller;
	}

	function indexAction() {
		$this->render();
	}

	function descriptionAction() {
		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');

		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_StringTrim());

		$valeur = htmlspecialchars($filter->filter($this->_request->getPost("valeur_2")));
			
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();
			
		if ($this->_request->isPost()) {
			$controle = $this->_request->getPost("valeur_1");

			if ($controle == 2) {
				//$valeur = $this->_request->getPost("valeur_2");
				$data = array(
					'description_braldun' => $valeur,
				);
				$where = "id_braldun=".$this->view->user->id_braldun;
				$braldunTable = new Braldun();
				$braldunTable->update($data, $where);

				$this->view->user->description_braldun = $valeur;
			}
		}
		$this->render();
	}

	function imagesAction() {
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();
			
		$this->view->urlAvatarValide = true;
		$this->view->urlBlasonValide = true;
			
		if ($this->_request->isPost()) {
			$urlAvatar = $this->_request->getPost("valeur_1");
			$urlBlason = $this->_request->getPost("valeur_2");

			Zend_Loader::loadClass("Bral_Util_Image");

			if ($urlAvatar != "http://" && $urlAvatar != "") {
				$urlAvatarValide = Bral_Util_Image::controlAvatar($urlAvatar);
			} else {
				$urlAvatar = "http://";
				$urlAvatarValide = true;
			}

			if ($urlBlason != "http://" && $urlBlason != "") {
				$urlBlasonValide = Bral_Util_Image::controlBlason($urlBlason);
			} else {
				$urlBlason = "http://";
				$urlBlasonValide = true;
			}

			if (!$urlAvatarValide) {
				$urlAvatar = "http://";
			}

			if (!$urlBlasonValide) {
				$urlBlason = "http://";
			}

			$data = array(
				'url_avatar_braldun' => $urlAvatar,
				'url_blason_braldun' => $urlBlason,
			);
			$where = "id_braldun=".$this->view->user->id_braldun;
			$braldunTable = new Braldun();
			$braldunTable->update($data, $where);

			$this->view->user->url_avatar_braldun = $urlAvatar;
			$this->view->user->url_blason_braldun = $urlBlason;


			$this->view->urlAvatarValide = $urlAvatarValide;
			$this->view->urlBlasonValide = $urlBlasonValide;
		}
		$this->render();
	}

	function motdepasseAction() {
		if ($this->_request->isPost()) {
			Zend_Loader::loadClass("Bral_Validate_StringLength");
			Zend_Loader::loadClass('Zend_Filter_StripTags');

			$validateurPasswordNouveau = new Bral_Validate_StringLength(5, 20);

			$filter = new Zend_Filter_StripTags();
			$this->password_actuel_braldun = trim($filter->filter(trim($this->_request->getPost('password_actuel_braldun'))));
			$this->password_nouveau_braldun = trim($filter->filter(trim($this->_request->getPost('password_nouveau_braldun'))));
			$this->password_confirm_braldun = trim($filter->filter(trim($this->_request->getPost('password_confirm_braldun'))));

			$validPasswordActuel = false;
			$braldunTable = new Braldun();
			$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
			$braldun = $braldunRowset->current();

			$validPasswordActuel = (md5($this->password_actuel_braldun) == $braldun->password_braldun);
			$validPasswordNouveau = $validateurPasswordNouveau->isValid($this->password_nouveau_braldun);
			$validPasswordConfirm = ($this->password_confirm_braldun == $this->password_nouveau_braldun);

			if (($validPasswordActuel) && ($validPasswordNouveau) && ($validPasswordConfirm)) {

				$data = array(
					'password_braldun' => md5($this->password_nouveau_braldun),
				);
				$where = "id_braldun=".$braldun->id_braldun;
				$braldunTable->update($data, $where);
				$this->view->message = "Votre mot de passe est modifi&eacute;";
				echo $this->view->render("Parametres/index.phtml");
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
					$this->view->messagesPasswordConfirm = "Les deux mots de passe sont diff&eacute;rents";
				}
				$this->view->messagesPasswordNouveau = $tabPassword;
			}
		}

		$this->render();
	}

	function emailAction() {
		$this->email_actuel_braldun = null;
		$this->email_nouveau_braldun = null;
		$this->email_confirm_braldun =  null;

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass("Bral_Validate_StringLength");
			Zend_Loader::loadClass("Bral_Validate_Inscription_EmailBraldun");
			Zend_Loader::loadClass("Zend_Validate_EmailAddress");

			$validateurEmailNouveau = new Bral_Validate_Inscription_EmailBraldun();

			$filter = new Zend_Filter_StripTags();
			$this->password_braldun = trim($filter->filter(trim($this->_request->getPost('email_password_actuel_braldun'))));
			$this->email_actuel_braldun = trim($filter->filter(trim($this->_request->getPost('email_actuel_braldun'))));
			$this->email_nouveau_braldun = trim($filter->filter(trim($this->_request->getPost('email_nouveau_braldun'))));
			$this->email_confirm_braldun = trim($filter->filter(trim($this->_request->getPost('email_confirm_braldun'))));

			$validEmailActuel = false;
			$braldunTable = new Braldun();
			$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
			$braldun = $braldunRowset->current();

			$validPassword = (md5($this->password_braldun) == $braldun->password_braldun);
			$validEmailActuel = ($this->email_actuel_braldun == $braldun->email_braldun);
			$validEmailNouveau = $validateurEmailNouveau->isValid($this->email_nouveau_braldun, ($this->view->config->general->production == 1));
			$validEmailConfirm = ($this->email_confirm_braldun == $this->email_nouveau_braldun);

			if (($validPassword) && ($validEmailActuel) && ($validEmailNouveau) && ($validEmailConfirm)) {

				$data = array(
					'email_braldun' => $this->email_nouveau_braldun,
				);
				$where = "id_braldun=".$braldun->id_braldun;
				$braldunTable->update($data, $where);

				$this->view->message = "L'adresse ".$this->email_nouveau_braldun." est bien prise en compte";
				echo $this->view->render("Parametres/index.phtml");
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

		$this->view->email_actuel_braldun = $this->email_actuel_braldun;
		$this->view->email_nouveau_braldun = $this->email_nouveau_braldun;
		$this->view->email_confirm_braldun = $this->email_confirm_braldun;

		$this->render();
	}

	function reglagesmailAction() {
		$this->view->modification = false;

		$envoi_mail_message = $this->_request->getPost("valeur_1");
		$envoi_mail_evenement = $this->_request->getPost("valeur_2");
		$position_messagerie_braldun = $this->_request->getPost("valeur_3");

		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();
			
		if ($this->_request->isPost()) {
				
			if ($envoi_mail_message != "oui" && $envoi_mail_message != "non") {
				throw new Zend_Exception("Erreur envoi_mail_message:".$envoi_mail_message);
			}

			if ($envoi_mail_evenement != "oui" && $envoi_mail_evenement != "non") {
				throw new Zend_Exception("Erreur envoi_mail_evenement:".$envoi_mail_evenement);
			}

			if ($position_messagerie_braldun != "d" && $position_messagerie_braldun != "b") {
				throw new Zend_Exception("Erreur position_messagerie_braldun:".$position_messagerie_braldun);
			}

			$this->view->user->envoi_mail_message_braldun = $envoi_mail_message;
			$this->view->user->envoi_mail_evenement_braldun = $envoi_mail_evenement;
			$this->view->user->position_messagerie_braldun = $position_messagerie_braldun;

			$data = array(
				'envoi_mail_message_braldun' => $this->view->user->envoi_mail_message_braldun,
				'envoi_mail_evenement_braldun' => $this->view->user->envoi_mail_evenement_braldun,
				'position_messagerie_braldun' => $this->view->user->position_messagerie_braldun,
			);
			$where = "id_braldun=".$this->view->user->id_braldun;
			$braldunTable = new Braldun();
			$braldunTable->update($data, $where);

			$this->view->message = "Réglages des mails pris en compte";
			echo $this->view->render("Parametres/index.phtml");
		} else {
			$this->render();
		}
	}

	function partageAction() {
		Zend_Loader::loadClass("Partage");
		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');

		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_StringTrim());

		$this->view->idPartage = null;
		$this->view->erreurBraldun = false;
		$braldun = null;

		if ($this->_request->isPost()) {
			$idBraldun = intval($filter->filter($this->_request->getPost("id_braldun")));
			$message = htmlspecialchars($filter->filter($this->_request->getPost("message")));

			$braldunTable = new Braldun();
			$braldun = $braldunTable->findById($idBraldun);

			if ($braldun == null || count($braldun) < 1) {
				$this->view->erreurBraldun = true;
			}
		}

		if ($this->_request->isPost() && $this->view->erreurBraldun == false) {

			$data = array(
				'commentaire_partage' => $message,
				'date_declaration_partage' => date("Y-m-d H:i:s"),
				'id_fk_braldun_declarant_partage' => $this->view->user->id_braldun,
				'id_fk_braldun_declare_partage' => $idBraldun,
			);

			$partageTable = new Partage();
			$this->view->idPartage = $partageTable->insert($data);

			Zend_Loader::loadClass("Bral_Util_Mail");
			$mail = Bral_Util_Mail::getNewZendMail();

			$mail->setFrom($this->view->config->general->mail->enqueteurs->from, $this->view->config->general->mail->enqueteurs->nom);
			$mail->addTo($this->view->config->general->mail->enqueteurs->from, $this->view->config->general->mail->enqueteurs->nom);
			$mail->setSubject("[Braldahim-Enqueteur Jeu] Partage n°".$this->view->idPartage);
			$texte = "--------> Braldun déclarant : ".$this->view->user->prenom_braldun." ".$this->view->user->nom_braldun. " (".$this->view->user->id_braldun.")".PHP_EOL;
			$texte .= "--------> Braldun déclaré : ".$braldun->prenom_braldun." ".$braldun->nom_braldun. " (".$braldun->id_braldun.")".PHP_EOL;
			$texte .= "--------> Mail du déclarant : ".$this->view->user->email_braldun.PHP_EOL;
			$texte .= "--------> Mail du déclaré : ".$braldun->email_braldun.PHP_EOL;
			$texte .= "--------> IP du déclarant : ".$_SERVER['REMOTE_ADDR'];
			$texte .= "--------> Message : ".PHP_EOL;
			$texte .= $message.PHP_EOL;

			$mail->setBodyText($texte);
			$mail->send();
		}
		$this->render();
	}

	function contacterAction() {
		Zend_Loader::loadClass("Enquete");
		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');

		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_StringTrim());

		$message = htmlspecialchars($filter->filter($this->_request->getPost("message")));

		$this->view->idEnquete = null;

		if ($this->_request->isPost()) {

			$data = array(
				'message_enquete' => $message,
				'date_enquete' => date("Y-m-d H:i:s"),
				'id_fk_braldun_enquete' => $this->view->user->id_braldun,
			);

			$enqueteTable = new Enquete();
			$this->view->idEnquete = $enqueteTable->insert($data);

			Zend_Loader::loadClass("Bral_Util_Mail");
			$mail = Bral_Util_Mail::getNewZendMail();

			$mail->setFrom($this->view->config->general->mail->enqueteurs->from, $this->view->config->general->mail->enqueteurs->nom);
			$mail->addTo($this->view->config->general->mail->enqueteurs->from, $this->view->config->general->mail->enqueteurs->nom);
			$mail->setSubject("[Braldahim-Enqueteur Jeu] Enquête n°".$this->view->idEnquete);
			$texte = "--------> Braldun ".$this->view->user->prenom_braldun." ".$this->view->user->nom_braldun. " (".$this->view->user->id_braldun.")".PHP_EOL;
			$texte .= "--------> Mail ".$this->view->user->email_braldun.PHP_EOL;
			$texte .= "--------> Message : ".PHP_EOL;
			$texte .= $message.PHP_EOL;

			$mail->setBodyText($texte);
			$mail->send();
		}
		$this->render();
	}
}