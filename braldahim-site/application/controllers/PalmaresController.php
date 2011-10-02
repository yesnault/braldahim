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
class PalmaresController extends Bral_Controller_Box
{

	function init()
	{
		Zend_Loader::loadClass("Bral_Xml_Response");
		Zend_Loader::loadClass("Bral_Xml_Entry");
		Zend_Loader::loadClass("Bral_Util_String");
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesNaissance($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render();
	}

	function naissanceAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesNaissance($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function chasseursgibierAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesChasseursgibier($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function combattantspveAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesCombattantspve($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function combattantspvpAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesCombattantspvp($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function koAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesKo($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function distinctionAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesDistinction($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function experienceAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesExperience($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function gredinsAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesGredins($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function redresseursAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesRedresseurs($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function superbraldunsAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesSuperbralduns($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function monstresAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesMonstres($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function mineursAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesRecolteurs($this->_request, $this->view, "mineurs"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function herboristesAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesRecolteurs($this->_request, $this->view, "herboristes"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function bucheronsrecolteursAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesRecolteurs($this->_request, $this->view, "bucherons"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function chasseursAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesRecolteurs($this->_request, $this->view, "chasseurs"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function apothicairesAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesFabricants($this->_request, $this->view, "apothicaires"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function menuisiersAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesFabricants($this->_request, $this->view, "menuisiers"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function forgeronsAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesFabricants($this->_request, $this->view, "forgerons"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function tanneursAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesFabricants($this->_request, $this->view, "tanneurs"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function bucheronsfabriquantspalissadesAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesFabricants($this->_request, $this->view, "bucheronspalissades"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function bucheronsfabriquantsroutesAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesFabricants($this->_request, $this->view, "bucheronsroutes"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function cuisiniersAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesFabricants($this->_request, $this->view, "cuisiniers"), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function runesAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesRunes($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	function motsruniquesAction()
	{
		$this->prepareFiltre();
		$this->addBoxes(Bral_Palmares_Factory::getBoxesMotsRuniques($this->_request, $this->view), "boite_e");
		$this->prepareCommun();
		$this->render("index");
	}

	private function prepareCommun()
	{
		$this->getBoxesData();
		$this->prepareSelection();
	}

	private function prepareSelection()
	{
		$selection[] = array("nom" => "Naissances", "url" => "naissance");
		$selection[] = array("nom" => "Grands Combattants PvE", "url" => "combattantspve");
		$selection[] = array("nom" => "Grands Combattants PvP", "url" => "combattantspvp");
		$selection[] = array("nom" => "Grands Chasseurs de gibiers", "url" => "chasseursgibier");
		$selection[] = array("nom" => "KO", "url" => "ko");
		$selection[] = array("nom" => "Expérience", "url" => "experience");
		$selection[] = array("nom" => "Monstres", "url" => "monstres");
		$selection[] = array("nom" => "Super Braldûns", "url" => "superbralduns");
		$this->view->selection = $selection;

		$reputation[] = array("nom" => "Distinctions", "url" => "distinction");
		$reputation[] = array("nom" => "Gredins", "url" => "gredins");
		$reputation[] = array("nom" => "Redresseurs de Torts", "url" => "redresseurs");
		$this->view->selectionReputation = $reputation;

		$selectionRecolteurs = null;
		$selectionRecolteurs[] = array("nom" => "Mineurs", "url" => "mineurs");
		$selectionRecolteurs[] = array("nom" => "Herboristes", "url" => "herboristes");
		$selectionRecolteurs[] = array("nom" => "Bûcherons", "url" => "bucheronsrecolteurs");
		$selectionRecolteurs[] = array("nom" => "Chasseurs", "url" => "chasseurs");
		$this->view->selectionRecolteurs = $selectionRecolteurs;

		$selectionFabricants = null;
		$selectionFabricants[] = array("nom" => "Apothicaires", "url" => "apothicaires");
		$selectionFabricants[] = array("nom" => "Menuisiers", "url" => "menuisiers");
		$selectionFabricants[] = array("nom" => "Forgerons", "url" => "forgerons");
		$selectionFabricants[] = array("nom" => "Tanneurs", "url" => "tanneurs");
		$selectionFabricants[] = array("nom" => "Bûcherons - Palissades", "url" => "bucheronsfabriquantspalissades");
		$selectionFabricants[] = array("nom" => "Bûcherons - Sentiers", "url" => "bucheronsfabriquantsroutes");
		$selectionFabricants[] = array("nom" => "Cuisiniers", "url" => "cuisiniers");
		$this->view->selectionFabricants = $selectionFabricants;

		$selectionRunes = null;
		$selectionRunes[] = array("nom" => "Drops Runes", "url" => "runes");
		$selectionRunes[] = array("nom" => "Mots Runiques", "url" => "motsruniques");
		$this->view->selectionRunes = $selectionRunes;
	}

	private function prepareFiltre()
	{
		$listFiltre[] = array("nom" => "Mois en cours", "valeur" => 1);
		$listFiltre[] = array("nom" => "Dernier mois", "valeur" => 2);
		$listFiltre[] = array("nom" => "Année en cours", "valeur" => 3);
		$listFiltre[] = array("nom" => "Année précédente", "valeur" => 4);
		$listFiltre[] = array("nom" => "Depuis toujours", "valeur" => 5);
		$this->view->listFiltre = $listFiltre;
	}

	function loadAction()
	{
		$this->prepareFiltre();
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		$this->xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$box = Bral_Palmares_Factory::getBox($this->_request, $this->view, true);
		$xml_entry->set_box($box);
		$xml_entry->set_valeur($box->getNomInterne());
		$this->xml_response->add_entry($xml_entry);
		unset($xml_entry);
		$this->xml_response->render();
	}

	function palmarestop10Action()
	{
		$this->prepareFiltre();
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		$this->xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$caction = "do_palmares_distinctiontop10_5_jsVersion";
		$box = Bral_Palmares_Factory::getBox($this->_request, $this->view, true, $caction);
		$xml_entry->set_box($box);
		$xml_entry->set_valeur($box->getNomInterne());
		$this->xml_response->add_entry($xml_entry);
		$this->xml_response->renderJs();
	}

	function palmarestop10derniermoisAction()
	{
		$this->prepareFiltre();
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		$this->xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$caction = "do_palmares_distinctiontop10_2_jsVersion";
		$box = Bral_Palmares_Factory::getBox($this->_request, $this->view, true, $caction);
		$xml_entry->set_box($box);
		$xml_entry->set_valeur($box->getNomInterne());
		$this->xml_response->add_entry($xml_entry);
		$this->xml_response->renderJs();
	}
}

