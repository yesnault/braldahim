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
	
	const ERREUR_01_EXCEPTION = "ERREUR-01. Erreur Technique, l'équipe est informée";
	const ERREUR_02_PARAMETRES = "ERREUR-02. Paramètres incorrects";
	const ERREUR_03_HOBBIT_INCONNU = "ERREUR-03. Hobbit inconnu";
	const ERREUR_04_MDP_INVALIDE = "ERREUR-04. Mot de passe invalide";
	const ERREUR_05_HOBBIT_DESACTIVE = "ERREUR-05. Hobbit désactivé ou pnj";
	const ERREUR_06_SERVICE_TEMPORAIREMENT_DESACTIVE = "ERREUR-06. Service temporairement désactivé";
	const ERREUR_07_SERVICE_INCONNU = "ERREUR-07. Service inconnu";
	const ERREUR_08_VERSION_INCORRECTE = "ERREUR-08. Version incorrecte";
	
	protected $view = null;
	
	public function __construct($nomSysteme, $view) {
		Zend_Loader::loadClass('Script'); 
		$this->nomSysteme = $nomSysteme;
		$this->config = Zend_Registry::get('config');
		$this->view = $view;
	}
	
	abstract function calculScriptImpl();
	abstract function getType();
	
	public function calculScript($param = null) {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - calculScript - enter -");
		
		$scriptTable = new Script();
	 	$idScript = $this->preCalcul($scriptTable);
	 	$message = null;
	 	try {
			$message = $this->calculScriptImpl($param);
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
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Script - calculScript - enter -");
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
}