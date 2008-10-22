<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
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
