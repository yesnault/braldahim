<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class GestionController extends Zend_Controller_Action
{

	function init()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlRole(get_class($this));

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction()
	{

		Zend_Loader::loadClass("BraldunsRoles");
		$braldunsRoles = new BraldunsRoles();
		$roles = $braldunsRoles->findByIdBraldun(Zend_Auth::getInstance()->getIdentity()->id_braldun);

		$tabRoles = null;
		foreach ($roles as $r) {
			$tabRoles[] = $r["nom_systeme_role"];
		}

		$this->view->roles = $tabRoles;

		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("TypeLieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->fetchAll("id_fk_type_lieu <>" . TypeLieu::ID_TYPE_RUINE, "id_fk_type_lieu asc");
		$this->view->administrationLieux = $lieux;

		$lieuxSansDescription = $lieuTable->fetchAll("id_fk_type_lieu <>" . TypeLieu::ID_TYPE_RUINE . " and (description_lieu is null or description_lieu like '')", "id_fk_type_lieu asc");
		$this->view->administrationSansDescriptionLieux = $lieuxSansDescription;

		$lieuxAvecDescription = $lieuTable->fetchAll("description_lieu is not null and description_lieu not like ''", "id_fk_type_lieu asc");
		$this->view->administrationAvecDescriptionLieux = $lieuxAvecDescription;

		$tousLieux = $lieuTable->fetchAll(null, "id_fk_type_lieu asc");
		$this->view->administrationTousLieux = $tousLieux;

		Zend_Loader::loadClass("Bougrie");
		$bougrieTable = new Bougrie();
		$bougries = $bougrieTable->fetchAll();
		$this->view->administrationBougries = $bougries;

		$this->render();
	}
}
