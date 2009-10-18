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
class ParametresController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		$this->initView();

		$this->view->config = Zend_Registry::get('config');
		if ($this->view->config->general->actif != 1) {
			$this->_redirect('/');
		}

		// Fonction non accessible pour les gardiens
		if (Zend_Auth::getInstance()->getIdentity()->gardeEnCours === true) {
			$this->_redirect('/erreur/gardiennage');
		}

		if (Zend_Auth::getInstance()->getIdentity()->est_charte_validee_hobbit == "non") {
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
			
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();
			
		if ($this->_request->isPost()) {
			$controle = $this->_request->getPost("valeur_1");

			if ($controle == 2) {
				//$valeur = $this->_request->getPost("valeur_2");
				$data = array(
					'description_hobbit' => $valeur,
				);
				$where = "id_hobbit=".$this->view->user->id_hobbit;
				$hobbitTable = new Hobbit();
				$hobbitTable->update($data, $where);

				$this->view->user->description_hobbit = $valeur;
			}
		}
		$this->render();
	}

	function imagesAction() {
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();
			
		$this->view->urlAvatarValide = true;
		$this->view->urlBlasonValide = true;
			
		if ($this->_request->isPost()) {
			$urlAvatar = $this->_request->getPost("valeur_1");
			$urlBlason = $this->_request->getPost("valeur_2");

			Zend_Loader::loadClass("Bral_Util_Image");

			$urlAvatarValide = Bral_Util_Image::controlAvatar($urlAvatar);
			$urlBlasonValide = Bral_Util_Image::controlBlason($urlBlason);

			if (!$urlAvatarValide) $urlAvatar = $this->view->user->url_avatar_hobbit;
			if (!$urlBlasonValide) $urlBlason = $this->view->user->url_blason_hobbit;

			$data = array(
				'url_avatar_hobbit' => $urlAvatar,
				'url_blason_hobbit' => $urlBlason,
			);
			$where = "id_hobbit=".$this->view->user->id_hobbit;
			$hobbitTable = new Hobbit();
			$hobbitTable->update($data, $where);

			$this->view->user->url_avatar_hobbit = $urlAvatar;
			$this->view->user->url_blason_hobbit = $urlBlason;

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
			$this->password_actuel_hobbit = trim($filter->filter(trim($this->_request->getPost('password_actuel_hobbit'))));
			$this->password_nouveau_hobbit = trim($filter->filter(trim($this->_request->getPost('password_nouveau_hobbit'))));
			$this->password_confirm_hobbit = trim($filter->filter(trim($this->_request->getPost('password_confirm_hobbit'))));

			$validPasswordActuel = false;
			$hobbitTable = new Hobbit();
			$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
			$hobbit = $hobbitRowset->current();

			$validPasswordActuel = (md5($this->password_actuel_hobbit) == $hobbit->password_hobbit);
			$validPasswordNouveau = $validateurPasswordNouveau->isValid($this->password_nouveau_hobbit);
			$validPasswordConfirm = ($this->password_confirm_hobbit == $this->password_nouveau_hobbit);

			if (($validPasswordActuel) && ($validPasswordNouveau) && ($validPasswordConfirm)) {

				$data = array(
					'password_hobbit' => md5($this->password_nouveau_hobbit),
				);
				$where = "id_hobbit=".$hobbit->id_hobbit;
				$hobbitTable->update($data, $where);
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
			$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
			$hobbit = $hobbitRowset->current();

			$validPassword = (md5($this->password_hobbit) == $hobbit->password_hobbit);
			$validEmailActuel = ($this->email_actuel_hobbit == $hobbit->email_hobbit);
			$validEmailNouveau = $validateurEmailNouveau->isValid($this->email_nouveau_hobbit, ($this->view->config->general->production == 1));
			$validEmailConfirm = ($this->email_confirm_hobbit == $this->email_nouveau_hobbit);

			if (($validPassword) && ($validEmailActuel) && ($validEmailNouveau) && ($validEmailConfirm)) {

				$data = array(
					'email_hobbit' => $this->email_nouveau_hobbit,
				);
				$where = "id_hobbit=".$hobbit->id_hobbit;
				$hobbitTable->update($data, $where);

				$this->view->message = "L'adresse ".$this->email_nouveau_hobbit." est bien prise en compte";
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

		$this->view->email_actuel_hobbit = $this->email_actuel_hobbit;
		$this->view->email_nouveau_hobbit = $this->email_nouveau_hobbit;
		$this->view->email_confirm_hobbit = $this->email_confirm_hobbit;

		$this->render();
	}

	function reglagesmailAction() {
		$this->view->modification = false;

		$envoi_mail_message = $this->_request->getPost("valeur_1");
		$envoi_mail_evenement = $this->_request->getPost("valeur_2");

		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();
			
		if ($this->_request->isPost()) {

			$this->view->user->envoi_mail_message_hobbit = $envoi_mail_message;
			$this->view->user->envoi_mail_evenement_hobbit = $envoi_mail_evenement;

			$data = array(
				'envoi_mail_message_hobbit' => $this->view->user->envoi_mail_message_hobbit,
				'envoi_mail_evenement_hobbit' => $this->view->user->envoi_mail_evenement_hobbit,
			);
			$where = "id_hobbit=".$this->view->user->id_hobbit;
			$hobbitTable = new Hobbit();
			$hobbitTable->update($data, $where);

			$this->view->message = "Modifications effectu&eacute;es";
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

		$this->view->idEnquete = null;
		$this->view->erreurHobbit = false;
		$hobbit = null;

		if ($this->_request->isPost()) {
			$idHobbit = intval($filter->filter($this->_request->getPost("id_hobbit")));
			$message = htmlspecialchars($filter->filter($this->_request->getPost("message")));

			$hobbitTable = new Hobbit();
			$hobbit = $hobbitTable->findById($idHobbit);

			if ($hobbit == null || count($hobbit) < 1) {
				$this->view->erreurHobbit = true;
			}
		}

		if ($this->_request->isPost() && $this->view->erreurHobbit == false) {

			$data = array(
				'commentaire_partage' => $message,
				'date_declaration_partage' => date("Y-m-d H:i:s"),
				'id_fk_hobbit_declarant_partage' => $this->view->user->id_hobbit,
				'id_fk_hobbit_declare_partage' => $idHobbit,
			);

			$partageTable = new Partage();
			$this->view->idPartage = $partageTable->insert($data);

			Zend_Loader::loadClass("Bral_Util_Mail");
			$mail = Bral_Util_Mail::getNewZendMail();

			$mail->setFrom($this->view->config->general->mail->enqueteurs->from, $this->view->config->general->mail->enqueteurs->nom);
			$mail->addTo($this->view->config->general->mail->enqueteurs->from, $this->view->config->general->mail->enqueteurs->nom);
			$mail->setSubject("[Braldahim-Enqueteur Jeu] Partage n°".$this->view->idEnquete);
			$texte = "--------> Hobbit déclarant : ".$this->view->user->prenom_hobbit." ".$this->view->user->nom_hobbit. " (".$this->view->user->id_hobbit.")".PHP_EOL;
			$texte = "--------> Hobbit déclaré : ".$hobbit->prenom_hobbit." ".$hobbit->nom_hobbit. " (".$hobbit->id_hobbit.")".PHP_EOL;
			$texte .= "--------> Mail du déclarant : ".$this->view->user->email_hobbit.PHP_EOL;
			$texte .= "--------> Mail du déclaré : ".$hobbit->email_hobbit.PHP_EOL;
			$texte .= "--------> IP du déclarant : ".$_SERVER['REMOTE_ADDR']." Host:".gethostbyaddr($_SERVER['REMOTE_ADDR']).PHP_EOL;
			$texte .= "--------> Message : ".PHP_EOL;
			$texte .= $message.PHP_EOL;

			$mail->setBodyText($texte);
			//$mail->send();
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
				'id_fk_hobbit_enquete' => $this->view->user->id_hobbit,
			);

			$enqueteTable = new Enquete();
			$this->view->idEnquete = $enqueteTable->insert($data);

			Zend_Loader::loadClass("Bral_Util_Mail");
			$mail = Bral_Util_Mail::getNewZendMail();

			$mail->setFrom($this->view->config->general->mail->enqueteurs->from, $this->view->config->general->mail->enqueteurs->nom);
			$mail->addTo($this->view->config->general->mail->enqueteurs->from, $this->view->config->general->mail->enqueteurs->nom);
			$mail->setSubject("[Braldahim-Enqueteur Jeu] Enquête n°".$this->view->idEnquete);
			$texte = "--------> Hobbit ".$this->view->user->prenom_hobbit." ".$this->view->user->nom_hobbit. " (".$this->view->user->id_hobbit.")".PHP_EOL;
			$texte .= "--------> Mail ".$this->view->user->email_hobbit.PHP_EOL;
			$texte .= "--------> Message : ".PHP_EOL;
			$texte .= $message.PHP_EOL;

			$mail->setBodyText($texte);
			$mail->send();
		}
		$this->render();
	}

}