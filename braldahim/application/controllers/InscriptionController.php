<?php

class InscriptionController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass('Hobbit');
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->view->title = "Inscription";
//		$hobbit = new Hobbit();
//		$this->view->hobbits = $hobbit->fetchAll();
// 		$this->render();
		$this->_redirect('inscription/ajouter');
	}

	function ajouterAction() {
		$this->view->title = "Nouvel Hobbit";

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			
			$validateurEmail = new Zend_Validate_EmailAddress();
			$validateurNom = new Bral_Validate_StringLength(6, 20);
			$validateurPassword = new Bral_Validate_StringLength(6, 20);
			
			$filter = new Zend_Filter_StripTags();
			$this->nom_hobbit = $filter->filter($this->_request->getPost('nom_hobbit'));
			$this->email_hobbit = trim($filter->filter($this->_request->getPost('email_hobbit')));
			$this->password_hobbit = trim($filter->filter($this->_request->getPost('password_hobbit')));
			$this->sexe_hobbit = trim($filter->filter($this->_request->getPost('sexe_hobbit')));
			
			$validNom = $validateurNom->isValid($this->nom_hobbit);
			$validEmail = $validateurEmail->isValid($this->email_hobbit);
			$validPassword = $validateurPassword->isValid($this->password_hobbit);
			if ($this->sexe_hobbit == "feminin" || $this->sexe_hobbit == "masculin") {
				$validSexe = true;
			} else {
				$validSexe = false;
			}
			
			if (($validNom) 
				&& ($validEmail)
				&& ($validPassword)
				&& ($validSexe)) {
					
				$data = $this->initialiseDataHobbit();
				$hobbit = new Hobbit();
				$hobbit->insert($data);
				$this->_redirect('/');
				return;
			} else {
				$tabNom = null;
				$tabPassword = null;
				
				if (count($validateurEmail->getMessages()) > 0) {
					 $this->view->messagesEmail = "Adresse email invalide";
				}
				foreach ($validateurNom->getMessages() as $message) {
					 $tabNom[] = $message;
				}
				foreach ($validateurPassword->getMessages() as $message) {
					 $tabPassword[] = $message;
				}
				if (!$validSexe) {
					$this->view->messagesSexe = "Choisis un genre !";
				}
				$this->view->messagesNom = $tabNom;
				$this->view->messagesPassword = $tabPassword;
			}
		}

		// set up an "empty" Hobbit
		$this->view->hobbit= new stdClass();
		$this->view->hobbit->id = null;
		$this->view->hobbit->nom_hobbit = $this->nom_hobbit;
		$this->view->hobbit->email_hobbit = $this->email_hobbit;

		// additional view fields required by form

		$this->render();
	}
	
	private function initialiseDataHobbit() {
		
		$data = array(
			'nom_hobbit' => $this->nom_hobbit,
			'email_hobbit'  => $this->email_hobbit,
			'password_hobbit'  => md5($this->password_hobbit),
			'est_compte_actif_hobbit'  => "non",
			'x_hobbit' => 0, // A calculer
			'y_hobbit' => 0, // A calculer
			'duree_base_tour_hobbit' => $this->view->config->game->tour->duree_base,
		);
		return $data;
	}
}

