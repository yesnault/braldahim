<?php

class InscriptionController extends Zend_Controller_Action
{

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass('Hobbit');
	}

	function indexAction()
	{
		$this->view->title = "Inscription";
		$hobbit = new Hobbit();
		$this->view->hobbits = $hobbit->fetchAll();
 		$this->render();
	}

	function ajouterAction()
	{
		$this->view->title = "Nouvel Hobbit";

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$filter = new Zend_Filter_StripTags();

			$nom_hobbit = $filter->filter($this->_request->getPost('nom_hobbit'));
			$email_hobbit = trim($filter->filter($this->_request->getPost('email_hobbit')));

			if ($nom_hobbit != '' && $email_hobbit!= '') {
				$data = array(
				'nom_hobbit' => $nom_hobbit,
				'email_hobbit'  => $email_hobbit,
				);
				$hobbit = new Hobbit();
				$hobbit->insert($data);

				$this->_redirect('/');
				return;
			}
		}

		// set up an "empty" Hobbit
		$this->view->hobbit= new stdClass();
		$this->view->hobbit->id = null;
		$this->view->hobbit->nom_hobbit = '';
		$this->view->hobbit->email_hobbit = '';

		// additional view fields required by form
		$this->view->action = 'ajouter';
		$this->view->buttonText = 'Ajouter';

		$this->render();
	}
}

