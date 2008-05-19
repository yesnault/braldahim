<?php

class Bral_Util_Mail {
	public static function getNewZendMail() {
		Zend_Loader::loadClass("Zend_Mail");
		
		$c = Zend_Registry::get('config');
		if ($c->general->mail->use_smtp_server == '1') {
			Zend_Loader::loadClass("Zend_Mail_Transport_Smtp");
			$transport = new Zend_Mail_Transport_Smtp($c->general->mail->smtp_server);
			Zend_Mail::setDefaultTransport($transport);
		}
		
		return new Zend_Mail();
	}
}