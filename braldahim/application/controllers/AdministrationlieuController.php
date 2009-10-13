<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class AdministrationlieuController extends Zend_Controller_Action {

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

	function lieuAction() {
		Zend_Loader::loadClass('Lieu');

		$this->modificationLieu = false;

		if ($this->_request->isPost() && $this->_request->get('idlieu') == $this->_request->getPost("id_lieu")) {
			$modification = "";

			$tabPost = $this->_request->getPost();

			$lieuTable = new Lieu();
			$lieuRowset = $lieuTable->findById($this->_request->getPost('id_lieu'));
			$lieu = $lieuRowset->toArray();

			foreach ($tabPost as $key => $value) {
				if ($key != 'id_lieu' && mb_substr($key, -5) == "_lieu") {

					if ($lieu[$key] != $value) {
						$modification .= " ==> Valeur modifiée : ";
					}
					$modification .= "$key avant: ".$lieu[$key]. " apres:".$value;
					$modification .= PHP_EOL;

					if ($value == '') {
						$value = null;
						$data [$key] = $value;
					} else {
						$data [$key] = stripslashes($value);
					}
				}
			}

			$where = "id_lieu=" . $this->_request->getPost("id_lieu");
			$lieuTable->update($data, $where);
			$this->view->modificationLieu = true;

			$config = Zend_Registry::get('config');
			if ($config->general->mail->exception->use == '1') {
				Zend_Loader::loadClass("Bral_Util_Mail");
				$mail = Bral_Util_Mail::getNewZendMail();

				$mail->setFrom($config->general->mail->administration->from, $config->general->mail->administration->nom);
				$mail->addTo($config->general->mail->administration->from, $config->general->mail->administration->nom);
				$mail->setSubject("[Braldahim-Admin Jeu] Gestion Lieu ".$this->_request->getPost("id_lieu"));
				$texte = "--------> Utilisateur ".$this->view->user->prenom_lieu." ".$this->view->user->nom_lieu. " (".$this->view->user->id_lieu.")".PHP_EOL;
				$texte .= PHP_EOL.$modification;

				$mail->setBodyText($texte);
				$mail->send();
			}
		}

		$this->lieuPrepare();
		$this->render();
	}

	private function lieuPrepare() {

		$this->view->id_lieu = intval($this->_request->get('idlieu'));

		$lieuTable = new Lieu();
		$lieuRowset = $lieuTable->findById($this->view->id_lieu);
		if (count($lieuRowset) == 1) {
			$this->view->lieu = $lieuRowset->toArray();
		} else {
			$this->view->lieu = null;
		}

		if ($this->_request->get('mode') == "" || $this->_request->get('mode') == "simple") {
			$this->view->mode = "simple";
			$keySimple [] = "id_lieu";
			$keySimple [] = "nom_lieu";
			$keySimple [] = "description_lieu";
			$this->view->keySimple = $keySimple;
		} else {
			Bral_Util_Securite::controlAdmin(); // uniquement pour les admin
			$this->view->mode = "complexe";
		}
	}
}

