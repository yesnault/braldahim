<?php

class InscriptionController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass("Bral_Util_De");
		Zend_Loader::loadClass("Bral_Validate_Inscription_EmailHobbit");
		Zend_Loader::loadClass("Bral_Validate_Inscription_NomHobbit");
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Zend_Validate_EmailAddress");
		Zend_Loader::loadClass("Zend_Validate");
		Zend_Loader::loadClass("Bral_Util_Mail");
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("HobbitsCompetences");
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->view->title = "Inscription";
		$this->_redirect('/inscription/ajouter');
	}
	function validationAction() {
		$this->view->title = "Validation de l'inscription";
		$this->view->validationOk = false;
		$this->view->emailMaitreJeu = $this->view->config->general->mail->from_email;

		$email_hobbit = $this->_request->get("e");
		$md5_nom_hobbit = $this->_request->get("h");
		$md5_password_hobbit = $this->_request->get("p");

		$hobbitTable = new Hobbit();
		$hobbit = $hobbitTable->findByEmail($email_hobbit);

		if (count($hobbit) > 0) {
			if ($md5_nom_hobbit == md5($hobbit->nom_hobbit) && ($md5_password_hobbit == $hobbit->password_hobbit)) {
				$this->view->validationOk = true;

				$data = array(
				'est_compte_actif_hobbit' => "oui",
				);
				$where = "id_hobbit=".$hobbit->id_hobbit;
				$hobbitTable->update($data, $where);

				$details = $hobbit->nom_hobbit ." (".$hobbit->id_hobbit.") est apparu sur Braldahim";
				Zend_Loader::loadClass('Evenement');
				$evenementTable = new Evenement();
				$data = array(
				'id_hobbit_evenement' => $hobbit->id_hobbit,
				'date_evenement' => date("Y-m-d H:i:s"),
				'id_fk_type_evenement' => $this->view->config->game->evenements->type->naissance,
				'details_evenement' => $details,
				);
				$evenementTable->insert($data);
			}
		}

		$this->render();
	}

	function ajouterAction() {
		$this->view->title = "Nouvel Hobbit";
		$this->nom_hobbit = "";
		$this->email_hobbit = "";
		$this->email_confirm_hobbit = "";

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$validateurEmail = new Bral_Validate_Inscription_EmailHobbit();
			$validateurNom = new Bral_Validate_Inscription_NomHobbit();
			$validateurPassword = new Bral_Validate_StringLength(5, 20);

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())
			->addFilter(new Zend_Filter_StripTags());
			$this->nom_hobbit = $filter->filter($this->_request->getPost('nom_hobbit'));
			$this->email_hobbit = $filter->filter($this->_request->getPost('email_hobbit'));
			$this->email_confirm_hobbit = $filter->filter($this->_request->getPost('email_confirm_hobbit'));
			$this->password_hobbit = $filter->filter($this->_request->getPost('password_hobbit'));
			$this->password_confirm_hobbit = $filter->filter($this->_request->getPost('password_confirm_hobbit'));
			$this->sexe_hobbit = $filter->filter($this->_request->getPost('sexe_hobbit'));

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

				$hobbitTable = new Hobbit();
				$this->view->id_hobbit = $hobbitTable->insert($data);
				$this->view->nom_hobbit = $this->nom_hobbit;
				$this->view->email_hobbit = $this->email_hobbit;

				$this->initialiseDataHobbitsCompetences();

				$this->envoiEmail();
				echo $this->view->render("inscription/fin.phtml");
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
		$this->view->hobbit->id_hobbit = null;
		$this->view->hobbit->nom_hobbit = $this->nom_hobbit;
		$this->view->hobbit->email_hobbit = $this->email_hobbit;
		$this->view->hobbit->email_confirm_hobbit = $this->email_confirm_hobbit;

		$this->render();
	}

	private function initialiseDataHobbit() {

		$lieuTable = new Lieu();
		$mairiesRowset = $lieuTable->findByType($this->view->config->game->lieu->type->mairie);
		$de = Bral_Util_De::get_de_specifique(0, count($mairiesRowset)-1);
		$lieu = $mairiesRowset[$de];

		$pv = $this->view->config->game->pv_base + 0 * 4;
		$poids = 0 * 2 + 1;
		$armure_nat = 0;
		$reg = 1;
		
		$data = array(
		'nom_hobbit' => $this->nom_hobbit,
		'email_hobbit'  => $this->email_hobbit,
		'password_hobbit'  => md5($this->password_hobbit),
		'est_compte_actif_hobbit'  => "non",
		'castars_hobbit' => $this->view->config->game->inscription->castars,
		'sexe_hobbit' => $this->sexe_hobbit,
		'x_hobbit' => $lieu["x_lieu"],
		'y_hobbit' => $lieu["y_lieu"],
		'vue_bm_hobbit' => $this->view->config->game->inscription->vue_bm,
		'date_debut_tour_hobbit' => date("Y-m-d H:i:s"),
		'date_fin_tour_hobbit' => Bral_Util_ConvertDate::get_date_add_time_to_date(date("Y-m-d H:i:s"), $this->view->config->game->tour->duree_tour_manque),
		'duree_base_tour_hobbit' => $this->view->config->game->tour->duree_base,
		'duree_prochain_tour_hobbit' => $this->view->config->game->inscription->duree_prochain_tour,
		'duree_courant_tour_hobbit' => $this->view->config->game->inscription->duree_courant_tour,
		'date_creation_hobbit' => date("Y-m-d H:i:s"),
		'tour_position_hobbit' => $this->view->config->game->inscription->tour_position,
		'balance_faim_hobbit' => $this->view->config->game->inscription->balance_faim,
		'pv_max_hobbit' => $pv,
		'pv_restant_hobbit' => $pv,
		'force_base_hobbit' => $this->view->config->game->inscription->force_base,
		'agilite_base_hobbit' => $this->view->config->game->inscription->agilite_base,
		'vigueur_base_hobbit' => $this->view->config->game->inscription->vigueur_base,
		'sagesse_base_hobbit' => $this->view->config->game->inscription->sagesse_base,
		'pa_hobbit' => $this->view->config->game->inscription->pa,
		'poids_transportable_hobbit' => $poids,
		'armure_naturelle_hobbit' => $armure_nat,
		'regeneration_hobbit' => $reg,
		);

		return $data;
	}

	private function initialiseDataHobbitsCompetences() {

		$competenceTable = new Competence();
		$tab = $competenceTable->findCommunesInscription(0);

		foreach($tab as $c) {
			$data = array(
			'id_hobbit_hcomp' => $this->view->id_hobbit,
			'id_competence_hcomp'  => $c["id_competence"],
			'pourcentage_hcomp'  => $c["pourcentage_init_competence"],
			'date_gain_tour_hcomp'  => "0000-00-00 00:00:00",
			);

			$hobbitCompetenceTable = new HobbitsCompetences();
			$hobbitCompetenceTable->insert($data);
		}
	}

	private function envoiEmail() {
		$this->view->urlValidation = $this->view->config->general->url;
		$this->view->adresseSupport = $this->view->config->general->adresseSupport;
		$this->view->urlValidation .= "/inscription/validation?e=".$this->email_hobbit;
		$this->view->urlValidation .= "&h=".md5($this->nom_hobbit);
		$this->view->urlValidation .= "&p=".md5($this->password_hobbit);

		$contenuText = $this->view->render("inscription/mailText.phtml");
		$contenuHtml = $this->view->render("inscription/mailHtml.phtml");

		$mail = Bral_Util_Mail::getNewZendMail();
		$mail->setFrom($this->view->config->general->mail->from_email, $this->view->config->general->mail->from_nom);
		$mail->addTo($this->email_hobbit, $this->nom_hobbit);
		$mail->setSubject($this->view->config->game->inscription->titre_mail);
		$mail->setBodyText($contenuText);
		if ($this->view->config->general->envoi_mail_html == true) {
			$mail->setBodyHtml($contenuHtml);
		}
		$mail->send();
	}
}

