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

	const NB_TYPE_DYNAMIQUE_MAX = 24;
	const NB_TYPE_STATIQUE_MAX = 10;
	const NB_TYPE_APPELS_MAX = 6;

	const PARM_ID_BRALDUN = 'idBraldun';
	const PARM_MDP_RESTREINT = 'mdpRestreint';
	const PARM_VERSION = 'version';

	const VERIFICATION_OK = "OK";

	const SERVICE_ACTIVE = true;
	const SERVICE_DESACTIVE = true;

	const ERREUR_01_EXCEPTION = "ERREUR-01. Erreur Technique, l'équipe est informée";
	const ERREUR_02_PARAMETRES = "ERREUR-02. Paramètres incorrects";
	const ERREUR_03_BRALDUN_INCONNU = "ERREUR-03. Braldun inconnu";
	const ERREUR_04_MDP_INVALIDE = "ERREUR-04. Mot de passe invalide";
	const ERREUR_05_BRALDUN_DESACTIVE = "ERREUR-05. Braldun désactivé ou pnj";
	const ERREUR_06_SERVICE_TEMPORAIREMENT_DESACTIVE = "ERREUR-06. Service temporairement désactivé";
	//mis en place dans la factory : const ERREUR_07_SERVICE_INCONNU = "ERREUR-07. Service inconnu";
	const ERREUR_08_VERSION_INCORRECTE = "ERREUR-08. Version incorrecte";
	const ERREUR_09_DEPASSEMENT_APPELS = "ERREUR-09. Depassement Appels";

	protected $view = null;
	protected $braldun = null;
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

		$codeVerification = $this->verificationNbAppels($scriptTable);
		if ($codeVerification != self::VERIFICATION_OK) {
			$this->postCalcul($scriptTable, $idScript, self::ETAT_KO, $this->nbAppelsMsg);
			Bral_Util_Log::scripts()->err("Bral_Scripts_Script - calculScript - Erreur NbAppels -");
			return $codeVerification;
		}

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

		$message = $this->nbAppelsMsg.$message;
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
			'id_fk_braldun_script' => $this->braldun->id_braldun,
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

		$idBraldunRecu = $this->request->get(self::PARM_ID_BRALDUN);
		$mdpRestreintRecu = $this->request->get(self::PARM_MDP_RESTREINT);

		if (((int)$idBraldunRecu.""!=$idBraldunRecu."")) {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_02_PARAMETRES n (".$idBraldunRecu.") - exit -");
			return self::ERREUR_02_PARAMETRES;
		} else {
			$idBraldun = (int)$idBraldunRecu;
		}

		Zend_Loader::loadClass("Braldun");
		$braldunTable = new Braldun();
		$braldunRow = $braldunTable->findById($idBraldun);

		if ($braldunRow == null || count($braldunRow) < 1) {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_03_BRALDUN_INCONNU (".$idBraldun.") - exit -");
			return self::ERREUR_03_BRALDUN_INCONNU;
		}

		if ($braldunRow->password_braldun != $mdpRestreintRecu) {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_04_MDP_INVALIDE (".$idBraldun.", ".$mdpRestreintRecu.") - exit -");
			return self::ERREUR_04_MDP_INVALIDE;
		}

		if ($braldunRow->est_pnj_braldun == 'oui' || $braldunRow->est_compte_desactive_braldun == 'oui' || $braldunRow->est_compte_actif_braldun == 'non') {
			Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - ERREUR_05_BRALDUN_DESACTIVE - exit -");
			return self::ERREUR_05_BRALDUN_DESACTIVE;
		}

		$this->braldun = $braldunRow;

		$retour = self::VERIFICATION_OK;
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - initParametres - exit -");
		return $retour;
	}

	private function getNbAppelsMax() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - getNbAppelsMax - enter -");
		$type = $this->getType();
		if ($type == self::TYPE_DYNAMIQUE) {
			$nb = self::NB_TYPE_DYNAMIQUE_MAX;
		} else if ($type == self::TYPE_STATIQUE) {
			$nb = self::NB_TYPE_STATIQUE_MAX;
		} else if ($type == self::TYPE_APPELS) {
			$nb = self::NB_TYPE_APPELS_MAX;
		} else {
			throw new Zend_Exception("Erreur Parametrage Nb Appels");
		}
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - getNbAppelsMax - exit:".$nb." -");
		return $nb;
	}

	private function verificationNbAppels($scriptTable) {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - verificationNbAppels - enter -");

		$date = date("Y-m-d H:i:s");
		$rem_time = "24:00:00";

		Zend_Loader::loadClass("Bral_Util_ConvertDate");
		$dateDebut = Bral_Util_ConvertDate::get_date_remove_time_to_date($date, $rem_time);
		$where = $scriptTable->getAdapter()->quoteInto('date_debut_script <= ?',  $dateDebut);
		$nb = $scriptTable->delete($where. " AND type_script like '".$this->getType()."'");
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - verificationNbAppels - del.".$nb." du type .".$this->getType()." -");

		$nb = $scriptTable->countByIdBraldunAndType($this->braldun->id_braldun, $this->getType());
		$nbMax = $this->getNbAppelsMax();
		$this->nbAppelsMsg = "TYPE:".$this->getType().";NB_APPELS:".$nb.";MAX_AUTORISE:".$nbMax.PHP_EOL;
		
		if ($nb > $nbMax) {
			$retour = self::ERREUR_09_DEPASSEMENT_APPELS.";".$this->nbAppelsMsg;
		} else {
			$retour = self::VERIFICATION_OK;
		}

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - verificationNbAppels - exit -");
		return $retour;
	}
}