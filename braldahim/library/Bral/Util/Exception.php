<?php

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
class Bral_Util_Exception {

	private function __construct(){}

	public static function traite($e, $afficheEcho = true, $view = null) {
		if ($afficheEcho) {
			echo "Une erreur est survenue. L'equipe Braldahim est prevenue.";
			echo " Si le probleme persiste, merci de prendre contact via le forum Anomalies ";
			echo " en indiquant cette heure ".date("Y-m-d H:m:s");
		}

		$user = "hobbit:inconnu";
		if ($view != null && $view->user != null) {
			$user = $view->user->prenom_hobbit . " ". $view->user->nom_hobbit. " (".$view->user->id_hobbit.")";
		}
		$user .= PHP_EOL;
		Bral_Util_Log::exception()->alert($user.$e);

		$config = Zend_Registry::get('config');
		if ($config->general->mail->exception->use == '1') {
			Zend_Loader::loadClass("Bral_Util_Mail");
			$mail = Bral_Util_Mail::getNewZendMail();

			$mail->setFrom($config->general->mail->exception->from, $config->general->mail->exception->nom);
			$mail->addTo($config->general->mail->exception->from, $config->general->mail->exception->nom);
			$mail->setSubject("[Braldahim-Exception] Exception rencontrée");
				
			$formatTexte = date("Y-m-d H:m:s"). ' '. $_SERVER['REMOTE_ADDR'];
			$formatTexte .=  ' '. $_SERVER['SERVER_NAME'].' '. $_SERVER['REQUEST_METHOD'];
			$formatTexte .= ' '. $_SERVER['REQUEST_URI'];
			$formatTexte .= ' '.$_SERVER['HTTP_USER_AGENT'];
			$formatTexte .= ' '.$user;

			$mail->setBodyText("--------> ".$formatTexte." ".$e." " . PHP_EOL);
			$mail->send();
		}
	}
}