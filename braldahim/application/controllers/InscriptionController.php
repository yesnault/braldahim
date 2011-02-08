<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class InscriptionController extends Zend_Controller_Action {

	function init() {
		Zend_Auth::getInstance()->clearIdentity();
		$this->initView();
		Zend_Loader::loadClass("Bral_Validate_Inscription_EmailBraldun");
		Zend_Loader::loadClass("Bral_Validate_Inscription_PrenomBraldun");
		Zend_Loader::loadClass("Bral_Validate_StringLength");
		Zend_Loader::loadClass("Zend_Validate_EmailAddress");
		Zend_Loader::loadClass("Zend_Validate");
		Zend_Loader::loadClass("Bral_Util_Mail");
		Zend_Loader::loadClass("Bral_Util_Evenement");
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("BraldunsCompetences");
		Zend_Loader::loadClass("Couple");
		Zend_Loader::loadClass("Nom");
		Zend_Loader::loadClass("Region");
		$this->view->config = Zend_Registry::get('config');
		if ($this->view->config->general->actif != 1) {
			$this->_redirect('/');
		}
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

		$email_braldun = $this->_request->get("e");
		$md5_prenom_braldun = $this->_request->get("h");
		$hash_password_braldun = $this->_request->get("p");

		$braldunTable = new Braldun();
		$braldun = null;
		if ($email_braldun != null && $email_braldun != "") {
			$braldun = $braldunTable->findByEmail($email_braldun);
			Bral_Util_Log::inscription()->trace("InscriptionController - validationAction - A : " . $braldun->est_compte_actif_braldun);
		} else {
			Bral_Util_Log::inscription()->trace("InscriptionController - validationAction - B");
		}

		if ($braldun != null && $md5_prenom_braldun != null && $md5_prenom_braldun != "" && $hash_password_braldun != null && $hash_password_braldun != "") {
			if ($braldun->est_compte_actif_braldun == 'non' && count($braldun) > 0) {
				if ($md5_prenom_braldun == md5($braldun->prenom_braldun) && ($hash_password_braldun == $braldun->password_hash_braldun)) {
					$this->view->validationOk = true;

					$dataParents = $this->calculParent($braldun->id_braldun);

					$data = array(
						'est_compte_actif_braldun' => "oui",
						'id_fk_pere_braldun' => $dataParents["id_fk_pere_braldun"],
						'id_fk_mere_braldun' => $dataParents["id_fk_mere_braldun"],
					);

					$where = "id_braldun=".$braldun->id_braldun;
					$braldunTable->update($data, $where);

					$braldun->id_fk_pere_braldun =  $dataParents["id_fk_pere_braldun"];
					$braldun->id_fk_mere_braldun =  $dataParents["id_fk_mere_braldun"];

					$e = "";
					if ($braldun->sexe_braldun == "feminin") {
						$e = "e";
					}

					$details = $braldun->prenom_braldun ." ".$braldun->nom_braldun." (".$braldun->id_braldun.") est apparu".$e." sur Braldahim";
					Zend_Loader::loadClass('Evenement');
					$evenementTable = new Evenement();
					$data = array(
						'id_fk_braldun_evenement' => $braldun->id_braldun,
						'date_evenement' => date("Y-m-d H:i:s"),
						'id_fk_type_evenement' => $this->view->config->game->evenements->type->naissance,
						'details_evenement' => $details,
					);
					$evenementTable->insert($data);

					Zend_Loader::loadClass("Bral_Util_Quete");
					Bral_Util_Quete::creationQueteInitiatique($braldun, $this->view->config);

					Zend_Loader::loadClass("Bral_Util_Messagerie");
					$message = $this->view->render("inscription/messagepnj.phtml");
					Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->inscription->id_braldun, $braldun->id_braldun, $message, $this->view);

					$this->ajouterDistinctionTesteur($braldun->id_braldun, $email_braldun);
					Bral_Util_Log::inscription()->notice("InscriptionController - validationAction - validation OK pour :".$email_braldun);
				} else {
					Bral_Util_Log::inscription()->trace("InscriptionController - validationAction - C MD5 invalides : ".$md5_prenom_braldun.":".md5($braldun->prenom_braldun)." PASS:".$hash_password_braldun.":".$braldun->password_braldun);
				}
			} else {
				Bral_Util_Log::tech()->notice("InscriptionController - validationAction - compte deja active");
				$this->view->compteActif = true;
			}
		} else {
			Bral_Util_Log::tech()->notice("InscriptionController - validationAction - compte deja active");
			$this->view->compteActif = false;
		}
		Bral_Util_Log::inscription()->trace("InscriptionController - validationAction - exit");
		$this->render();
	}

	/**
	 * Ajoute la distinction Beta Testeur aux anciens
	 * @param unknown_type $idBraldun identifiant du Braldun
	 * @param unknown_type $emailBraldun email du braldun, doit être le même qu'en version Beta (table Testeur)
	 * @return void
	 */
	private function ajouterDistinctionTesteur($idBraldun, $emailBraldun) {
		Zend_Loader::loadClass("Testeur");
		$testeurTable = new Testeur();
		$r = $testeurTable->findByEmail($emailBraldun);

		if (count($r) != 0) {
			Zend_Loader::loadClass("Bral_Util_Distinction");
			$texte = "Testeur sur la version Beta de Braldahim";
			Bral_Util_Distinction::ajouterDistinction($idBraldun, Bral_Util_Distinction::ID_TYPE_BETA_TESTEUR, $texte);
		}
	}

	function ajouterAction() {
		Bral_Util_Log::inscription()->trace("InscriptionController - ajouterAction - enter");
		$this->view->title = "Nouvel Braldun";
		$this->prenom_braldun = "";
		$this->email_braldun = "";
		$this->email_confirm_braldun = "";
		$this->sexe_braldun = "";
		$this->id_region = -1;

		$regionTable =  new Region();
		$regionsRowset = $regionTable->fetchAll();
		$regionsRowset = $regionsRowset->toArray();

		$regions = null;
		foreach ($regionsRowset as $r) {
			if ($r["id_region"] == 1) {// || $r["id_region"] == 3) {
				$regions[$r["id_region"]]["nom"] = $r["nom_region"];
				$regions[$r["id_region"]]["est_pvp"] = $r["est_pvp_region"];
			}
		}

		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$validateurEmail = new Bral_Validate_Inscription_EmailBraldun();
			$validateurPrenom = new Bral_Validate_Inscription_PrenomBraldun();
			$validateurPassword = new Bral_Validate_StringLength(5, 20);

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())
			->addFilter(new Zend_Filter_StripTags());

			$this->prenom_braldun = stripslashes($filter->filter($this->_request->getPost('prenom_braldun')));
			$this->prenom_braldun = Bral_Util_String::firstToUpper($this->prenom_braldun);

			$this->email_braldun = $filter->filter($this->_request->getPost('email_braldun'));
			$this->email_confirm_braldun = $filter->filter($this->_request->getPost('email_confirm_braldun'));
			$this->password_braldun = $filter->filter($this->_request->getPost('password_braldun'));
			$this->password_confirm_braldun = $filter->filter($this->_request->getPost('password_confirm_braldun'));
			$this->sexe_braldun = $filter->filter($this->_request->getPost('sexe_braldun'));
			$this->id_region = $filter->filter($this->_request->getPost('id_region'));

			$captcha_vue =  $this->_request->getPost('captcha');
			$validPrenom = $validateurPrenom->isValid($this->prenom_braldun);
			$validEmail = $validateurEmail->isValid($this->email_braldun, ($this->view->config->general->production == 1));
			$validPassword = $validateurPassword->isValid($this->password_braldun);

			$validEmailConfirm = ($this->email_confirm_braldun == $this->email_braldun);
			$validPasswordConfirm = ($this->password_confirm_braldun == $this->password_braldun);
			$validCaptcha = $this->validateCaptcha($captcha_vue);

			if ($this->sexe_braldun == "feminin" || $this->sexe_braldun == "masculin") {
				$validSexe = true;
			} else {
				$validSexe = false;
			}

			// Verification de la region
			if (!isset($this->id_region)) {
				$this->id_region = -1;
			}

			if (($validPrenom)
			&& ($validEmail)
			&& ($validPassword)
			&& ($validSexe)
			&& ($validEmailConfirm)
			&& ($validPasswordConfirm)
			&& ($validCaptcha)) {
					
				$data = $this->initialiseDataBraldun();

				$braldunTable = new Braldun();
				$this->view->id_braldun = $braldunTable->insert($data);
				$this->view->prenom_braldun = $this->prenom_braldun;
				$this->view->email_braldun = $this->email_braldun;

				$this->envoiEmail($data["password_hash_braldun"]);
				Bral_Util_Log::tech()->notice("InscriptionController - ajouterAction - envoi email vers ".$this->email_braldun);
				echo $this->view->render("inscription/fin.phtml");

				// Creation du coffre
				Zend_Loader::loadClass("Coffre");
				$coffreTable = new Coffre();
				$data = array(
					"id_fk_braldun_coffre" => $this->view->id_braldun,
				);
				$coffreTable->insert($data);
				
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
					$this->view->messagesSexe = "Choisissez un genre !";
				}
				if (!$validEmailConfirm) {
					$this->view->messagesEmailConfirm = "Les deux emails sont diff&eacute;rents";
				}
				if (!$validPasswordConfirm) {
					$this->view->messagesPasswordConfirm = "Les deux mots de passe sont diff&eacute;rents";
				}
				if (!$validCaptcha) {
					$this->view->messageCaptchaInvalide = "Saisie invalide";
				}
				$this->view->messagesPrenom = $tabPrenom;
				$this->view->messagesEmail = $tabEmail;
				$this->view->messagesPassword = $tabPassword;
			}
		}

		Zend_Loader::loadClass("Zend_Captcha_Image");
		$captcha = new Zend_Captcha_Image();

		$captcha->setTimeout('300')
		->setWordLen('6')
		->setHeight('80')
		->setFont('../public/fonts/Arial.ttf')
		->setImgDir($this->view->config->captcha->patch);

		$id = $captcha->generate();
		$this->view->captcha = $captcha;

		// Braldûn par defaut
		$this->view->braldun= new stdClass();
		$this->view->braldun->id_braldun = null;
		$this->view->braldun->prenom_braldun = $this->prenom_braldun;
		$this->view->braldun->email_braldun = $this->email_braldun;
		$this->view->braldun->email_confirm_braldun = $this->email_confirm_braldun;
		$this->view->braldun->sexe_braldun = $this->sexe_braldun;
		$this->view->regions = $regions;
		$this->view->id_region = $this->id_region;
		Bral_Util_Log::inscription()->trace("InscriptionController - ajouterAction - exit");
		$this->render();
	}

	private function initialiseDataBraldun() {
		// region aleatoire

		if ($this->id_region == -1) {
			//$de = Bral_Util_De::get_1D2();

			$this->id_region = 1;
			/*
			 if ($de == 1) {
				$this->id_region = 1;
				} else {
				$this->id_region = 3;
				}*/

			/*	$regionTable = new Region();
			 $regionsRowset = $regionTable->fetchAll();
			 $de = Bral_Util_De::get_de_specifique(0, count($regionsRowset)-1);
			 $regionsRowset = $regionsRowset->toArray();
			 $region = $regionsRowset[$de];
			 $this->id_region = $region["id_region"];
			 */
		}

		// Mairie aleatoire dans la region
		Zend_Loader::loadClass("TypeLieu");
		$lieuTable = new Lieu();
		$lieuxRowset = $lieuTable->findByTypeAndRegion(TypeLieu::ID_TYPE_HOPITAL, $this->id_region, "non", "oui");
		$de = Bral_Util_De::get_de_specifique(0, count($lieuxRowset)-1);
		$lieu = $lieuxRowset[$de];

		$pv = $this->view->config->game->pv_base + 0 * $this->view->config->game->pv_max_coef;
		$poids = Bral_Util_Poids::calculPoidsTransportable(0);
		$armure_nat = 0;
		$reg = 1;

		Zend_Loader::loadClass('Bral_Util_Nom');
		$nom = new Bral_Util_Nom();
			
		$dataNom = $nom->calculNom($this->prenom_braldun, $this->email_braldun);
		$nom_braldun = $dataNom["nom"];
		$id_fk_nom_initial_braldun = $dataNom["id_nom"];

		$mdate = date("Y-m-d H:i:s");
		$pv = Bral_Util_Commun::calculPvMaxBaseSansEffetMotE($this->view->config, $this->view->config->game->inscription->vigueur_base);

		Zend_Loader::loadClass('Bral_Util_Hash');
		$salt = Bral_Util_Hash::getSalt();
		$passwordHash = Bral_Util_Hash::getHashString($salt, md5($this->password_braldun));
			
		$data = array(
			'nom_braldun' => $nom_braldun,
			'prenom_braldun' => $this->prenom_braldun,
			'id_fk_nom_initial_braldun' => $id_fk_nom_initial_braldun,
			'email_braldun'  => $this->email_braldun,
			'password_salt_braldun'  => $salt,
			'password_hash_braldun'  => $passwordHash,
			'est_compte_actif_braldun'  => "non",
			'castars_braldun' => $this->view->config->game->inscription->castars,
			'sexe_braldun' => $this->sexe_braldun,
			'x_braldun' => $lieu["x_lieu"],
			'y_braldun' => $lieu["y_lieu"],
			'z_braldun' => 0,
			'vue_bm_braldun' => $this->view->config->game->inscription->vue_bm,
			'date_fin_tour_braldun' => Bral_Util_ConvertDate::get_date_add_time_to_date($mdate, $this->view->config->game->tour->inscription->duree_base_cumul),
			'date_debut_tour_braldun' => Bral_Util_ConvertDate::get_date_remove_time_to_date($mdate, $this->view->config->game->tour->inscription->duree_base_cumul),
			'date_fin_latence_braldun' => Bral_Util_ConvertDate::get_date_remove_time_to_date($mdate, $this->view->config->game->tour->inscription->duree_base_milieu),
			'date_debut_cumul_braldun' => $mdate,
			'duree_prochain_tour_braldun' => $this->view->config->game->tour->duree_base,
			'duree_courant_tour_braldun' => $this->view->config->game->tour->duree_base,
			'date_creation_braldun' => $mdate,
			'tour_position_braldun' => $this->view->config->game->tour->position_latence, // sera recalcule lors de la connexion avec cumul
			'balance_faim_braldun' => $this->view->config->game->inscription->balance_faim,
			'pv_restant_braldun' => $pv,
			'force_base_braldun' => $this->view->config->game->inscription->force_base,
			'agilite_base_braldun' => $this->view->config->game->inscription->agilite_base,
			'vigueur_base_braldun' => $this->view->config->game->inscription->vigueur_base,
			'sagesse_base_braldun' => $this->view->config->game->inscription->sagesse_base,
		//'pa_braldun' => $this->view->config->game->inscription->pa, // seront recalcules lors de la connexion en cumul
			'poids_transportable_braldun' => $poids,
			'armure_naturelle_braldun' => $armure_nat,
			'regeneration_braldun' => $reg,
			'poids_transporte_braldun' => Bral_Util_Poids::calculPoidsTransporte(-1, $this->view->config->game->inscription->castars),
			'pv_max_braldun' => $pv,
			'pv_restant_braldun' => $pv,
			'est_charte_validee_braldun' => "oui",
			'id_fk_region_creation_braldun' => $this->id_region,
			'est_quete_braldun' => "oui",
		);

		return $data;
	}

	private function envoiEmail($passwordHashBraldun) {
		Bral_Util_Log::inscription()->trace("InscriptionController - envoiEmail - enter");

		Zend_Loader::loadClass("Bral_Util_Inscription");
		$this->view->urlValidation = Bral_Util_Inscription::getLienValidation($this->view->id_braldun, $this->email_braldun, md5($this->prenom_braldun), $passwordHashBraldun);
		$this->view->adresseSupport = $this->view->config->general->adresseSupport;

		$contenuText = $this->view->render("inscription/mailText.phtml");
		$contenuHtml = $this->view->render("inscription/mailHtml.phtml");

		$mail = Bral_Util_Mail::getNewZendMail();
		$mail->setFrom($this->view->config->general->mail->from_email, $this->view->config->general->mail->from_nom);
		$mail->addTo($this->email_braldun, $this->prenom_braldun);
		$mail->addBcc($this->view->config->general->adresseInscriptions);
		$mail->setSubject("Braldahim - Validation de l'inscription");
		$mail->setBodyText($contenuText);
		if ($this->view->config->general->envoi_mail_html == true) {
			$mail->setBodyHtml($contenuHtml);
		}
		$mail->send();
		Bral_Util_Log::inscription()->trace("InscriptionController - envoiEmail - enter");
		Bral_Util_Log::mail()->trace("InscriptionController - envoiEmail - ".$this->email_braldun. " ". $this->prenom_braldun);
	}

	private function calculParent($idBraldun) {
		Bral_Util_Log::inscription()->trace("InscriptionController - calculParent - enter");
		// on tente de creer de nouveaux couples si besoin
		$de = Bral_Util_De::get_de_specifique(0, 3);

		for ($i = 0; $i < $de; $i++) {
			$this->creationCouple($idBraldun);
		}

		// on va regarder s'il y a des couples dispo
		$coupleTable = new Couple();
		$couplesRowset = $coupleTable->findAllEnfantPossible();

		$dataParents["id_fk_pere_braldun"] = null;
		$dataParents["id_fk_mere_braldun"] = null;

		if (count($couplesRowset) >= 1) {
			$de = Bral_Util_De::get_de_specifique(0, count($couplesRowset)-1);
			$couple = $couplesRowset[$de];

			$dataParents["id_fk_pere_braldun"] = $couple["id_fk_m_braldun_couple"];
			$dataParents["id_fk_mere_braldun"] = $couple["id_fk_f_braldun_couple"];

			$where = "id_fk_m_braldun_couple=".$couple["id_fk_m_braldun_couple"]." AND id_fk_f_braldun_couple=".$couple["id_fk_f_braldun_couple"];
			$nombreEnfants = $couple["nb_enfants_couple"] + 1;
			$data = array('nb_enfants_couple' => $nombreEnfants);

			$coupleTable->update($data, $where);

			$detailEvenement = "Un heureux événement est arrivé... ";
			$detailsBot = " Vous venez d'avoir un nouvel enfant à  ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y',date("Y-m-d H:i:s")).".";
			$detailsBot .= " Consultez votre onglet Famille pour plus de détails.";

			Bral_Util_Evenement::majEvenements($couple["id_fk_m_braldun_couple"], $this->view->config->game->evenements->type->famille, $detailEvenement, $detailsBot, 0, "braldun", true, $this->view);
			Bral_Util_Evenement::majEvenements($couple["id_fk_f_braldun_couple"], $this->view->config->game->evenements->type->famille, $detailEvenement, $detailsBot, 0, "braldun", true, $this->view);

			Zend_Loader::loadClass("Bral_Util_Messagerie");
			$message = $detailsBot.PHP_EOL.PHP_EOL." Signé Irène Doucelac".PHP_EOL."Inutile de répondre à ce message.";
			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->naissance->id_braldun, $couple["id_fk_m_braldun_couple"], $message, $this->view);
			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->naissance->id_braldun, $couple["id_fk_f_braldun_couple"], $message, $this->view);

			Bral_Util_Log::inscription()->notice("InscriptionController - calculParent - utilisation d'un couple existant (m:".$couple["id_fk_m_braldun_couple"]." f:".$couple["id_fk_f_braldun_couple"]." enfants:".$nombreEnfants.")");
		} else { // pas de couple dispo, on tente d'en creer un nouveau
			$dataParents = $this->creationCouple($idBraldun);
		}
		Bral_Util_Log::inscription()->trace("InscriptionController - calculParent - exit");
		return $dataParents;
	}

	private function creationCouple($idBraldun) {
		Bral_Util_Log::inscription()->trace("InscriptionController - creationCouple - enter");
		$dataParents["id_fk_pere_braldun"] = null;
		$dataParents["id_fk_mere_braldun"] = null;
		$braldunTable = new Braldun();
		$braldunsMasculinRowset = $braldunTable->findBraldunsMasculinSansConjoint($idBraldun);
		if (count($braldunsMasculinRowset) > 0) {
			$braldunsFemininRowset = $braldunTable->findBraldunsFemininSansConjoint($idBraldun);
			if (count($braldunsFemininRowset) > 0) { // creation d'un nouveau couple
				$de = Bral_Util_De::get_de_specifique(0, count($braldunsMasculinRowset)-1);
				$pere = $braldunsMasculinRowset[$de];

				$de = Bral_Util_De::get_de_specifique(0, count($braldunsFemininRowset)-1);
				$mere = $braldunsFemininRowset[$de];

				$data = array('id_fk_m_braldun_couple' => $pere["id_braldun"],
							  'id_fk_f_braldun_couple' => $mere["id_braldun"],
							  'date_creation_couple' => date("Y-m-d H:i:s"),
							  'nb_enfants_couple' => 0,
				);
				$coupleTable = new Couple();
				$coupleTable->insert($data);

				$dataParents["id_fk_pere_braldun"] = $pere["id_braldun"];
				$dataParents["id_fk_mere_braldun"] = $mere["id_braldun"];

				$detailEvenement =  "[b".$mere["id_braldun"]."] s'est mariée avec [b".$pere["id_braldun"]."]" ;
				$detailsBot = "Mariage effectué à  ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('H:i:s \l\e d/m/y',date("Y-m-d H:i:s")).".";
				$detailsBot .= " Consultez votre onglet Famille pour plus de détails.";

				Bral_Util_Evenement::majEvenements($pere["id_braldun"], $this->view->config->game->evenements->type->famille, $detailEvenement, $detailsBot, $pere["niveau_braldun"], "braldun", true, $this->view);
				Bral_Util_Evenement::majEvenements($mere["id_braldun"], $this->view->config->game->evenements->type->famille, $detailEvenement, $detailsBot, $mere["niveau_braldun"], "braldun", true, $this->view);

				Zend_Loader::loadClass("Bral_Util_Messagerie");
				$message = $detailsBot.PHP_EOL.PHP_EOL." Signé Irène Doucelac".PHP_EOL."Inutile de répondre à ce message.";
				Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->naissance->id_braldun, $mere["id_braldun"], $message, $this->view);
				Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->naissance->id_braldun, $pere["id_braldun"], $message, $this->view);

				Bral_Util_Log::tech()->notice("InscriptionController - creationCouple - creation d'un nouveau couple");
			} else {
				Bral_Util_Log::tech()->notice("InscriptionController - creationCouple - plus de Braldûn f disponible");
			}
		} else {
			Bral_Util_Log::tech()->notice("InscriptionController - creationCouple - plus de Braldûn m disponible");
		}
		Bral_Util_Log::inscription()->trace("InscriptionController - creationCouple - exit");
		return $dataParents;
	}

	function validateCaptcha($captcha) {
		$captchaId = $captcha['id'];
		$captchaInput = $captcha['input'];
		$captchaSession = new Zend_Session_Namespace('Zend_Form_Captcha_' . $captchaId);
		$captchaIterator = $captchaSession->getIterator();
		if($captchaIterator != null && array_key_exists("word", $captchaIterator) && $captchaInput != $captchaIterator['word']){
			return false;
		} else {
			return true;
		}
	}
}
