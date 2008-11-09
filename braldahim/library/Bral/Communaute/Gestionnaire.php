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
class Bral_Communaute_Gestionnaire extends Bral_Communaute_Communaute {

	function __construct($request, $view, $interne) {
		Zend_Loader::loadClass("Communaute");
		Zend_Loader::loadClass("RangCommunaute");
		
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
		$this->preparePage();

		$this->view->isUpdateGestionnaire = false;
		
		if ($this->_request->get("caction") == "do_communaute_gestionnaire") {
			$this->updateGestionnaire();
		}
	}

	function getNomInterne() {
		return "box_communaute_action";
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function preparePage() {
		$estGestionnaire = false;
		
		$communauteTable = new Communaute();
		$communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_hobbit);
		if (count($communauteRowset) == 1) {
			$communaute = $communauteRowset[0];
			if ($communaute["id_fk_hobbit_gestionnaire_communaute"] == $this->view->user->id_hobbit) {
				$estGestionnaire = true;
			}
		}
		
		if ($estGestionnaire == false) {
			throw new Zend_Exception(get_class($this)." Vos n'etes pas Gestionaire");
		}
		if ($communaute == null) {
			throw new Zend_Exception(get_class($this)." Communaute Invalide");
		}
		
		$this->communaute = $communaute;
	}
	
	private function prepareRender() {
		$c = array(
			"prenom_hobbit" => $this->communaute["prenom_hobbit"], 
			"nom_hobbit" => $this->communaute["nom_hobbit"], 
			"id_hobbit" => $this->communaute["id_hobbit"], 
		);
		$this->view->communaute = $c;
		
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->findByIdCommunaute($this->communaute["id_communaute"]);
		$tabMembres = null;

		foreach($hobbitRowset as $m) {
			if ($m["ordre_rang_communaute"] != 1) { // on ne met pas le gestionnaire actuel dans la liste
				$tabMembres[] = array(
					"id_hobbit" => $m["id_hobbit"],
					"nom_hobbit" => $m["nom_hobbit"],
					"prenom_hobbit" => $m["prenom_hobbit"],
					"id_rang_communaute" => $m["id_rang_communaute"],
					"nom_rang_communaute" => $m["nom_rang_communaute"],
				);
			}
		}

		$this->view->tabMembres = $tabMembres;
	}
		
	public function render() {
		$this->prepareRender();
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute/gerer/gestionnaire.phtml");
	}

	private function updateGestionnaire() {
		$idHobbit = Bral_Util_Controle::getValeurIntVerif($this->_request->getPost("valeur_1"));
		
		$this->prepareRender();
		
		$hobbitTrouve = false;
		foreach($this->view->tabMembres as $m) {
			if ($m["id_hobbit"] == $idHobbit) {
				$hobbitTrouve = true;
				break;
			}
		}
		
		if ($hobbitTrouve == false) {
			throw new Zend_Exception(get_class($this)." Hobbit Invalide:".$idHobbit);
		}
		
		$communauteTable = new Communaute();
		$data = array("id_fk_hobbit_gestionnaire_communaute" => $idHobbit);
		$where = " id_communaute=".$this->communaute["id_communaute"];
		$communauteTable->update($data, $where);
		
		$hobbitTable = new Hobbit();
		$rangCommunauteTable = new RangCommunaute();
		$rowSet = $rangCommunauteTable->findRangCreateur($this->communaute["id_communaute"]);
		
		$data = array('id_fk_rang_communaute_hobbit' => $rowSet["id_rang_communaute"]);
		$where = 'id_hobbit = '.$idHobbit;
		$hobbitTable->update($data, $where);
		
		$rowSet = $rangCommunauteTable->findRangSecond($this->communaute["id_communaute"]);
		$this->view->user->id_fk_rang_communaute_hobbit = $rowSet["id_rang_communaute"];
		$data = array('id_fk_rang_communaute_hobbit' => $this->view->user->id_fk_rang_communaute_hobbit);
		$where = 'id_hobbit = '.$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
		
		$this->view->isUpdateGestionnaire = true;
	}
	
}
