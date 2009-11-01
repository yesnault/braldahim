<?

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class AuthController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		Zend_Loader::loadClass('Hobbit');
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');

		if ($this->view->config->general->actif != 1 && $this->_request->action == 'login') {
			$this->_redirect('/');
		}
	}

	function indexAction() {
		$this->_redirect('/');
	}

	function loginAction() {
		// si le joueur est connecte, on le deconnecte !
		if (Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/auth/logout');
		}

		$this->view->message = '';

		$session = new Session();
		$this->view->nbConnecte = $session->count();

		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$f = new Zend_Filter_StripTags();
		$email = $f->filter($this->_request->getPost('post_email_hobbit'));
		$password = $f->filter($this->_request->getPost('post_password_hobbit'));
			
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && $email != "" && $password != "") {
			// setup Zend_Auth adapter for a database table
			Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
			$dbAdapter = Zend_Registry::get('dbAdapter');
			// Suppression de la sessions courante
			Zend_Auth::getInstance()->clearIdentity();
			$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
			$authAdapter->setTableName('hobbit');

			$authAdapter->setIdentityColumn('email_hobbit');
			$authAdapter->setCredentialColumn('password_hobbit');

			// Set the input credential values to authenticate against
			$authAdapter->setIdentity($email);
			$authAdapter->setCredential(md5($password));

			// do the authentication
			$auth = Zend_Auth::getInstance();
			$result = $auth->authenticate($authAdapter);
			if ($result->isValid()) {

				$hobbit = $authAdapter->getResultRowObject(null,'password_hobbit');

				Zend_Loader::loadClass("Bral_Util_Admin");
				Bral_Util_Admin::init($hobbit);

				$this->calculSortieHibernation($hobbit);

				if ($hobbit->est_en_hibernation_hobbit == "oui") {
					Bral_Util_Log::authentification()->warn("AuthController - loginAction - compte non actif : ".$email);
					$this->view->message = "Ce compte est en hibernation jusqu'au ".Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y',$hobbit->date_fin_hibernation_hobbit). " minimum inclus.";
					Zend_Auth::getInstance()->clearIdentity();
				} else if ($hobbit->est_compte_actif_hobbit == "oui" && $hobbit->est_compte_desactive_hobbit == "non") {
					Bral_Util_Log::authentification()->notice("AuthController - loginAction - authentification OK pour ".$email);
					// success : store database row to auth's storage system
					// (not the password though!)
					$auth->getStorage()->write($hobbit);
					// activation du tour
					Zend_Auth::getInstance()->getIdentity()->dateAuth = md5(date("Y-m-d H:i:s"));
					Zend_Auth::getInstance()->getIdentity()->initialCall = true;

					Zend_Auth::getInstance()->getIdentity()->activation = ($f->filter($this->_request->getPost('auth_activation')) == 'oui');
					// Gardiennage
					Zend_Auth::getInstance()->getIdentity()->gardiennage = ($f->filter($this->_request->getPost('auth_gardiennage')) == 'oui');
					Zend_Auth::getInstance()->getIdentity()->gardeEnCours = false;
					// Admin
					Zend_Auth::getInstance()->getIdentity()->administrateur = (Zend_Auth::getInstance()->getIdentity()->sysgroupe_hobbit == 'admin');
					Zend_Auth::getInstance()->getIdentity()->usurpationEnCours = false;
					Zend_Auth::getInstance()->getIdentity()->administrationvue = false;
					Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees = null;
					Zend_Auth::getInstance()->getIdentity()->gestion = (Zend_Auth::getInstance()->getIdentity()->sysgroupe_hobbit == 'gestion');

					$sessionTable = new Session();
					$data = array("id_fk_hobbit_session" => $hobbit->id_hobbit, "id_php_session" => session_id(), "ip_session" => $_SERVER['REMOTE_ADDR'], "date_derniere_action_session" => date("Y-m-d H:i:s"));
					$sessionTable->insertOrUpdate($data);

					if (Zend_Auth::getInstance()->getIdentity()->gardiennage === true) {
						Bral_Util_Log::authentification()->trace("AuthController - loginAction - appel gardiennage");
						$this->_redirect('/Gardiennage/');
					} else if (Zend_Auth::getInstance()->getIdentity()->est_charte_validee_hobbit == "non") {
						$this->_redirect('/charte/');
					} else {
						$this->_redirect('/interface/');
					}
				} else if ($hobbit->est_compte_actif_hobbit == "non") {
					Bral_Util_Log::authentification()->warn("AuthController - loginAction - compte non actif : ".$email);
					$this->view->message = "Ce compte n'est pas actif";
					Zend_Auth::getInstance()->clearIdentity();
				} else { //if ($hobbit->est_compte_desactive_hobbit == "oui") {
					Bral_Util_Log::authentification()->warn("AuthController - loginAction - compte desactive : ".$email);
					$this->view->message = "Ce compte est actuellement désactivé. ";
					$this->view->message .= "Contactez les enquêteurs à l'adresse ".$this->view->config->general->mail->enqueteurs->from." si vous n'êtes pas déjà en contact avec un enquêteur.";
					Zend_Auth::getInstance()->clearIdentity();
				}
			} else {
				Bral_Util_Log::authentification()->notice("AuthController - loginAction - echec d'authentification pour ".$email);
				$this->view->message = "Echec d'authentification";
			}
		} else if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			$this->view->message = "Veuillez renseigner les champs";
		}
		$this->view->title = "Authentification";

		$this->prepareInfosJeu();
		$this->render();
	}

	function logoutAction() {
		$this->deleteSessionInTable();
		Zend_Auth::getInstance()->clearIdentity();
		$this->_redirect('/');
	}

	function logoutajaxAction() {
		$this->deleteSessionInTable();
		Zend_Auth::getInstance()->clearIdentity();
		$this->render();
	}

	private function calculSortieHibernation(&$hobbit) {
		if ($hobbit->est_en_hibernation_hobbit == "oui") {
			$aujourdhui = date("Y-m-d 0:0:0");
			echo $aujourdhui;
			if ($aujourdhui > Bral_Util_ConvertDate::get_datetime_mysql_datetime("Y-m-d 0:0:0", $hobbit->date_fin_hibernation_hobbit)) {

				// si le hobbit est dans un donjon, on le remet à l'hopital le plus proche
				if ($hobbit->est_donjon_hobbit = "oui") {
					Zend_Loader::loadClass("Lieu");
					Zend_Loader::loadClass("TypeLieu");
					$lieuTable = new Lieu();
					$hopitalRowset = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_HOPITAL, $hobbit->x_hobbit, $hobbit->y_hobbit, "non");
					$hobbit->x_hobbit = $hopitalRowset[0]["x_lieu"];
					$hobbit->y_hobbit = $hopitalRowset[0]["y_lieu"];
					$hobbit->z_hobbit = $hopitalRowset[0]["z_lieu"];
					$hobbit->est_donjon_hobbit = "non";
				}

				$hobbit->date_fin_hibernation_hobbit = null;
				$hobbit->est_en_hibernation_hobbit = "non";
				$hobbit->date_fin_tour_hobbit = $aujourdhui;
				$data = array(
					'date_fin_hibernation_hobbit' => $hobbit->date_fin_hibernation_hobbit,
					'est_en_hibernation_hobbit' => $hobbit->est_en_hibernation_hobbit,
					'date_fin_tour_hobbit' => $hobbit->date_fin_tour_hobbit,
					'est_donjon_hobbit' => $hobbit->est_donjon_hobbit,
					'x_hobbit' => $hobbit->x_hobbit,
					'y_hobbit' => $hobbit->y_hobbit,
					'z_hobbit' => $hobbit->z_hobbit,
				);

				$where = "id_hobbit = ".intval($hobbit->id_hobbit);
				$table = new Hobbit();
				$table->update($data, $where);
			}
		}
	}

	private function deleteSessionInTable() {
		$c = "stdClass";
		if (Zend_Auth::getInstance()->hasIdentity() && Zend_Auth::getInstance()->getIdentity() instanceof $c) {
			$idHobbit = Zend_Auth::getInstance()->getIdentity()->id_hobbit;
			if ($idHobbit > 0) {
				$sessionTable = new Session();
				$where = "id_fk_hobbit_session = ".$idHobbit;
				$sessionTable->delete($where);
			}
		}
	}

	private function prepareInfosJeu() {
		Zend_Loader::loadClass("Bral_Util_InfoJeu");
		$infoJeu = Bral_Util_InfoJeu::prepareInfosJeu();
		$this->view->annonces = $infoJeu["annonces"];
		$this->view->histoires = $infoJeu["histoires"];
	}
}
