<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class AuthController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		Zend_Loader::loadClass('Braldun');
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		$this->view->estMobile = Zend_Registry::get("estMobile");
		$this->view->estIphone = Zend_Registry::get("estIphone");

		if ($this->view->config->general->actif != 1 && $this->_request->action == 'login') {
			$this->_redirect('/');
		}
	}

	function indexAction()
	{
		$this->_redirect('/');
	}

	function loginmobileAction()
	{
		$this->loginWork();
		$this->render();
	}

	function loginAction()
	{
		if ($this->view->estMobile) {
			$this->_forward("loginmobile");
		} else {
			Zend_Loader::loadClass('Bral_Helper_Bougrie');

			Zend_Loader::loadClass("Bral_Util_Lot");
			$this->view->lots = Bral_Util_Lot::getLotsByHotelAccueil();

			Zend_Loader::loadClass("Bral_Util_Communaute");
			$this->view->communautes = Bral_Util_Communaute::getTop5($this->view);

			$this->loginWork();
			$this->render();
		}
	}

	function loginWork()
	{
		// si le joueur est connecte, on le deconnecte !
		if (Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/auth/logout');
		}

		$this->view->message = '';

		$session = new Session();
		$this->view->nbConnecte = $session->count();

		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$f = new Zend_Filter_StripTags();
		$email = $f->filter($this->_request->getPost('post_email_braldun'));
		$password = $f->filter($this->_request->getPost('post_password_braldun'));

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && $email != "" && $password != "") {

			Zend_Loader::loadClass('Bral_Util_Hash');

			// setup Zend_Auth adapter for a database table
			Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
			$dbAdapter = Zend_Registry::get('dbAdapter');
			// Suppression de la sessions courante
			Zend_Auth::getInstance()->clearIdentity();
			$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
			$authAdapter->setTableName('braldun');

			$authAdapter->setIdentityColumn('email_braldun');
			$authAdapter->setCredentialColumn('password_hash_braldun');

			$braldunTable = new Braldun();
			$salt = $braldunTable->getSaltByEmail($email);
			$salt = $salt["password_salt_braldun"];

			// Set the input credential values to authenticate against
			$authAdapter->setIdentity($email);
			$passwordHash = Bral_Util_Hash::getHashString($salt, md5($password));
			$authAdapter->setCredential($passwordHash);

			// do the authentication
			$auth = Zend_Auth::getInstance();
			$result = $auth->authenticate($authAdapter);
			if ($result->isValid()) {

				$braldun = $authAdapter->getResultRowObject(null, array('password_hash_braldun', 'password_salt_braldun'));

				Zend_Loader::loadClass("Bral_Util_Admin");
				Bral_Util_Admin::init($braldun);

				$this->calculSortieHibernation($braldun);

				if ($braldun->est_en_hibernation_braldun == "oui") {
					Bral_Util_Log::authentification()->warn("AuthController - loginAction - compte non actif : " . $email);
					$this->view->message = "Ce compte est en hibernation jusqu'au " . Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y', $braldun->date_fin_hibernation_braldun) . " minimum inclus.";
					Zend_Auth::getInstance()->clearIdentity();
				} else if ($braldun->est_compte_actif_braldun == "oui" && $braldun->est_compte_desactive_braldun == "non") {
					Bral_Util_Log::authentification()->notice("AuthController - loginAction - authentification OK pour " . $email);
					// success : store database row to auth's storage system
					// (not the password though!)
					$auth->getStorage()->write($braldun);
					// activation du tour
					Zend_Auth::getInstance()->getIdentity()->dateAuth = md5(date("Y-m-d H:i:s"));
					Zend_Auth::getInstance()->getIdentity()->dateConnexion = date("Y-m-d H:i:s");
					Zend_Auth::getInstance()->getIdentity()->initialCall = true;

					Zend_Auth::getInstance()->getIdentity()->activation = ($f->filter($this->_request->getPost('auth_activation')) == 'oui');
					// Gardiennage
					Zend_Auth::getInstance()->getIdentity()->gardiennage = ($f->filter($this->_request->getPost('auth_gardiennage')) == 'oui');
					Zend_Auth::getInstance()->getIdentity()->gardeEnCours = false;
					// Admin
					Zend_Auth::getInstance()->getIdentity()->administrateur = (Zend_Auth::getInstance()->getIdentity()->sysgroupe_braldun == 'admin');
					Zend_Auth::getInstance()->getIdentity()->usurpationEnCours = false;
					Zend_Auth::getInstance()->getIdentity()->administrationvue = false;
					Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees = null;
					Zend_Auth::getInstance()->getIdentity()->gestion = (Zend_Auth::getInstance()->getIdentity()->sysgroupe_braldun == 'gestion');
					Zend_Auth::getInstance()->getIdentity()->rangCommunaute = null;

					if ($braldun->id_fk_communaute_braldun != null) {
						Zend_Loader::loadClass("RangCommunaute");
						$rangCommunauteTable = new RangCommunaute();
						$rang = $rangCommunauteTable->findByIdRang($braldun->id_fk_rang_communaute_braldun);
						if ($rang != null) {
							Zend_Auth::getInstance()->getIdentity()->rangCommunaute = $rang[0]["ordre_rang_communaute"];
						}
					}

					$sessionTable = new Session();
					$data = array("id_fk_braldun_session" => $braldun->id_braldun, "id_php_session" => session_id(), "ip_session" => $_SERVER['REMOTE_ADDR'], "date_derniere_action_session" => date("Y-m-d H:i:s"));
					$sessionTable->insertOrUpdate($data);

					if (Zend_Auth::getInstance()->getIdentity()->gardiennage === true) {
						Zend_Auth::getInstance()->getIdentity()->rangCommunaute = null;
						Bral_Util_Log::authentification()->trace("AuthController - loginAction - appel gardiennage");
						$this->_redirect('/Gardiennage/');
					} else if (Zend_Auth::getInstance()->getIdentity()->est_charte_validee_braldun == 'non') {
						$this->_redirect('/charte/');
					} else if (Zend_Auth::getInstance()->getIdentity()->est_sondage_valide_braldun == 'non' && Zend_Auth::getInstance()->getIdentity()->est_pnj_braldun == 'non') {
						$this->_redirect('/sondage/');
					} else {
						$this->_redirect('/interface/');
					}
				} else if ($braldun->est_compte_actif_braldun == 'non') {
					Bral_Util_Log::authentification()->warn("AuthController - loginAction - compte non actif : " . $email);
					$this->view->message = "Ce compte n'est pas actif";
					Zend_Auth::getInstance()->clearIdentity();
				} else { //if ($braldun->est_compte_desactive_braldun == "oui") {
					Bral_Util_Log::authentification()->warn("AuthController - loginAction - compte desactive : " . $email);
					$this->view->message = "Ce compte est actuellement désactivé. ";
					$this->view->message .= "Contactez les enquêteurs à l'adresse " . $this->view->config->general->mail->enqueteurs->from . " si vous n'êtes pas déjà en contact avec un enquêteur.";
					Zend_Auth::getInstance()->clearIdentity();
				}
			} else {
				Bral_Util_Log::authentification()->notice("AuthController - loginAction - echec d'authentification pour " . $email);
				$this->view->message = "Echec d'authentification";
			}
		} else if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			$this->view->message = "Veuillez renseigner les champs";
		}
		$this->view->title = "Authentification";

		$this->prepareInfosJeu();
	}

	function logoutAction()
	{
		$this->deleteSessionInTable();
		Zend_Auth::getInstance()->clearIdentity();
		$this->_redirect('/');
	}

	function logoutajaxAction()
	{
		$this->deleteSessionInTable();
		Zend_Auth::getInstance()->clearIdentity();
		$this->render();
	}

	private function calculSortieHibernation(&$braldun)
	{
		if ($braldun->est_en_hibernation_braldun == "oui") {
			$aujourdhui = date("Y-m-d 0:0:0");
			echo $aujourdhui;
			if ($aujourdhui > Bral_Util_ConvertDate::get_datetime_mysql_datetime("Y-m-d 0:0:0", $braldun->date_fin_hibernation_braldun)) {

				// si le Braldûn est dans un donjon, on le remet à l'hopital le plus proche
				if ($braldun->est_donjon_braldun == "oui") {
					Zend_Loader::loadClass("Lieu");
					Zend_Loader::loadClass("TypeLieu");
					$lieuTable = new Lieu();
					$hopitalRowset = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_HOPITAL, $braldun->x_braldun, $braldun->y_braldun, "non");
					$braldun->x_braldun = $hopitalRowset[0]["x_lieu"];
					$braldun->y_braldun = $hopitalRowset[0]["y_lieu"];
					$braldun->z_braldun = $hopitalRowset[0]["z_lieu"];
					$braldun->est_donjon_braldun = "non";
				}

				$braldun->date_fin_hibernation_braldun = null;
				$braldun->est_en_hibernation_braldun = "non";
				$braldun->date_fin_tour_braldun = $aujourdhui;
				$data = array(
					'date_fin_hibernation_braldun' => $braldun->date_fin_hibernation_braldun,
					'est_en_hibernation_braldun' => $braldun->est_en_hibernation_braldun,
					'date_fin_tour_braldun' => $braldun->date_fin_tour_braldun,
					'est_donjon_braldun' => $braldun->est_donjon_braldun,
				);

				$where = "id_braldun = " . intval($braldun->id_braldun);
				$table = new Braldun();
				$table->update($data, $where);
			}
		}
	}

	private function deleteSessionInTable()
	{
		$c = "stdClass";
		if (Zend_Auth::getInstance()->hasIdentity() && Zend_Auth::getInstance()->getIdentity() instanceof $c) {
			$idBraldun = Zend_Auth::getInstance()->getIdentity()->id_braldun;
			if ($idBraldun > 0) {
				$sessionTable = new Session();
				$where = "id_fk_braldun_session = " . $idBraldun;
				$sessionTable->delete($where);
			}
		}
	}

	private function prepareInfosJeu()
	{
		/*	Zend_Loader::loadClass("Bral_Util_InfoJeu");
		 $infoJeu = Bral_Util_InfoJeu::prepareInfosJeu();
		 $this->view->nouvelles = $infoJeu["toutes"];
	 */
	}
}
