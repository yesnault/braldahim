<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class PerduController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		Zend_Loader::loadClass("Bral_Validate_Perdu_EmailBraldun");
		Zend_Loader::loadClass("Bral_Util_Mail");
		Zend_Loader::loadClass("Zend_Validate_EmailAddress");
		Zend_Loader::loadClass("Zend_Validate");
		$this->view->config = Zend_Registry::get('config');
		if ($this->view->config->general->actif != 1) {
			$this->_redirect('/');
		}
	}

	function indexAction()
	{
		$this->view->title = "Mot de passe perdu";
		$this->email_braldun = "";
		$this->email_confirm_braldun = "";

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$validateurEmail = new Bral_Validate_Perdu_EmailBraldun();

			$filter = new Zend_Filter_StripTags();
			$this->email_braldun = trim($filter->filter(trim($this->_request->getPost('email_braldun'))));
			$this->email_confirm_braldun = trim($filter->filter(trim($this->_request->getPost('email_confirm_braldun'))));

			$validEmail = $validateurEmail->isValid($this->email_braldun);
			$validEmailConfirm = ($this->email_confirm_braldun == $this->email_braldun);

			if ($validEmailConfirm && $validEmail) {
				$this->view->emailGenerationOk = false;
				$this->view->message = "";
				$braldunTable = new Braldun();
				$braldun = $braldunTable->findByEmail($this->email_braldun);
				$this->view->email_braldun = $this->email_braldun;

				if (count($braldun) > 0) {
					$this->view->emailGenerationOk = true;
					$this->prenom_braldun = $braldun->prenom_braldun;
					$this->nom_braldun = $braldun->nom_braldun;
					$this->password_hash_braldun = $braldun->password_hash_braldun;
					$this->id_braldun = $braldun->id_braldun;

					try {
						$this->envoiEmailGeneration();
					} catch (Zend_Mail_Protocol_Exception $e) {
						$this->view->emailGenerationOk = false;
						$this->view->message = $e->getMessage();
					}
				}
				echo $this->view->render("Perdu/envoiEmailGeneration.phtml");
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

		$this->view->email_braldun = $this->email_braldun;
		$this->view->email_confirm_braldun = $this->email_confirm_braldun;

		$this->render();
	}

	function generationAction()
	{
		$this->view->title = "Génération d'un nouveau mot de passe";
		$this->view->generationOk = false;
		$this->view->emailMaitreJeu = $this->view->config->general->mail->from_email;
		$this->generationOk = false;

		$this->email_braldun = $this->_request->get("e");
		$md5_prenom_braldun = $this->_request->get("h");
		$password_hash_braldun = $this->_request->get("p");

		$braldunTable = new Braldun();
		$braldun = $braldunTable->findByEmail($this->email_braldun);
		if (count($braldun) > 0) {
			if ($md5_prenom_braldun == md5($braldun->prenom_braldun) && ($password_hash_braldun == $braldun->password_hash_braldun)) {
				$this->view->generationOk = true;
				$this->prenom_braldun = $braldun->prenom_braldun;
				$this->nom_braldun = $braldun->nom_braldun;
				$this->id_braldun = $braldun->id_braldun;
				$this->view->email_braldun = $this->email_braldun;

				Zend_Loader::loadClass('Bral_Util_Hash');
				$salt = Bral_Util_Hash::getSalt();
				$this->password_braldun = Bral_Util_De::get_chaine_aleatoire(6);
				$this->password_hash_braldun = Bral_Util_Hash::getHashString($salt, md5($this->password_braldun));

				$data = array(
					'password_hash_braldun' => $this->password_hash_braldun,
					'password_salt_braldun' => $salt,
				);
				$where = "id_braldun=" . $braldun->id_braldun;
				$braldunTable->update($data, $where);

				$this->envoiEmailNouveauPassword();
			}
		}
		$this->render();
	}

	private function envoiEmailGeneration()
	{
		$this->view->urlGeneration = $this->view->config->general->url;
		$this->view->adresseSupport = $this->view->config->general->adresseSupport;
		$this->view->urlGeneration .= "/Perdu/generation?e=" . $this->email_braldun;
		$this->view->urlGeneration .= "&h=" . md5($this->prenom_braldun);
		$this->view->urlGeneration .= "&p=" . $this->password_hash_braldun;

		$this->view->prenom_braldun = $this->prenom_braldun;
		$this->view->nom_braldun = $this->nom_braldun;
		$this->view->id_braldun = $this->id_braldun;

		$contenuText = $this->view->render("Perdu/mailGenerationText.phtml");
		$contenuHtml = $this->view->render("Perdu/mailGenerationHtml.phtml");

		$mail = Bral_Util_Mail::getNewZendMail();
		$mail->setFrom($this->view->config->general->mail->from_email, $this->view->config->general->mail->from_nom);
		$mail->addTo($this->email_braldun, $this->prenom_braldun);
		$mail->setSubject("Braldahim - Perte de mot de passe");
		$mail->setBodyText($contenuText);
		if ($this->view->config->general->envoi_mail_html == true) {
			$mail->setBodyHtml($contenuHtml);
		}
		$mail->send();
		Bral_Util_Log::mail()->trace("PerduController::envoiEmailGeneration - " . $this->email_braldun . " " . $this->prenom_braldun);
	}

	private function envoiEmailNouveauPassword()
	{
		$this->view->urlValidation = $this->view->config->general->url;
		$this->view->adresseSupport = $this->view->config->general->adresseSupport;
		$this->view->urlValidation .= "/Perdu/generation?e=" . $this->email_braldun;
		$this->view->urlValidation .= "&h=" . md5($this->prenom_braldun);
		$this->view->urlValidation .= "&p=" . $this->password_hash_braldun;

		$this->view->nom_braldun = $this->nom_braldun;
		$this->view->prenom_braldun = $this->prenom_braldun;

		$this->destinataire = $this->prenom_braldun . " " . $this->nom_braldun;

		$this->view->id_braldun = $this->id_braldun;
		$this->view->password_braldun = $this->password_braldun;

		$contenuText = $this->view->render("Perdu/mailNouveauPasswordText.phtml");
		$contenuHtml = $this->view->render("Perdu/mailNouveauPasswordHtml.phtml");

		$mail = Bral_Util_Mail::getNewZendMail();
		$mail->setFrom($this->view->config->general->mail->from_email, $this->view->config->general->mail->from_nom);
		$mail->addTo($this->email_braldun, $this->destinataire);
		$mail->setSubject("Braldahim - Perte de mot de passe");
		$mail->setBodyText($contenuText);
		if ($this->view->config->general->envoi_mail_html == true) {
			$mail->setBodyHtml($contenuHtml);
		}
		$mail->send();
		Bral_Util_Log::mail()->trace("PerduController::envoiEmailNouveauPassword - " . $this->email_braldun . " " . $this->prenom_braldun);
	}
}