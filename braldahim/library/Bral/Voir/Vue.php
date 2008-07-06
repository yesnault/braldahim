<?php

class Bral_Voir_Vue {

	function __construct($request, $view) {
		$this->_request = $request;
		$this->view = $view;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		return $this->view->render("voir/vue.phtml");
	}
}
