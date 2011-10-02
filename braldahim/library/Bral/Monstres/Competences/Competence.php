<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Monstres_Competences_Competence
{

	protected $monstre = null;
	protected $cible = null;
	protected $view = null;
	protected static $config = null;

	public function __construct($competence, &$monstre, $view)
	{
		$this->competence = $competence;
		$this->monstre = &$monstre;
		self::$config = Zend_Registry::get('config');
		$this->view = $view;
	}

	public function action()
	{
		Bral_Util_Log::viemonstres()->trace(get_class($this) . " - action - (idm:" . $this->monstre["id_monstre"] . ") - enter");

		$retour = $this->actionSpecifique();

		Bral_Util_Log::viemonstres()->trace(get_class($this) . " - action - (idm:" . $this->monstre["id_monstre"] . ") - exit");
		return $retour;
	}

	abstract function actionSpecifique();
}