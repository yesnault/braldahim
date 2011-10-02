<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class AdministrationbougrieController extends Zend_Controller_Action
{

	function init()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlRole(get_class($this));

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');

		Zend_Loader::loadClass('Bougrie');

		$bougrie["id_bougrie"] = -1;
		$bougrie["texte_bougrie"] = "";
		$bougrie["regle_bougrie"] = "";

		$this->view->bougrie = $bougrie;
	}

	function indexAction()
	{
		$this->render();
	}

	private function envoiMailAdmin($censure = null)
	{
		if ($this->view->config->general->mail->exception->use == '1') {

			$modification = "";

			$bougrieTable = new Bougrie();

			$idBougrie = (int)$this->_request->get('id_bougrie');
			$where = $bougrieTable->getAdapter()->quoteInto('id_bougrie = ?', $idBougrie);
			$bougrieRowset = $bougrieTable->fetchRow($where);
			$bougrie = $bougrieRowset->toArray();

			$tabPost = $this->_request->getPost();

			foreach ($bougrie as $key => $value) {
				if ($key != 'id_bougrie' && mb_substr($key, -7) == "_bougrie") {
					$value = $this->_request->get($key);

					if ($censure == null && $bougrie[$key] != $value) {
						$modification .= " ==> Valeur modifiée : ";
					}

					if ($censure == null) {
						$modification .= "$key avant: " . $bougrie[$key] . " apres:" . $value;
					} else {
						$modification .= "$key : " . $bougrie[$key];
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
			$mail->setSubject("[Braldahim-Admin Jeu] Administration Bougrie " . $idBougrie);
			$texte = "--------> Utilisateur " . $this->view->user->prenom_braldun . " " . $this->view->user->nom_braldun . " (" . $this->view->user->id_braldun . ")" . PHP_EOL;
			$texte .= PHP_EOL . $modification;

			$mail->setBodyText($texte);
			$mail->send();
		}
	}

	public function bougrieAction()
	{

		if ($this->_request->get('id_bougrie') && $this->_request->get('id_bougrie') != -1) {
			$bougrieTable = new Bougrie();
			$bougrieRowset = $bougrieTable->findById($this->_request->get('id_bougrie'));
			$bougrieRowset = $bougrieRowset->toArray();
			$this->view->bougrie = $bougrieRowset;
		}

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$creation = true;

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
			$texte = stripslashes(($filter->filter($this->_request->getPost('texte_bougrie'))));
			$regle = stripslashes(($filter->filter($this->_request->getPost('regle_bougrie'))));

			$bougrieTable = new Bougrie();

			if ($this->_request->get('id_bougrie') != -1) {
				$this->envoiMailAdmin();

				$data = array(
					'texte_bougrie' => $texte,
					'regle_bougrie' => $regle,
				);
				$where = 'id_bougrie = ' . $this->_request->get('id_bougrie');
				$bougrieTable->update($data, $where);
				$idBougrie = $this->_request->get('id_bougrie');
			} else {
				$data = array(
					'texte_bougrie' => $texte,
					'regle_bougrie' => $regle,
				);
				$idBougrie = $bougrieTable->insert($data);
			}

			$bougrieRowset = $bougrieTable->findById($idBougrie);
			$bougrieRowset = $bougrieRowset->toArray();
			$this->view->bougrie = $bougrieRowset;
		}

		$this->bougriesPrepare();
		$this->render();
	}

	private function bougriesPrepare()
	{
		$bougrieTable = new Bougrie();
		$bougrieRowset = $bougrieTable->fetchAll(null, "id_bougrie DESC");
		$bougrie = null;
		foreach ($bougrieRowset as $i) {
			$bougrie[] = array(
				"id_bougrie" => $i["id_bougrie"],
				"texte_bougrie" => $i["texte_bougrie"],
				"regle_bougrie" => $i["regle_bougrie"],
			);
		}

		$this->view->bougries = $bougrie;
	}
}

