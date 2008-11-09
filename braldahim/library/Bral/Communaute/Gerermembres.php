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
class Bral_Communaute_Gerermembres extends Bral_Communaute_Communaute {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass("Communaute");
		Zend_Loader::loadClass("RangCommunaute");

		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		
		$this->view->message = null;
		$this->preparePage();
		$this->updateRangHobbit();
		$this->updateExclureHobbit();
	}

	function getNomInterne() {
		return "box_communaute_action";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function anotherXmlEntry() {
		if ($this->view->message != null) {
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("action");
			$xml_entry->set_valeur("effect.disappear");
			$xml_entry->set_data("communaute_gerer_membre_update");
			return $xml_entry;
		} else {
			return null;
		}
	}			
	
	private function prepareRender() {
		$communaute = null;
		$this->view->tri = "";
		$this->view->filtre = "";
		$this->view->page = "";
		$this->view->precedentOk = false;
		$this->view->suivantOk = false;
		
		$communauteTable = new Communaute();
		$communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_hobbit);
		if (count($communauteRowset) == 1) {
			$communaute = $communauteRowset[0];
		}
		
		if ($communaute == null) {
			throw new Zend_Exception(get_class($this)." Communaute Invalide");
		}
		
		$hobbitTable = new Hobbit();
		$nbMembresTotal = $hobbitTable->countByIdCommunaute($communaute["id_communaute"]);
		
		$hobbitRowset = $hobbitTable->findByIdCommunaute($communaute["id_communaute"], $this->_filtre, $this->_page, $this->_nbMax, $this->_ordreSql, $this->_sensOrdreSql);
		$tabMembres = null;

		foreach($hobbitRowset as $m) {
			$tabMembres[] = array(
				"id_hobbit" => $m["id_hobbit"],
				"nom_hobbit" => $m["nom_hobbit"],
				"prenom_hobbit" => $m["prenom_hobbit"],
				"niveau_hobbit" => $m["niveau_hobbit"],
				"date_entree" => $m["date_entree_communaute_hobbit"],
				"id_rang_communaute" => $m["id_rang_communaute"],
				"nom_rang_communaute" => $m["nom_rang_communaute"],
				"ordre_rang_communaute" => $m["ordre_rang_communaute"],
			);
		}
		
		$rangCommunauteTable = new RangCommunaute();
		$rangsCommunauteRowset = $rangCommunauteTable->findByIdCommunaute($communaute["id_communaute"]);
		$tabRangs = null;

		foreach($rangsCommunauteRowset as $r) {
			$tabRangs[] = array(
				"id_rang" => $r["id_rang_communaute"],
				"nom" => $r["nom_rang_communaute"],
				"ordre_rang_communaute" => $r["ordre_rang_communaute"],
			);
		}
		
		if ($this->_page == 1) {
			$this->view->precedentOk = false;
		} else {
			$this->view->precedentOk = true;
		}

		if (count($tabMembres) == 0) {
			$this->view->suivantOk = false;
		} else {
			$this->view->suivantOk = true;
		}
		
		$this->view->page = $this->_page;
		$this->view->filtre = $this->_filtre;
		$this->view->ordre = $this->_ordre;
		$this->view->sensOrdre = $this->_sensOrdre;
		$this->view->tabRangs = $tabRangs;
		$this->view->tabMembres = $tabMembres;
		$this->view->nbMembresTotal = $nbMembresTotal;
		$this->view->nom_interne = $this->getNomInterne();
	}
	
	public function render() {
		$this->prepareRender();
		return $this->view->render("interface/communaute/gerer/membres.phtml");
	}
	
	private function preparePage() {
		$this->_page = 1;
		
		if ($this->_request->get("valeur_1") == "f") {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if ($this->_request->get("valeur_1") == "p") {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if ($this->_request->get("valeur_1") == "s") {
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else if ($this->_request->get("valeur_1") == "o") {
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6")) + 1;
		} else if ($this->_request->get("valeur_1") == "r" || $this->_request->get("valeur_1") == "e") { // rang
			$this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3"));
			$this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
			$ordre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
			$sensOrdre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_6"));
		} else {
			$this->_page = 1;
			$this->_filtre = -1;
			$ordre = -1;
			$sensOrdre = 1;
		}
		
		$this->_ordre = $ordre;
		$this->_sensOrdre = $sensOrdre;
		
		$this->_ordreSql = $this->getChampOrdre($ordre);
		$this->_sensOrdreSql = $this->getSensOrdre($sensOrdre);
		
		if ($this->_page < 1) {
			$this->_page = 1;
		}
		$this->_nbMax = $this->view->config->communaute->membres->nb_affiche;
	}
	
	private function getChampOrdre($ordre) {
		$retour = "";
		if ($ordre == 1) {
			$retour = "prenom_hobbit";
		} elseif ($ordre == 2) {
			$retour = "nom_hobbit";
		} elseif ($ordre == 3) {
			$retour = "id_hobbit";
		} elseif ($ordre == 4) {
			$retour = "niveau_hobbit";
		} elseif ($ordre == 5) {
			$retour = "date_entree_communaute_hobbit";
		} elseif ($ordre == 6) {
			$retour = "id_rang_communaute";
		} else {
			$retour = "prenom_hobbit";
		}
		return $retour;
	}
	
	private function getSensOrdre($sensOrdre) {
		$sens = " ASC ";
		if ($sensOrdre % 2 == 0) {
			return " DESC ";
		} else {
			return " ASC ";
		}
		return $sens;
	}
	
	private function updateRangHobbit() {
		if ($this->_request->get("valeur_1") == "r") {
			$this->prepareRender();
			
			$idHobbit = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_7"));
			$idRangHobbit = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_8"));
			
			$hobbitTrouve = false;
			foreach($this->view->tabMembres as $m) {
				if ($m["id_hobbit"] == $idHobbit && $m["ordre_rang_communaute"] != 1) { // le gestionnaire ne peut pas etre modifie
					$hobbitTrouve = true;
					break;
				}
			}
			if ($hobbitTrouve == false) {
				throw new Zend_Exception(get_class($this)." Hobbit invalide : val=".$idHobbit);
			}
			
			$rangTrouve = false;
			foreach($this->view->tabRangs as $r) {
			if ($r["id_rang"] == $idRangHobbit) {
					$rangTrouve = true;
					break;
				}
			}
			if ($rangTrouve == false) {
				throw new Zend_Exception(get_class($this)." rang invalide : val=".$idRangHobbit);
			}
			
			$hobbitTable = new Hobbit();
			$data = array('id_fk_rang_communaute_hobbit' => $idRangHobbit);
			$where = 'id_hobbit = '.$idHobbit;
			$hobbitTable->update($data, $where);
			
			$this->view->message = "Modification du Hobbit ".$idHobbit. " effectuée";
		}
	}
	
	private function updateExclureHobbit() {
		if ($this->_request->get("valeur_1") == "e") {
			$this->prepareRender();
			
			$idHobbit = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_9"));
			
			$hobbitTrouve = false;
			foreach($this->view->tabMembres as $m) {
				if ($m["id_hobbit"] == $idHobbit && $m["ordre_rang_communaute"] != 1) { // le gestionnaire ne peut pas etre exclu
					$hobbitTrouve = true;
					break;
				}
			}
			if ($hobbitTrouve == false) {
				throw new Zend_Exception(get_class($this)." Hobbit invalide : val=".$idHobbit);
			}
			
			$hobbitTable = new Hobbit();
			$data = array(
				'id_fk_communaute_hobbit' => null,
				'id_fk_rang_communaute_hobbit' => null ,
				'date_entree_communaute_hobbit' => null,
			);
			$where = 'id_hobbit = '.$idHobbit;
			$hobbitTable->update($data, $where);
			
			$this->view->message = "Exclusion du Hobbit ".$idHobbit. " effectuée";
		}
	}
}
