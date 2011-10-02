<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class SondageController extends Zend_Controller_Action
{

	function init()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		if ($this->view->config->general->actif != 1) {
			$this->_redirect('/');
		}

		if ($this->view->user->est_sondage_valide_braldun == 'oui') {
			$this->_redirect('/');
		}

		$this->view->controleur = $this->_request->controller;
	}

	function indexAction()
	{
		Zend_Loader::loadClass("Sondage");
		$sondageTable = new Sondage();
		$sondageEnCours = $sondageTable->findEnCours();

		if ($sondageEnCours == null || count($sondageEnCours) != 1) {
			$this->updateBraldunValide();
			$this->_redirect('/');
		} else {
			$this->view->sondage = $sondageEnCours[0];
			if ($this->_request->isPost()) {
				$this->valider();
			}
			$this->render();
		}
	}

	function finAction()
	{
		$this->render();
	}

	function valider()
	{
		$this->view->erreur = "";
		if ($this->_request->isPost()) {
			// mise à jour reponse.

			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());

			$commentaire = $filter->filter($this->_request->getPost('commentaire'));

			$nbReponse = 3;
			for ($i = 1; $i <= $nbReponse; $i++) {
				//if (((int)$this->_request->getPost("reponse_$i").""!=$this->_request->getPost("reponse_$i")."") || (int)$this->_request->getPost("reponse_$i") < 0) {
				if (($this->_request->getPost("reponse_$i") . "" != "oui") &&
					($this->_request->getPost("reponse_$i") . "" != "non")
				) {
					$this->view->erreur .= "Reponse $i invalide<br />";
				} else {
					$reponse[$i] = $this->_request->getPost("reponse_$i");
				}
			}

			if ($this->view->erreur == "") { // si reponse valide

				Zend_Loader::loadClass("SondageReponse");
				$sondageReponseTable = new SondageReponse();

				$data = array(
					'id_fk_sondage_reponse' => $this->view->sondage["id_sondage"],
					'id_fk_braldun_sondage_reponse' => $this->view->user->id_braldun,
					'commentaire_braldun_sondage_reponse' => $commentaire,
					'date_sondage_reponse' => date("Y-m-d H:i:s"),
				);

				for ($i = 1; $i <= $nbReponse; $i++) {
					$data["reponse_" . $i . "_sondage_reponse"] = $reponse[$i];
				}

				$sondageReponseTable->insert($data);

				$this->updateBraldunValide();
				$this->_redirect('/sondage/fin');
			}
		} else {
			$this->_redirect('/auth/logout');
		}
	}

	private function updateBraldunValide()
	{
		$braldunTable = new Braldun();
		$data = array("est_sondage_valide_braldun" => "oui");
		$where = "id_braldun=" . $this->view->user->id_braldun;
		$braldunTable->update($data, $where);
	}
}