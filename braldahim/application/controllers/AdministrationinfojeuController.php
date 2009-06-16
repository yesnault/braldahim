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
class AdministrationinfojeuController extends Zend_Controller_Action {
	
	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}
		
		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();
		
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		
		Zend_Loader::loadClass('InfoJeu');
		
		$infoJeu["id_info_jeu"] = -1;
		$infoJeu["date_info_jeu"] = -1;
		$infoJeu["text_info_jeu"] = "NouveauTexte. [url=lienhttp]Exemple lien[/url]";
		$infoJeu["lien_info_jeu"] = "Url du post sur le forum";
		$this->view->infoJeu = $infoJeu;
	}
	
	function indexAction() {
		$this->render();
	}
	
	public function estaccueilAction() {
		$this->updateAccueil('oui');
		$this->_forward('infosjeu');
	}
	
	public function estnonaccueilAction() {
		$this->updateAccueil('non');
		$this->_forward('infosjeu');
	}
	
	private function updateAccueil($estSurAccueil) {
		if ($this->_request->get('idinfo')) { 
			$infoJeuTable = new InfoJeu();
			$data = array('est_sur_accueil_info_jeu' => $estSurAccueil);
			$where = 'id_info_jeu = '.$this->_request->get('idinfo');
			$infoJeuTable->update($data, $where);
		}
	}
	
	public function infosjeuAction() {
		
		if ($this->_request->get('idinfo')) { 
			$infoJeuTable = new InfoJeu();
			$infosRowset = $infoJeuTable->findById($this->_request->get('idinfo'));
			$infosRowset = $infosRowset->toArray();
			$this->view->infoJeu = $infosRowset;
		}
		
		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');
			
			$creation = true;
			
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
			$texte = $filter->filter($this->_request->getPost('texte_info'));
			$lien = $filter->filter($this->_request->getPost('lien_info'));
			
			$infoJeuTable = new InfoJeu();
			
			if ($this->_request->get('idinfoEdit') != -1) {
				$data = array('text_info_jeu' => $texte, 'lien_info_jeu' => $lien);
				$where = 'id_info_jeu = '.$this->_request->get('idinfoEdit');
				$infoJeuTable->update($data, $where);
				$idInfo = $this->_request->get('idinfoEdit');
			} else {
				$data = array(
					'date_info_jeu' => date("Y-m-d H:i:s"),
				 	'text_info_jeu' => $texte,
					'lien_info_jeu' => $lien
				);
				$idInfo = $infoJeuTable->insert($data);
			}
			
			$infosRowset = $infoJeuTable->findById($idInfo);
			$infosRowset = $infosRowset->toArray();
			$this->view->infoJeu = $infosRowset;
		}
		
		$this->infosJeuPrepare();
		$this->render();
	}
	
	private function infosJeuPrepare() {
		$infoJeuTable = new InfoJeu();
		$infosRowset = $infoJeuTable->fetchAll(null, "date_info_jeu DESC");
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

