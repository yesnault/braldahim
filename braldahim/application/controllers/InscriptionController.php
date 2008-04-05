<?php

class InscriptionController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		Zend_Loader::loadClass("Bral_Validate_Inscription_EmailHobbit");
		Zend_Loader::loadClass("Bral_Validate_Inscription_PrenomHobbit");
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Zend_Validate_EmailAddress");
		Zend_Loader::loadClass("Zend_Validate");
		Zend_Loader::loadClass("Bral_Util_Mail");
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("HobbitsCompetences");
		Zend_Loader::loadClass("Couple");
		Zend_Loader::loadClass("Nom");
		Zend_Loader::loadClass("Region");
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->view->title = "Inscription";
		$this->_redirect('/inscription/ajouter');
	}
	
	function validationAction() {
		Bral_Util_Log::inscription()->trace("InscriptionController - validationAction - enter");
		$this->view->title = "Validation de l'inscription";
		$this->view->validationOk = false;
		$this->view->emailMaitreJeu = $this->view->config->general->mail->from_email;
		$this->view->compteActif = false;
		
		$email_hobbit = $this->_request->get("e");
		$md5_prenom_hobbit = $this->_request->get("h");
		$md5_password_hobbit = $this->_request->get("p");

		$hobbitTable = new Hobbit();
		$hobbit = $hobbitTable->findByEmail($email_hobbit);
		
		if ($hobbit->est_compte_actif_hobbit == 'non') {
			if (count($hobbit) > 0) {
				if ($md5_prenom_hobbit == md5($hobbit->prenom_hobbit) && ($md5_password_hobbit == $hobbit->password_hobbit)) {
					$this->view->validationOk = true;
					
					$dataParents = $this->calculParent($hobbit->id_hobbit);
			
					$data = array(
						'est_compte_actif_hobbit' => "oui",
						'id_fk_pere_hobbit' => $dataParents["id_fk_pere_hobbit"],
						'id_fk_mere_hobbit' => $dataParents["id_fk_mere_hobbit"],
					);
					$where = "id_hobbit=".$hobbit->id_hobbit;
					$hobbitTable->update($data, $where);
	
					$details = $hobbit->prenom_hobbit ." ".$hobbit->nom_hobbit." (".$hobbit->id_hobbit.") est apparu sur Braldahim";
					Zend_Loader::loadClass('Evenement');
					$evenementTable = new Evenement();
					$data = array(
						'id_fk_hobbit_evenement' => $hobbit->id_hobbit,
						'date_evenement' => date("Y-m-d H:i:s"),
						'id_fk_type_evenement' => $this->view->config->game->evenements->type->naissance,
						'details_evenement' => $details,
					);
					$evenementTable->insert($data);
					Bral_Util_Log::inscription()->notice("InscriptionController - validationAction - validation OK pour :".$email_hobbit);
				}
			}
		} else {
			Bral_Util_Log::tech()->notice("InscriptionController - validationAction - compte deja active");
			$this->view->compteActif = true;
		}
		Bral_Util_Log::inscription()->trace("InscriptionController - validationAction - exit");
		$this->render();
	}

	function ajouterAction() {
		Bral_Util_Log::inscription()->trace("InscriptionController - ajouterAction - enter");
		$this->view->title = "Nouvel Hobbit";
		$this->prenom_hobbit = "";
		$this->email_hobbit = "";
		$this->email_confirm_hobbit = "";
		$this->id_region = -1;
		
		$regionTable =  new Region();
		$regionsRowset = $regionTable->fetchAll();
		$regionsRowset = $regionsRowset->toArray();
		
		$regions = null;
		foreach ($regionsRowset as $r) {
			$regions[$r["id_region"]] = $r["nom_region"];		
		}

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$validateurEmail = new Bral_Validate_Inscription_EmailHobbit();
			$validateurPrenom = new Bral_Validate_Inscription_PrenomHobbit();
			$validateurPassword = new Bral_Validate_StringLength(5, 20);

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())
			->addFilter(new Zend_Filter_StripTags());
			$this->prenom_hobbit = $filter->filter($this->_request->getPost('prenom_hobbit'));
			$this->email_hobbit = $filter->filter($this->_request->getPost('email_hobbit'));
			$this->email_confirm_hobbit = $filter->filter($this->_request->getPost('email_confirm_hobbit'));
			$this->password_hobbit = $filter->filter($this->_request->getPost('password_hobbit'));
			$this->password_confirm_hobbit = $filter->filter($this->_request->getPost('password_confirm_hobbit'));
			$this->sexe_hobbit = $filter->filter($this->_request->getPost('sexe_hobbit'));
			$this->id_region = $filter->filter($this->_request->getPost('id_region'));

			$validPrenom = $validateurPrenom->isValid($this->prenom_hobbit);
			$validEmail = $validateurEmail->isValid($this->email_hobbit);
			$validPassword = $validateurPassword->isValid($this->password_hobbit);
	
			$validEmailConfirm = ($this->email_confirm_hobbit == $this->email_hobbit);
			$validPasswordConfirm = ($this->password_confirm_hobbit == $this->password_hobbit);
			
			if ($this->sexe_hobbit == "feminin" || $this->sexe_hobbit == "masculin") {
				$validSexe = true;
			} else {
				$validSexe = false;
			}
			
			// Verification de la plante
			if (!isset($this->id_region)) {
				$this->id_region = -1;
			}
	
			if (($validPrenom)
			&& ($validEmail)
			&& ($validPassword)
			&& ($validSexe)
			&& ($validEmailConfirm)
			&& ($validPasswordConfirm)) {
					
				$data = $this->initialiseDataHobbit();

				$hobbitTable = new Hobbit();
				$this->view->id_hobbit = $hobbitTable->insert($data);
				$this->view->prenom_hobbit = $this->prenom_hobbit;
				$this->view->email_hobbit = $this->email_hobbit;

				$this->initialiseDataHobbitsCompetences();

				$this->envoiEmail();
				Bral_Util_Log::tech()->notice("InscriptionController - ajouterAction - envoie email vers :".$this->email_hobbit);
				echo $this->view->render("inscription/fin.phtml");
				return;
			} else {
				$tabPrenom = null;
				$tabEmail = null;
				$tabPassword = null;

				foreach ($validateurEmail->getMessages() as $message) {
					$tabEmail[] = $message;
				}
				foreach ($validateurPrenom->getMessages() as $message) {
					$tabPrenom[] = $message;
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
				$this->view->messagesPrenom = $tabPrenom;
				$this->view->messagesEmail = $tabEmail;
				$this->view->messagesPassword = $tabPassword;
			}
		}

		// hobbit par défaut
		$this->view->hobbit= new stdClass();
		$this->view->hobbit->id_hobbit = null;
		$this->view->hobbit->prenom_hobbit = $this->prenom_hobbit;
		$this->view->hobbit->email_hobbit = $this->email_hobbit;
		$this->view->hobbit->email_confirm_hobbit = $this->email_confirm_hobbit;
		$this->view->regions = $regions;
		$this->view->id_region = $this->id_region;
		Bral_Util_Log::inscription()->trace("InscriptionController - ajouterAction - exit");
		$this->render();
	}

	private function initialiseDataHobbit() {
		
		// region aleatoire
		if ($this->id_region == -1) {
			$regionTable = new Region();
			$regionsRowset = $regionTable->fetchAll();
			$de = Bral_Util_De::get_de_specifique(0, count($regionsRowset)-1);
			$regionsRowset = $regionsRowset->toArray();
			$region = $regionsRowset[$de];
			$this->id_region = $region["id_region"];
		}
		
		// Mairie aleatoire dans la region
		$lieuTable = new Lieu();
		$mairiesRowset = $lieuTable->findByTypeAndRegion($this->view->config->game->lieu->type->mairie, $this->id_region);
		$de = Bral_Util_De::get_de_specifique(0, count($mairiesRowset)-1);
		$lieu = $mairiesRowset[$de];

		$pv = $this->view->config->game->pv_base + 0 * $this->view->config->game->pv_max_coef;
		$poids = 0 * 2 + 1;
		$armure_nat = 0;
		$reg = 1;
		
		Zend_Loader::loadClass('Bral_Util_Nom');
    	$nom = new Bral_Util_Nom();
    	
		$dataNom = $nom->calculNom($this->prenom_hobbit);
		$nom_hobbit = $dataNom["nom"];
		$id_fk_nom_initial_hobbit = $dataNom["id_nom"];
		
		$data = array(
			'nom_hobbit' => $nom_hobbit,
			'prenom_hobbit' => $this->prenom_hobbit,
			'id_fk_nom_initial_hobbit' => $id_fk_nom_initial_hobbit,
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
				'id_fk_hobbit_hcomp' => $this->view->id_hobbit,
				'id_fk_competence_hcomp'  => $c["id_competence"],
				'pourcentage_hcomp'  => $c["pourcentage_init_competence"],
				'date_gain_tour_hcomp'  => "0000-00-00 00:00:00",
			);

			$hobbitCompetenceTable = new HobbitsCompetences();
			$hobbitCompetenceTable->insert($data);
		}
	}

	private function envoiEmail() {
		Bral_Util_Log::inscription()->trace("InscriptionController - envoiEmail - enter");
		$this->view->urlValidation = $this->view->config->general->url;
		$this->view->adresseSupport = $this->view->config->general->adresseSupport;
		$this->view->urlValidation .= "/inscription/validation?e=".$this->email_hobbit;
		$this->view->urlValidation .= "&h=".md5($this->prenom_hobbit);
		$this->view->urlValidation .= "&p=".md5($this->password_hobbit);

		$contenuText = $this->view->render("inscription/mailText.phtml");
		$contenuHtml = $this->view->render("inscription/mailHtml.phtml");

		$mail = Bral_Util_Mail::getNewZendMail();
		$mail->setFrom($this->view->config->general->mail->from_email, $this->view->config->general->mail->from_nom);
		$mail->addTo($this->email_hobbit, $this->prenom_hobbit);
		$mail->setSubject($this->view->config->game->inscription->titre_mail);
		$mail->setBodyText($contenuText);
		if ($this->view->config->general->envoi_mail_html == true) {
			$mail->setBodyHtml($contenuHtml);
		}
		$mail->send();
		Bral_Util_Log::inscription()->trace("InscriptionController - envoiEmail - enter");
	}
	
	private function calculParent($idHobbit) {
		Bral_Util_Log::inscription()->trace("InscriptionController - calculParent - enter");
		// on tente de créer de nouveaux couples si besoin
		$de = Bral_Util_De::get_de_specifique(0, 3);
		
		for ($i = 0; $i < $de; $i++) {
			$this->creationCouple($idHobbit);
		}
		
		// on va regarder s'il y a des couples dispo
		$coupleTable = new Couple();
		$couplesRowset = $coupleTable->findAllEnfantPossible();
		
		$dataParents["id_fk_pere_hobbit"] = null;
		$dataParents["id_fk_mere_hobbit"] = null;
			
		if (count($couplesRowset) >= 1) {
			$de = Bral_Util_De::get_de_specifique(0, count($couplesRowset)-1);
			$couple = $couplesRowset[$de];
			
			$dataParents["id_fk_pere_hobbit"] = $couple["id_fk_m_hobbit_couple"];
			$dataParents["id_fk_mere_hobbit"] = $couple["id_fk_f_hobbit_couple"];
			
			$where = array('id_fk_m_hobbit_couple' => $couple["id_fk_m_hobbit_couple"],
						   'id_fk_f_hobbit_couple' => $couple["id_fk_f_hobbit_couple"]);
			$data = array('nb_enfants_couple' => $couple["nb_enfants_couple"] + 1);
			
			$coupleTable->update($data, $where);
			Bral_Util_Log::inscription()->notice("InscriptionController - calculParent - utilisation d'un couple existant");
		} else { // pas de couple dispo, on tente d'en creer un nouveau
			$dataParents = $this->creationCouple($idHobbit);
		}
		Bral_Util_Log::inscription()->trace("InscriptionController - calculParent - exit");
		return $dataParents;
	}
	
	private function creationCouple($idHobbit) {
		Bral_Util_Log::inscription()->trace("InscriptionController - creationCouple - enter");
		$dataParents["id_fk_pere_hobbit"] = null;
		$dataParents["id_fk_mere_hobbit"] = null;
		$hobbitTable = new Hobbit();
		$hobbitsMasculinRowset = $hobbitTable->findHobbitsMasculinSansConjoint($idHobbit);
		if (count($hobbitsMasculinRowset) > 0) {
			$hobbitsFemininRowset = $hobbitTable->findHobbitsFemininSansConjoint($idHobbit);
			if (count($hobbitsFemininRowset) > 0) { // création d'un nouveau couple
				$de = Bral_Util_De::get_de_specifique(0, count($hobbitsMasculinRowset)-1);
				$pere = $hobbitsMasculinRowset[$de];
					
				$de = Bral_Util_De::get_de_specifique(0, count($hobbitsFemininRowset)-1);
				$mere = $hobbitsFemininRowset[$de];
					
				$data = array('id_fk_m_hobbit_couple' => $pere["id_hobbit"],
				 'id_fk_f_hobbit_couple' => $mere["id_hobbit"],
				 'date_creation_couple' => date("Y-m-d H:i:s"),
				 'nb_enfants_couple' => 1);
				$coupleTable->insert($data);
					
				$dataParents["id_fk_pere_hobbit"] = $pere["id_hobbit"];
				$dataParents["id_fk_mere_hobbit"] = $mere["id_hobbit"];
				Bral_Util_Log::tech()->notice("InscriptionController - creationCouple - creation d'un nouveau couple");
			} else {
				Bral_Util_Log::tech()->notice("InscriptionController - creationCouple - plus de hobbit f disponible");
			}
		} else {
			Bral_Util_Log::tech()->notice("InscriptionController - creationCouple - plus de hobbit m disponible");
		}
		Bral_Util_Log::inscription()->trace("InscriptionController - creationCouple - exit");
		return $dataParents;
	}
}

