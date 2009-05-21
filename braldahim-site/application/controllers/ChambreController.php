<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class ChambreController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');

	}

	function indexAction() {
		$this->prepareMetiers();
		$this->prepareEchoppes();
		$this->render();
	}

	private function prepareMetiers() {
		Zend_Loader::loadClass("HobbitsMetiers");
		$hobbitsMetiers = new HobbitsMetiers();

		$metiersRowset = $hobbitsMetiers->countAllByMetier();

		$tabMetiers = null;
		foreach($metiersRowset as $m) {
			$tabMetiers[] = array(
				"nom_metier" => $m["nom_masculin_metier"],
				"nombre" => $m["nombre"],
			);
		}

		$this->view->metiers = $tabMetiers;
	}

	private function prepareEchoppes() {
		Zend_Loader::loadClass("Echoppe");
		$echoppeTable = new Echoppe();

		$echoppesRowset = $echoppeTable->findAllWithRegion();

		$tabEchoppes = null;
		foreach($echoppesRowset as $m) {
			$tabEchoppes[$m["nom_region"]][] = array(
				"nom_region" => $m["nom_region"],
				"nom_metier" => $m["nom_masculin_metier"],
				"nombre" => $m["nombre"],
			);
		}

		$this->view->echoppes = $tabEchoppes;
	}
}