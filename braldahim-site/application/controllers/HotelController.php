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
class HotelController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
	}

	function indexAction() {
		Zend_Loader::loadClass("Bral_Hotel_Factory");
		$box = Bral_Hotel_Factory::getBox($this->_request, $this->view);
		
		$this->view = $box->getPreparedView();
		$this->render();
	}

	function loadAction() {
		Zend_Loader::loadClass("Bral_Xml_Response");
		Zend_Loader::loadClass("Bral_Xml_Entry");
		
		Zend_Loader::loadClass("Bral_Hotel_Factory");

		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();

		$this->xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$box = Bral_Hotel_Factory::getBox($this->_request, $this->view);
		$xml_entry->set_box($box);
		$xml_entry->set_valeur($box->getNomInterne());
		$this->xml_response->add_entry($xml_entry);
		unset($xml_entry);
		$this->xml_response->render();

	}

}