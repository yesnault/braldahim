<?php

class AdministrationHobbitsController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}

	function indexAction() {
		$this->render();
	}

	function hobbitsAction() {
		Zend_Loader::loadClass('Hobbit');

		$this->hobbitsPrepare();
		$this->render();
	}
	
	function hobbitAction() {
		Zend_Loader::loadClass('Hobbit');

		$this->modificationHobbit = false;
		
		if ($this->_request->isPost() && $this->_request->get('idhobbit') == $this->_request->getPost("id_hobbit")) {
			$tabPost = $this->_request->getPost();
			foreach($tabPost as $key => $value) {
				if ($key != 'id_hobbit' && substr($key, -7) == "_hobbit") {
					$data[$key] = $value;
				}
			}
			
			$hobbitTable = new Hobbit();
			$where = "id_hobbit=".$this->_request->getPost("id_hobbit");
			$hobbitTable->update($data, $where);
			$this->view->modificationHobbit = true;
		}

		$this->hobbitPrepare();
		$this->render();
	}

	private function hobbitsPrepare() {
		$hobbitTable = new Hobbit();
		
		$page = 1;
		$nbMax = 20;
		
		$hobbitsRowset = $hobbitTable->findAll($page, $nbMax);

		foreach($hobbitsRowset as $h) {
			$hobbits[] = array("id_hobbit" =>$h["id_hobbit"],
				"x_hobbit" =>$h["x_hobbit"] ,
				"y_hobbit" =>$h["y_hobbit"] ,
				"nom_hobbit" =>$h["nom_hobbit"],
				"prenom_hobbit" =>$h["prenom_hobbit"],
				"pa_hobbit" =>$h["pa_hobbit"],
				"castar_hobbit" =>$h["castar_hobbit"]
			);
		}
		$this->view->hobbits = $hobbitsRowset;
	}
	
	private function hobbitPrepare() {
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->findById($this->_request->get('idhobbit'));
		if (count($hobbitRowset) == 1) {
			$this->view->hobbit = $hobbitRowset->toArray();
		} else {
			$this->view->hobbit = null;
		}
		$this->view->id_hobbit = $this->_request->get('idhobbit');
		
		if ($this->_request->get('mode') == "" || $this->_request->get('mode') == "simple") {
			$this->view->mode = "simple";
			$keySimple[] = "id_hobbit";
			$keySimple[] = "x_hobbit";
			$keySimple[] = "y_hobbit";
			$keySimple[] = "pa_hobbit";
			$keySimple[] = "date_fin_tour_hobbit";
			$keySimple[] = "castars_hobbit";
			$this->view->keySimple = $keySimple;
		} else {
			$this->view->mode = "complexe";			
		}
	}
}

