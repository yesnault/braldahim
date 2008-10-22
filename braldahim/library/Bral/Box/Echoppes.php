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
class Bral_Box_Echoppes extends Bral_Box_Box {
	
	function getTitreOnglet() {
		return "&Eacute;choppes";
	}
	
	function getNomInterne() {
		return "box_echoppes";		
	}
	
	function getChargementInBoxes() {
		return false;
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		if ($this->view->affichageInterne) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/echoppes.phtml");
	}
	
	private function data() {
		Zend_Loader::loadClass("Bral_Echoppes_Echoppe");
		Zend_Loader::loadClass("Bral_Echoppes_Liste");
		$box = new Bral_Echoppes_Liste("liste", $this->_request, $this->view, "ask");
		$idEchoppeCourante = $box->prepareCommun();

		if ($idEchoppeCourante != false) {
			$box = Bral_Echoppes_Factory::getVoir($this->_request, $this->view, $idEchoppeCourante);
			$this->view->htmlContenu = $box->render();
		} else {
			$this->view->htmlContenu = $box->render();
		}
	}
}
