<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class VoirController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		$this->view->controleur = $this->_request->controller;

		Zend_Loader::loadClass('Bral_Voir_Factory');
	}

	function indexAction()
	{
		echo $this->render();
	}

	function communauteAction()
	{
		$voir = Bral_Voir_Factory::getCommunaute($this->_request, $this->view);
		echo $voir->render();
	}

	function communautesAction()
	{
		$voir = Bral_Voir_Factory::getCommunautes($this->_request, $this->view);
		echo $voir->render();
	}

	function equipementAction()
	{
		$voir = Bral_Voir_Factory::getEquipement($this->_request, $this->view);
		echo $voir->render();
	}

	function potionAction()
	{
		$voir = Bral_Voir_Factory::getPotion($this->_request, $this->view);
		echo $voir->render();
	}

	function runeAction()
	{
		$voir = Bral_Voir_Factory::getRune($this->_request, $this->view);
		echo $voir->render();
	}

	function materielAction()
	{
		$voir = Bral_Voir_Factory::getMateriel($this->_request, $this->view);
		echo $voir->render();
	}

	function braldunAction()
	{
		$voir = Bral_Voir_Factory::getBraldun($this->_request, $this->view);
		echo $voir->render();
	}

	function braldunsAction()
	{
		$voir = Bral_Voir_Factory::getBralduns($this->_request, $this->view);
		echo $voir->render();
	}

	function monstreAction()
	{
		$voir = Bral_Voir_Factory::getMonstre($this->_request, $this->view);
		echo $voir->render();
	}

	function monstretestAction()
	{
		if (!Zend_Auth::getInstance()->hasIdentity() || $this->config->general->production == 1) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass('Monstre');
		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($this->_request->get('idmonstre'));
		if (count($monstreRowset) > 1) {
			$this->view->monstre = $monstreRowset;
		} else {
			$this->view->monstre = null;
		}
		$this->view->id_monstre = $this->_request->get('idmonstre');
		$this->render();
	}

	function doactionAction()
	{
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_response = new Bral_Xml_Response();
		try {
			$voir = Bral_Voir_Factory::getAction($this->_request, $this->view);
			$xml_entry->set_valeur($voir->getNomInterne());
			$xml_entry->set_data($voir->render());
			$xml_response->add_entry($xml_entry);
		} catch (Zend_Exception $e) {
			$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
			$xml_entry->set_valeur($b->getNomInterne());
			$xml_entry->set_data($b->render());
			$xml_response->add_entry($xml_entry);
		}
		$xml_response->render();
	}
}

