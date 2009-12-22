<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
abstract class Bral_Scripts_Script {

	const ETAT_EN_COURS = 'EN_COURS';
	const ETAT_OK = 'OK';
	const ETAT_KO = 'KO';

	const TYPE_DYNAMIQUE = 'dynamique';
	const TYPE_STATIQUE = 'statique';
	const TYPE_APPELS = 'appels';

	const PARM_ID_HOBBIT = 'idHobbit';
	const PARM_MDP_RESTREINT = 'mdpRestreint';
	const PARM_VERSION = 'version';

	const VERIFICATION_OK = "OK";

	const SERVICE_ACTIVE = true;
	const SERVICE_DESACTIVE = true;

	const ERREUR_01_EXCEPTION = "ERREUR-01. Erreur Technique, l'équipe est informée";
	const ERREUR_02_PARAMETRES = "ERREUR-02. Paramètres incorrects";
	const ERREUR_03_HOBBIT_INCONNU = "ERREUR-03. Hobbit inconnu";
	const ERREUR_04_MDP_INVALIDE = "ERREUR-04. Mot de passe invalide";
	const ERREUR_05_HOBBIT_DESACTIVE = "ERREUR-05. Hobbit désactivé ou pnj";
	const ERREUR_06_SERVICE_TEMPORAIREMENT_DESACTIVE = "ERREUR-06. Service temporairement désactivé";
	//mis en place dans la factory : const ERREUR_07_SERVICE_INCONNU = "ERREUR-07. Service inconnu";
	const ERREUR_08_VERSION_INCORRECTE = "ERREUR-08. Version incorrecte";

	protected $view = null;
	protected $hobbit = null;
	protected $request = null;

	public function __construct($nomSysteme, $view, $request) {
		Zend_Loader::loadClass('Script');
		$this->nomSysteme = $nomSysteme;
		$this->config = Zend_Registry::get('config');
		$this->view = $view;
		$this->request = $request;
	}

	abstract function calculScriptImpl();
	abstract function getType();
	abstract function getEtatService();
	abstract function getVersion();

	public function calculScript() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - calculScript - enter -");

		$codeService = $this->verificationService();
		if ($codeService != self::VERIFICATION_OK) {
			return $codeService;
		}

		$codeVerification = $this->initParametres();
		if ($codeVerification != self::VERIFICATION_OK) {
			return $codeVerification;
		}

		$scriptTable = new Script();
		$idScript = $this->preCalcul($scriptTable);
		$message = null;
			
		try {
			$message = $this->calculScriptImpl();
		} catch (Zend_Exception $e) {
			$this->postCalcul($scriptTable, $idScript, self::ETAT_KO, $e->getMessage());
			Bral_Util_Log::scripts()->err("Bral_Scripts_Script - calculScript - Erreur -");

			$config = Zend_Registry::get('config');
			if ($config->general->mail->exception->use == '1') {
				Zend_Loader::loadClass("Bral_Util_Mail");
				$mail = Bral_Util_Mail::getNewZendMail();

				$mail->setFrom($config->general->mail->exception->from, $config->general->mail->exception->nom);
				$mail->addTo($config->general->mail->exception->from, $config->general->mail->exception->nom);
				$mail->setSubject("[Braldahim-Script] Exception rencontrée");
				$mail->setBodyText("--------> ".date("Y-m-d H:m:s"). ' IdScript:'.$idScript.PHP_EOL.$e->getMessage(). PHP_EOL);
				$mail->send();
			}

			return self::ERREUR_01_EXCEPTION;
		}
			
		$this->postCalcul($scriptTable, $idScript, self::ETAT_OK, $message);

		return $message;
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - calculScript - exit -");
	}

	private function preCalcul($scriptTable) {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - preCalcul - enter -");
		$data = array(
			'nom_script' => $this->nomSysteme,
			'date_debut_script' => date("Y-m-d H:i:s"),
			'etat_script' => self::ETAT_EN_COURS,
			'type_script' => $this->getType(),
			'id_fk_hobbit_script' => 1,
			'ip_script' => $_SERVER['REMOTE_ADDR'],
			'hostname_script' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
			'url_script' => $_SERVER["REQUEST_URI"],
		);
		$idScript = $scriptTable->insert($data);
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - preCalcul (id:".$idScript.") - exit -");
		return $idScript;
	}

	private function postCalcul($scriptTable, $idScript, $etat, $message = null) {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - postCalcul - enter -");

		$data = array(
			'date_fin_script' => date("Y-m-d H:i:s"),
			'etat_script' => $etat,
			'message_script' => $message,
		);
		$where = 'id_script='.$idScript;
		$scriptTable->update($data, $where);

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - postCalcul - exit -");
	}

	private function verificationService() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - verificationService - enter -");

		$versionRecue = $this->request->get(self::PARM_VERSION);

		if (((int)$versionRecue.""!=$versionRecue."")) {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_02_PARAMETRES a (".$versionRecue.") - exit -");
			return self::ERREUR_02_PARAMETRES;
		} else {
			$version = (int)$versionRecue;
		}

		if ($this->getVersion() != $version) {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_08_VERSION_INCORRECTE (".$this->getVersion(). ", ".$version.") - exit -");
			return self::ERREUR_08_VERSION_INCORRECTE;
		}

		if ($this->getEtatService() != self::SERVICE_ACTIVE) {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_06_SERVICE_TEMPORAIREMENT_DESACTIVE (".$this->getEtatService().") - exit -");
			return self::ERREUR_06_SERVICE_TEMPORAIREMENT_DESACTIVE;
		}

		$retour = self::VERIFICATION_OK;

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - verificationService - exit -");
		return $retour;
	}

	private function initParametres() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - initParametres - enter -");

		$idHobbitRecu = $this->request->get(self::PARM_ID_HOBBIT);
		$mdpRestreintRecu = $this->request->get(self::PARM_MDP_RESTREINT);

		if (((int)$idHobbitRecu.""!=$idHobbitRecu."")) {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_02_PARAMETRES n (".$idHobbitRecu.") - exit -");
			return self::ERREUR_02_PARAMETRES;
		} else {
			$idHobbit = (int)$idHobbitRecu;
		}

		Zend_Loader::loadClass("Hobbit");
		$hobbitTable = new Hobbit();
		$hobbitRow = $hobbitTable->findById($idHobbit);

		if ($hobbitRow == null || count($hobbitRow) < 1) {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_03_HOBBIT_INCONNU (".$idHobbit.") - exit -");
			return self::ERREUR_03_HOBBIT_INCONNU;
		}

		if ($hobbitRow->password_hobbit != $mdpRestreintRecu) {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_04_MDP_INVALIDE (".$idHobbit.", ".$mdpRestreintRecu.") - exit -");
			return self::ERREUR_04_MDP_INVALIDE;
		}
		
		if ($hobbitRow->est_pnj_hobbit == 'oui' || $hobbitRow->est_compte_desactive_hobbit == 'oui' || $hobbitRow->est_compte_actif_hobbit == 'non') {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_05_HOBBIT_DESACTIVE - exit -");
			return self::ERREUR_05_HOBBIT_DESACTIVE;
		}

		$this->hobbit = $hobbitRow;
		
		$retour = self::VERIFICATION_OK;
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - initParametres - exit -");
		return $retour;
	}
}