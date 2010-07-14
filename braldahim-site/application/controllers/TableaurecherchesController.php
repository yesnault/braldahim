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
class TableaurecherchesController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		Zend_Loader::loadClass("Bral_Util_Lien");
		$this->prepareContrats();
		$this->render();
	}

	private function prepareContrats() {
		
		$tabGredins = null;
		$taRedresseurs = null;
		
		Zend_Loader::loadClass("Contrat");
		$tableContrat = new Contrat();
		$contratsEnCours = $tableContrat->findEnCours();
		
		if ($contratsEnCours != null) {
			foreach($contratsEnCours as $c) {
				if ($c["type_contrat"] == "gredin") {
					$tabGredins[$c["id_fk_cible_braldun_contrat"]] = $c["nombre"];
				} else {
					$taRedresseurs[$c["id_fk_cible_braldun_contrat"]] = $c["nombre"];
				}
			}
		}
		
		$this->view->tabGredins = $tabGredins;
		$this->view->tabRedresseurs = $taRedresseurs;
	}

}