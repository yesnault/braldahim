<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class AdministrationblablaController extends Zend_Controller_Action
{

	function init()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');

		Zend_Loader::loadClass('Blabla');

		$blabla["id_blabla"] = -1;
		$blabla["x_blabla"] = 0;
		$blabla["y_blabla"] = 0;
		$blabla["z_blabla"] = 0;
		$blabla["id_fk_braldun_blabla"] = "";
		$blabla["date_blabla"] = "";
		$blabla["message_blabla"] = null;

		$this->view->blabla = $blabla;
	}

	function indexAction()
	{
		$this->render();
	}

	public function estcensureAction()
	{
		$this->updateCensure('oui');
		$this->_forward('blabla');
	}

	public function estnoncensureAction()
	{
		$this->updateCensure('non');
		$this->_forward('blabla');
	}

	private function updateCensure($estCensure)
	{
		if ($this->_request->get('id_blabla')) {

			$this->envoiMailAdmin($estCensure);

			$blablaTable = new Blabla();
			$data = array('est_censure_blabla' => $estCensure);
			$where = 'id_blabla = ' . intval($this->_request->get('id_blabla'));
			$blablaTable->update($data, $where);
		}

	}

	private function envoiMailAdmin($censure = null)
	{
		if ($this->view->config->general->mail->exception->use == '1') {

			if ($censure == null) {
				$modification = "";
			} else {
				$modification = "Passage en censure : " . $censure . " de ce Blabla" . PHP_EOL . PHP_EOL;
			}

			$blablaTable = new Blabla();

			$idBlabla = (int)$this->_request->get('id_blabla');
			$where = $blablaTable->getAdapter()->quoteInto('id_blabla = ?', $idBlabla);
			$blablaRowset = $blablaTable->fetchRow($where);
			$blabla = $blablaRowset->toArray();

			$tabPost = $this->_request->getPost();

			foreach ($blabla as $key => $value) {
				if ($key != 'id_blabla' && mb_substr($key, -7) == "_blabla") {
					$value = $this->_request->get($key);

					if ($censure == null && $blabla[$key] != $value) {
						$modification .= " ==> Valeur modifiée : ";
					}

					if ($censure == null) {
						$modification .= "$key avant: " . $blabla[$key] . " apres:" . $value;
					} else {
						if ($key == "est_censure_blabla") {
							$modification .= "$key : " . $censure;
						} else {
							$modification .= "$key : " . $blabla[$key];
						}

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

			Zend_Loader::loadClass("Bral_Util_Mail");
			$mail = Bral_Util_Mail::getNewZendMail();

			$mail->setFrom($this->view->config->general->mail->administration->from, $this->view->config->general->mail->administration->nom);
			$mail->addTo($this->view->config->general->mail->administration->from, $this->view->config->general->mail->administration->nom);
			$mail->setSubject("[Braldahim-Admin Jeu] Administration BlaBla " . $idBlabla);
			$texte = "--------> Utilisateur " . $this->view->user->prenom_braldun . " " . $this->view->user->nom_braldun . " (" . $this->view->user->id_braldun . ")" . PHP_EOL;
			$texte .= PHP_EOL . $modification;

			$mail->setBodyText($texte);
			$mail->send();
		}
	}

	public function blablaAction()
	{

		if ($this->_request->get('id_blabla')) {
			$blablaTable = new Blabla();
			$blablaRowset = $blablaTable->findById($this->_request->get('id_blabla'));
			$blablaRowset = $blablaRowset->toArray();
			$this->view->blabla = $blablaRowset;
		}

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$creation = true;

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
			$xBlabla = $filter->filter($this->_request->getPost('x_blabla'));
			$yBlabla = $filter->filter($this->_request->getPost('y_blabla'));
			$zBlabla = $filter->filter($this->_request->getPost('z_blabla'));
			$idBraldun = $filter->filter($this->_request->getPost('id_fk_braldun_blabla'));
			$dateBlabla = $filter->filter($this->_request->getPost('date_blabla'));
			$message = $filter->filter($this->_request->getPost('message_blabla'));

			$blablaTable = new Blabla();

			if ($this->_request->get('id_blabla') != -1) {
				$this->envoiMailAdmin();

				$data = array(
					'x_blabla' => $xBlabla,
					'y_blabla' => $yBlabla,
					'z_blabla' => $zBlabla,
					//	'id_fk_braldun_blabla' => $idBraldun,
					//	'date_blabla' => $dateBlabla,
					'message_blabla' => $message,
				);
				$where = 'id_blabla = ' . $this->_request->get('id_blabla');
				$blablaTable->update($data, $where);
				$idBlabla = $this->_request->get('id_blabla');
			}

			$blablaRowset = $blablaTable->findById($idBlabla);
			$blablaRowset = $blablaRowset->toArray();
			$this->view->blabla = $blablaRowset;
		}

		$this->blasblasPrepare();
		$this->render();
	}

	private function blasblasPrepare()
	{
		$blablaTable = new Blabla();
		$blablaRowset = $blablaTable->fetchAll(null, "date_blabla DESC");
		$blabla = null;
		foreach ($blablaRowset as $i) {
			$blabla[] = array(
				"id_blabla" => $i["id_blabla"],
				"x_blabla" => $i["x_blabla"],
				"y_blabla" => $i["y_blabla"],
				"z_blabla" => $i["z_blabla"],
				"id_fk_braldun_blabla" => $i["id_fk_braldun_blabla"],
				"date_blabla" => $i["date_blabla"],
				"message_blabla" => $i["message_blabla"],
				"est_censure_blabla" => $i["est_censure_blabla"],
			);
		}

		$this->view->blasblas = $blabla;
	}
}

