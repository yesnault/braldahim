<?php

class Bral_Echoppes_Deposercaisse extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
	}
}