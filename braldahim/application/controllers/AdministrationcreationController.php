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
class AdministrationcreationController extends Zend_Controller_Action {
	
	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}
	
	function indexAction() {
		$this->render();
	}
	
	function creationAction() {
		Zend_Loader::loadClass('Creation');
		
		$creation = false;
		$demain  = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
		$aujourdhui  = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		
		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$creation = true;

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
			
			$nbMonstres = (int)$filter->filter($this->_request->getPost("nbMonstres"));
			$nbFilons = (int)$filter->filter($this->_request->getPost("nbFilons"));
			$nbPlantes = (int)$filter->filter($this->_request->getPost("nbPlantes"));

			if ($nbMonstres < 0 || $nbFilons < 0 && $nbPlantes < 0) {
				throw new Zend_Exception("::boisAction : prixVente(".$prixVente.") ou prixReprise(".$prixReprise.") ou nbInitial(".$nbInitial.") invalide");
			}
			
			$this->ajouteParametres($nbMonstres, $nbFilons, $nbPlante);
		}
		
		$this->view->dateCreation = $demain;
		$this->view->creation = $creation;
		
		$this->render();
	}
	
	private function ajouteParametres($nbMonstres, $nbFilons, $nbPlante) {
		//TODO
	}
	
	private function creationPrepare() {
		$creationTable = new Creation();
		
		$listeDatesRowset = $creationTable->findDistinctDate();
		$listeDates = null;
		foreach($listeDatesRowset as $r) {
			$listeDates[] = $r["date_creation"];
		}
		
		$creationsRowset = $creationTable->fetchAll();
		
		$creations = null;
		foreach ($creationsRowset as $c) {
			$creations[] = array(
				"id_creation" => $c["id_creation"], 
				"type_creation" => $c["type_creation"], 
				"date_creation" => $c["date_creation"], 
				"nb_creation" => $c["nb_creation"], 
			);
		}
		
		$this->view->creations = $creations;
		$this->view->listeDates = $listeDates;
	}
}

