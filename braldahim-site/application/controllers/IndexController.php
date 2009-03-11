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
class IndexController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->prepareInfosJeu();
		$this->render();
	}
	
	private function prepareInfosJeu() {
		Zend_Loader::loadClass('InfoJeu');
		$infoJeuTable = new InfoJeu();
		
		$infosRowset = $infoJeuTable->findAllAccueil();
		$infosJeu = null;
		foreach ($infosRowset as $i) {
			$infosJeu[] = array(
				"id_info_jeu" => $i["id_info_jeu"],
				"date_info_jeu" => $i["date_info_jeu"],
				"text_info_jeu" => $i["text_info_jeu"],
				"est_sur_accueil_info_jeu" => $i["est_sur_accueil_info_jeu"],
				"lien_info_jeu" => $i["lien_info_jeu"],
				);
		}
		
		$this->view->infosJeu = $infosJeu;
	}
}