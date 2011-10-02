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
class ScriptsController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass("Bral_Scripts_Factory");
		Zend_Loader::loadClass("Bral_Scripts_Script");

		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
	}

	function indexAction()
	{
		$this->_redirect('/');
	}

	function vueAction()
	{
		$this->view->retour = Bral_Scripts_Factory::calculScript("Vue", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function profilAction()
	{
		$this->view->retour = Bral_Scripts_Factory::calculScript("Profil", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function evenementsAction()
	{
		$this->view->retour = Bral_Scripts_Factory::calculScript("Evenements", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function competencesAction()
	{
		$this->view->retour = Bral_Scripts_Factory::calculScript("Competences", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function labanAction()
	{
		Zend_Loader::loadClass("Bral_Scripts_Conteneur");
		$this->view->retour = Bral_Scripts_Factory::calculScript("Laban", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function coffreAction()
	{
		Zend_Loader::loadClass("Bral_Scripts_Conteneur");
		$this->view->retour = Bral_Scripts_Factory::calculScript("Coffre", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function charretteAction()
	{
		Zend_Loader::loadClass("Bral_Scripts_Conteneur");
		$this->view->retour = Bral_Scripts_Factory::calculScript("Charrette", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function echoppesAction()
	{
		Zend_Loader::loadClass("Bral_Scripts_Conteneur");
		$this->view->retour = Bral_Scripts_Factory::calculScript("Echoppes", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function equipementsAction()
	{
		Zend_Loader::loadClass("Bral_Scripts_Conteneur");
		$this->view->retour = Bral_Scripts_Factory::calculScript("Equipements", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function champsAction()
	{
		Zend_Loader::loadClass("Bral_Scripts_Conteneur");
		$this->view->retour = Bral_Scripts_Factory::calculScript("Champs", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function appelsAction()
	{
		Zend_Loader::loadClass("Bral_Scripts_Conteneur");
		$this->view->retour = Bral_Scripts_Factory::calculScript("Appels", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}

	function quetesAction()
	{
		Zend_Loader::loadClass("Bral_Scripts_Conteneur");
		$this->view->retour = Bral_Scripts_Factory::calculScript("Quetes", $this->view, $this->_request);
		echo $this->view->render("scripts/resultat.phtml");
	}
}