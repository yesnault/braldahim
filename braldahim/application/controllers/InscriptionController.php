<?php

class InscriptionController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass("Bral_Validate_Inscription_EmailHobbit");
		Zend_Loader::loadClass("Bral_Validate_Inscription_NomHobbit");
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Zend_Validate_EmailAddress");
		Zend_Loader::loadClass("Zend_Validate");
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
		
		$this->nom_hobbit = "";
		$this->email_hobbit = "";
		$this->email_confirm_hobbit = "";
		
		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			
			$validateurEmail = new Bral_Validate_Inscription_EmailHobbit();
			$validateurNom = new Bral_Validate_Inscription_NomHobbit();
			$validateurPassword = new Bral_Validate_StringLength(5, 20);
			
			$filter = new Zend_Filter_StripTags();
			$this->nom_hobbit = $filter->filter(trim($this->_request->getPost('nom_hobbit')));
			$this->email_hobbit = trim($filter->filter(trim($this->_request->getPost('email_hobbit'))));
			$this->email_confirm_hobbit = trim($filter->filter(trim($this->_request->getPost('email_confirm_hobbit'))));
			$this->password_hobbit = trim($filter->filter(trim($this->_request->getPost('password_hobbit'))));
			$this->password_confirm_hobbit = trim($filter->filter(trim($this->_request->getPost('password_confirm_hobbit'))));
			$this->sexe_hobbit = trim($filter->filter($this->_request->getPost('sexe_hobbit')));
			
			$validNom = $validateurNom->isValid($this->nom_hobbit);
			$validEmail = $validateurEmail->isValid($this->email_hobbit);
			$validPassword = $validateurPassword->isValid($this->password_hobbit);
			
			$validEmailConfirm = ($this->email_confirm_hobbit == $this->email_hobbit);
			$validPasswordConfirm = ($this->password_confirm_hobbit == $this->password_hobbit);
			
			if ($this->sexe_hobbit == "feminin" || $this->sexe_hobbit == "masculin") {
				$validSexe = true;
			} else {
				$validSexe = false;
			}
			
			if (($validNom) 
				&& ($validEmail)
				&& ($validPassword)
				&& ($validSexe)
				&& ($validEmailConfirm)
				&& ($validPasswordConfirm)) {
					
				$data = $this->initialiseDataHobbit();
				$hobbit = new Hobbit();
				$hobbit->insert($data);
				$this->_redirect('/');
				return;
			} else {
				$tabNom = null;
				$tabEmail = null;
				$tabPassword = null;
				
				foreach ($validateurEmail->getMessages() as $message) {
					 $tabEmail[] = $message;
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
				if (!$validEmailConfirm) {
					$this->view->messagesEmailConfirm = "Les deux emails sont différents";
				}
				if (!$validPasswordConfirm) {
					$this->view->messagesPasswordConfirm = "Les deux mots de passe sont différents";
				}
				$this->view->messagesNom = $tabNom;
				$this->view->messagesEmail = $tabEmail;
				$this->view->messagesPassword = $tabPassword;
			}
		}
		
		// hobbit par défaut
		$this->view->hobbit= new stdClass();
		$this->view->hobbit->id = null;
		$this->view->hobbit->nom_hobbit = $this->nom_hobbit;
		$this->view->hobbit->email_hobbit = $this->email_hobbit;
		$this->view->hobbit->email_confirm_hobbit = $this->email_confirm_hobbit;

		$this->render();
	}

	private function initialiseDataHobbit() {
		
		$lieuTable = new Lieu();
		$ahenne_peheux_rowset = $lieuTable->findByType($this->view->config->game->lieu->type->ahenne_peheux);
		$de = Bral_Util_De::get_de_specifique(0, count($ahenne_peheux_rowset)-1);
		$ahenne_peheux_array = $ahenne_peheux_rowset->toArray();
		$lieu = $ahenne_peheux_array[$de];
		
		$data = array(
			'nom_hobbit' => $this->nom_hobbit,
			'email_hobbit'  => $this->email_hobbit,
			'password_hobbit'  => md5($this->password_hobbit),
			'est_compte_actif_hobbit'  => "non",
			'x_hobbit' => $lieu["x_lieu"], 
			'y_hobbit' => $lieu["y_lieu"], 
			'vue_base_hobbit' => $this->view->config->game->inscription->vue_base, 
			'vue_bm_hobbit' => $this->view->config->game->inscription->vue_bm,
			'date_debut_tour_hobbit' => date("Y-m-d H:i:s"),
			'date_fin_tour_hobbit' => date("Y-m-d H:i:s"),
			'duree_base_tour_hobbit' => $this->view->config->game->tour->duree_base,
			'duree_prochain_tour_hobbit' => $this->view->config->game->inscription->duree_prochain_tour,
			'duree_courant_tour_hobbit' => $this->view->config->game->inscription->duree_courant_tour,
			'date_creation_hobbit' => date("Y-m-d H:i:s"),
			'tour_position_hobbit' => $this->view->config->game->inscription->tour_position,
		);
		return $data;
	}
}

