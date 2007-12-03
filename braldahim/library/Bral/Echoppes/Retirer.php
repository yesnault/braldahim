<?php

class Bral_Echoppes_Retirer extends Bral_Echoppes_Echoppe {

	function __construct($nomSystemeAction, $request, $view, $action, $id_echoppe = false) {
		if ($id_echoppe !== false) {
			$this->idEchoppe = $id_echoppe;
		}
		parent::__construct($nomSystemeAction, $request, $view, $action);
	}
	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {

	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}
}