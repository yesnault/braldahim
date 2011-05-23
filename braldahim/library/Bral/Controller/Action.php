<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Controller_Action extends Zend_Controller_Action {

	public function init() {
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		$this->view->estMobile = Zend_Registry::get("estMobile");
	}

	function preDispatch() {

		if ($this->view->config->general->actif != 1) {
			$this->_forward('logoutajax', 'auth');
		} else if (!Zend_Auth::getInstance()->hasIdentity() || $this->_request->get("dateAuth") != $this->view->user->dateAuth ) {
			$this->_forward('logoutajax', 'auth');
		} else if (!Zend_Auth::getInstance()->hasIdentity()
		|| ($this->_request->action != 'index' &&
		$this->view->user->initialCall == false &&
		$this->_request->get("dateAuth") != $this->view->user->dateAuth)
		|| !isset($this->view->user) || !isset($this->view->user->email_braldun)) {
			if (!Zend_Auth::getInstance()->hasIdentity() ) {
				Bral_Util_Log::tech()->warn("Bral_Controller_Action - logoutajax 1A - Session perdue");
			} else {
				$texte = "braldun:inconnu";
				if ($this->view != null && $this->view->user != null) {
					$texte = $this->view->user->prenom_braldun . " ". $this->view->user->nom_braldun. " (".$this->view->user->id_braldun.")";
				}
				Bral_Util_Log::tech()->warn("Bral_Controller_Action - logoutajax 1B ".$texte." action=".$this->_request->action. " initialCall=".$this->view->user->initialCall. " dateAuth=".$this->_request->get("dateAuth"). " dateAuth2=".$this->view->user->dateAuth);
			}

			$this->_forward('logoutajax', 'auth');
		} else {
			Zend_Loader::loadClass('Bral_Util_BralSession');
			if (Bral_Util_BralSession::refreshSession() == false) {
				$texte = "braldun:inconnu";
				if ($this->view != null && $this->view->user != null) {
					$texte = $this->view->user->prenom_braldun . " ". $this->view->user->nom_braldun. " (".$this->view->user->id_braldun.")";
				}
				$texte .= " action=".$this->_request->action. " uri=".$this->_request->getRequestUri();
				Bral_Util_Log::tech()->warn("Bral_Controller_Action - logoutajax ".$texte);
				$this->_forward('logoutajax', 'auth');
			} else {
				$this->view->user = Zend_Auth::getInstance()->getIdentity(); // pour rafraichissement session
			}
		}

		if ($this->view->user == null) {
			$texte = " action=".$this->_request->action. " uri=".$this->_request->getRequestUri();
			Bral_Util_Log::tech()->warn("Bral_Controller_Action - logoutajax ".$texte);
			$this->_forward('logoutajax', 'auth');
		} else {
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
	}

	protected function doBralAction($factory) {

		if (!$this->modification_tour) { // S'il n'y a pas eu de modification du tour, on passe à la competence
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("display");

			try {
				if ($factory == "Bral_Boutique_Factory") {
					$action = Bral_Boutique_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Carnet_Factory") {
					$action = Bral_Carnet_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Blabla_Factory") {
					$action = Bral_Blabla_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Butin_Factory") {
					$action = Bral_Butin_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Charrette_Factory") {
					$action = Bral_Charrette_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Competences_Factory") {
					$action = Bral_Competences_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Communaute_Factory") {
					$action = Bral_Communaute_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Champ_Factory") {
					$action = Bral_Champ_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Champs_Factory") {
					$action = Bral_Champs_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Contrats_Factory") {
					$action = Bral_Contrats_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Contrat_Factory") {
					$action = Bral_Contrat_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Echoppe_Factory") {
					$action = Bral_Echoppe_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Echoppes_Factory") {
					$action = Bral_Echoppes_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Filature_Factory") {
					$action = Bral_Filature_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Filatures_Factory") {
					$action = Bral_Filatures_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Hotel_Factory") {
					$action = Bral_Hotel_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Interfaceaction_Factory") {
					$action = Bral_Interfaceaction_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Lieux_Factory") {
					$action = Bral_Lieux_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Lot_Factory") {
					$action = Bral_Lot_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Messagerie_Factory") {
					$action = Bral_Messagerie_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Quete_Factory") {
					$action = Bral_Quete_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Quetes_Factory") {
					$action = Bral_Quetes_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Soule_Factory") {
					$action = Bral_Soule_Factory::getAction($this->_request, $this->view);
				} elseif ($factory == "Bral_Administrationajax_Factory") {
					$action = Bral_Administrationajax_Factory::getAction($this->_request, $this->view);
				}

				$xml_entry->set_data($action->render());
				$xml_entry->set_valeur($action->getNomInterne());
				$this->xml_response->add_entry($xml_entry);
				$boxToRefresh = null;
				if (!$this->view->estMobile) { // pas de refresh des boites en version mobile
					$boxToRefresh = $action->getListBoxRefresh();
				}
				for ($i=0; $i<count($boxToRefresh); $i++) {
					$xml_entry = new Bral_Xml_Entry();
					$refreshHtmlTable = false;
					if ($boxToRefresh[$i] == "box_vue" || $boxToRefresh[$i] == "box_laban" || $boxToRefresh[$i] == "box_echoppes" || $boxToRefresh[$i] == "box_soule" || $boxToRefresh[$i] == "box_quete") {
						$xml_entry->set_type("load_box");
						//$c = Bral_Box_Factory::getBox($boxToRefresh[$i], $this->_request, $this->view, false);
						//$nomInterne = $c->getNomInterne();
						$nomInterne = $boxToRefresh[$i];
						$xml_entry->set_data("foo");
					} else {
						$xml_entry->set_type("refresh");
						$c = Bral_Box_Factory::getBox($boxToRefresh[$i], $this->_request, $this->view, true);
						$xml_entry->set_data($c->render());
						$nomInterne = $c->getNomInterne();
						$refreshHtmlTable = true;
					}
					$xml_entry->set_valeur($nomInterne);
					$this->xml_response->add_entry($xml_entry);
					if ($refreshHtmlTable) {
						$tabTables = $c->getTablesHtmlTri();
						if ($tabTables != false) {
							Bral_Controller_Action::addXmlEntryTableHtmlTri($this->xml_response, $tabTables);
						}
					}
				}
				if ($action->getIdEchoppeCourante() !== false) {
					$this->xml_response->add_entry($this->getXmlEntryVoirEchoppe($action));
				}
				if ($action->getIdChampCourant() !== false) {
					$this->xml_response->add_entry($this->getXmlEntryVoirChamp($action));
				}

				if ($factory == "Bral_Echoppes_Factory" || "Bral_Communaute_Factory") {
					$tabTables = $action->getTablesHtmlTri();
					if ($tabTables != false) {
						self::addXmlEntryTableHtmlTri($this->xml_response, $tabTables);
					}
				}
				Bral_Util_Messagerie::setXmlResponseMessagerie($this->xml_response, $this->view->user->id_braldun);
			} catch (Zend_Exception $e) {
				$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
				$xml_entry->set_valeur($b->getNomInterne());
				$xml_entry->set_data($b->render());
				$this->xml_response->add_entry($xml_entry);
				Zend_Loader::loadClass("Bral_Util_Exception");
				Bral_Util_Exception::traite($b->render(), false, $this->view);
			}
		}
		$this->xml_response->render();
	}

	public function errorAction() {
		$errors = $this->_getParam('error_handler');
		$exception = $errors->exception;
		Zend_Loader::loadClass("Bral_Util_Exception");
		Bral_Util_Exception::traite("type:".$errors->type." msg:".$exception->getMessage()." ex:".$exception->getTraceAsString(), false, $this->view);
	}

	private function getXmlEntryVoirEchoppe($action) {
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		Zend_Loader::loadClass("Bral_Echoppes_Factory");
		$c = Bral_Echoppes_Factory::getVoir($this->_request, $this->view, $action->getIdEchoppeCourante());
		$xml_entry->set_valeur($c->getNomInterne());
		$xml_entry->set_data($c->render());
		return $xml_entry;
	}

	private function getXmlEntryVoirChamp($action) {
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		Zend_Loader::loadClass("Bral_Champs_Factory");
		$c = Bral_Champs_Factory::getVoir($this->_request, $this->view, $action->getIdChampCourant());
		$xml_entry->set_valeur($c->getNomInterne());
		$xml_entry->set_data($c->render());
		return $xml_entry;
	}

	public static function addXmlEntryTableHtmlTri(&$xmlResponse, $tabTables) {
		foreach($tabTables as $t) {
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("action");
			$xml_entry->set_valeur("HTMLTableTools");
			$xml_entry->set_data($t);
			$xmlResponse->add_entry($xml_entry);
		}
	}
}
