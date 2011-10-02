<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Exception
{

	private function __construct()
	{
	}

	public static function traite($e, $afficheEcho = true, $view = null)
	{
		if ($afficheEcho) {
			echo "Une erreur est survenue. L'equipe Braldahim est prevenue.";
			echo " Si le probleme persiste, merci de prendre contact via le forum Anomalies ";
			echo " en indiquant cette heure " . date("Y-m-d H:i:s");
		}

		$user = "braldun:inconnu";
		if ($view != null && $view->user != null) {
			$user = $view->user->prenom_braldun . " " . $view->user->nom_braldun . " (" . $view->user->id_braldun . ")";
		}
		$user .= PHP_EOL;
		Bral_Util_Log::exception()->alert($user . $e);

		$config = Zend_Registry::get('config');
		if ($config->general->mail->exception->use == '1') {
			Zend_Loader::loadClass("Bral_Util_Mail");
			$mail = Bral_Util_Mail::getNewZendMail();

			$mail->setFrom($config->general->mail->exception->from, $config->general->mail->exception->nom);
			$mail->addTo($config->general->mail->exception->from, $config->general->mail->exception->nom);
			$mail->setSubject("[Braldahim-Exception] Exception rencontrée");

			$formatTexte = 'Heure: ' . date("Y-m-d H:i:s") . PHP_EOL;
			$formatTexte .= 'REMOTE_ADDR: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
			$formatTexte .= 'SERVER_NAME / REQUEST_METHOD: ' . $_SERVER['SERVER_NAME'] . ' ' . $_SERVER['REQUEST_METHOD'] . PHP_EOL;
			$formatTexte .= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
			$formatTexte .= 'HTTP_USER_AGENT: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;
			$formatTexte .= 'Utilisateur: ' . $user . PHP_EOL;
			$formatTexte .= 'Exception : ' . PHP_EOL . $e . PHP_EOL . PHP_EOL;

			if ($view != null && $view->user != null) {
				$formatTexte .= 'View User : ' . var_export($view->user, true) . PHP_EOL;
			} else {
				$formatTexte .= 'View User : null' . PHP_EOL;
			}

			$mail->setBodyText("--------> " . $formatTexte . PHP_EOL . PHP_EOL);
			$mail->send();
		}
	}
}