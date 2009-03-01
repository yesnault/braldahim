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
class Bral_Controller_Action extends Zend_Controller_Action {
	
	public function init() {
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		
		if ($this->view->config->general->actif != 1) {
			$this->_redirect('/auth/logoutajax');
		} else if (!Zend_Auth::getInstance()->hasIdentity() || $this->_request->get("dateAuth") != $this->view->user->dateAuth ) {
			$this->_redirect('/auth/logoutajax');
		} else if (!Zend_Auth::getInstance()->hasIdentity() 
			|| ($this->_request->action != 'index' && 
				$this->view->user->initialCall == false && 
				$this->_request->get("dateAuth") != $this->view->user->dateAuth)
			|| !isset($this->view->user) || !isset($this->view->user->email_hobbit)) {
				if (!Zend_Auth::getInstance()->hasIdentity() ) {
					Bral_Util_Log::tech()->warn("Bral_Controller_Action - logoutajax 1A - Session perdue");
				} else {
					Bral_Util_Log::tech()->warn("Bral_Controller_Action - logoutajax 1B action=".$this->_request->action. " initialCall=".$this->view->user->initialCall. " dateAuth=".$this->_request->get("dateAuth"). " dateAuth2=".$this->view->user->dateAuth);
				}
				
				$this->_redirect('/auth/logoutajax');
		} else {
			Zend_Loader::loadClass('Bral_Util_BralSession');
			if (Bral_Util_BralSession::refreshSession() == false) {
				$this->_redirect('/auth/logoutajax');
			} 
		}
		$this->view->user = Zend_Auth::getInstance()->getIdentity(); // pour rafraichissement session
		$this->xml_response = new Bral_Xml_Response();

		$this->modification_tour = false;
		$t = Bral_Box_Factory::getTour($this->_request, $this->view, false);
		if ($t->modificationTour()) {
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("action");
			$xml_entry->set_valeur("goto");
			$xml_entry->set_data("/interface/");
			$this->xml_response->add_entry($xml_entry);
			$this->modification_tour = true;
		}
	}
	
	protected function doBralAction($factory) {

		if (!$this->modification_tour) { // S'il n'y a pas eu de modification du tour, on passe à la competence
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("display");

			try {
				if ($factory == "Bral_Competences_Factory") {
					$action = Bral_Competences_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Echoppes_Factory") {
					$action = Bral_Echoppes_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Echoppe_Factory") {
					$action = Bral_Echoppe_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Boutique_Factory") {
					$action = Bral_Boutique_Factory::getAction($this->_request, $this->view);	
				} elseif ($factory == "Bral_Lieux_Factory") {
					$action = Bral_Lieux_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Soule_Factory") {
					$action = Bral_Soule_Factory::getAction($this->_request, $this->view);
				}
				$xml_entry->set_valeur($action->getNomInterne());
				$xml_entry->set_data($action->render());
				$this->xml_response->add_entry($xml_entry);
				$boxToRefresh = $action->getListBoxRefresh();
				for ($i=0; $i<count($boxToRefresh); $i++) {
					$xml_entry = new Bral_Xml_Entry();
					if ($boxToRefresh[$i] == "box_vue" || $boxToRefresh[$i] == "box_laban" || $boxToRefresh[$i] == "box_echoppes" || $boxToRefresh[$i] == "box_soule") { 
						$xml_entry->set_type("load_box");
						//$c = Bral_Box_Factory::getBox($boxToRefresh[$i], $this->_request, $this->view, false);
						//$nomInterne = $c->getNomInterne();
						$nomInterne = $boxToRefresh[$i];
						$xml_entry->set_data(null);
					} else {
						$xml_entry->set_type("display");
						$c = Bral_Box_Factory::getBox($boxToRefresh[$i], $this->_request, $this->view, true);
						$xml_entry->set_data($c->render());
						$nomInterne = $c->getNomInterne();
					}
					$xml_entry->set_valeur($nomInterne);
					$this->xml_response->add_entry($xml_entry);
				}
				if ($action->getIdEchoppeCourante() !== false) {
					$this->xml_response->add_entry($this->getXmlEntryVoirEchoppe($action));
				}
				Bral_Util_Messagerie::setXmlResponseMessagerie($this->xml_response, $this->view->user->id_hobbit);
			} catch (Zend_Exception $e) {
				$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
				$xml_entry->set_valeur($b->getNomInterne());
				$xml_entry->set_data($b->render());
				$this->xml_response->add_entry($xml_entry);
			}
		}
		$this->xml_response->render();
	}
	
	private function getXmlEntryVoirEchoppe($action) {
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$c = Bral_Echoppes_Factory::getVoir($this->_request, $this->view, $action->getIdEchoppeCourante());
		$xml_entry->set_valeur($c->getNomInterne());
		$xml_entry->set_data($c->render());
		return $xml_entry;
	}
 }
