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
		$infoJeu["lien_wiki_info_jeu"] = "Url de l'article sur le wiki";
		$infoJeu["type_info_jeu"] = "annonce";
		$infoJeu["titre_info_jeu"] = null;

		$tabTypes["annonce"] = "Annonce à droite";
		$tabTypes["histoire"] = "Histoire de gazette";

		$this->view->infoJeu = $infoJeu;
		$this->view->tabTypes = $tabTypes;
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
			$titre = $filter->filter($this->_request->getPost('titre_info'));
			$lien = $filter->filter($this->_request->getPost('lien_info'));
			$lienWiki = $filter->filter($this->_request->getPost('lien_wiki_info'));
			$type = $filter->filter($this->_request->getPost('type_info'));

			$infoJeuTable = new InfoJeu();

			if ($this->_request->get('idinfoEdit') != -1) {
				$data = array('titre_info_jeu' => $titre, 'text_info_jeu' => $texte, 'lien_info_jeu' => $lien, 'lien_wiki_info_jeu' => $lienWiki, 'type_info_jeu' => $type);
				$where = 'id_info_jeu = '.$this->_request->get('idinfoEdit');
				$infoJeuTable->update($data, $where);
				$idInfo = $this->_request->get('idinfoEdit');
			} else {
				$data = array(
					'date_info_jeu' => date("Y-m-d H:i:s"),
				 	'text_info_jeu' => $texte,
					'lien_info_jeu' => $lien,
					'lien_wiki_info_jeu' => $lienWiki,
					"type_info_jeu" => $type,
					"titre_info_jeu" => $titre,
					
				);
				$idInfo = $infoJeuTable->insert($data);

				if ($this->view->config->twitter->use == '1') {
					$this->twitter($texte, $titre, $lien, $lienWiki);
				}
			}

			$infosRowset = $infoJeuTable->findById($idInfo);
			$infosRowset = $infosRowset->toArray();
			$this->view->infoJeu = $infosRowset;
		}

		$this->infosJeuPrepare();
		$this->render();
	}

	private function twitter($texte, $titre, $lien, $lienWiki) {
		Zend_Loader::loadClass("Zend_Service_Twitter");
		$twitter = new Zend_Service_Twitter($this->view->config->twitter->username, $this->view->config->twitter->password);
		// verify your credentials with twitter
		$response = $twitter->account->verifyCredentials();

		$texteTwitter = "";

		if ($lienWiki != null && $lienWiki != "") {
			if ((strlen($texteTwitter) +  strlen(" Suite: ")) < 120) {
				$texteTwitter .= " Suite: ".$lienWiki;
			}
		}

		if ($lien != null && $lien != "") {
			if ((strlen($texteTwitter) +  strlen(" Discussions: ")) < 120) {
				$texteTwitter .= " Discussions: ".$lien;
			}
		}

		if ($texte != null && $texte != "") {
			if ((strlen($texteTwitter) +  strlen($texte)) < 135) {
				$texteTwitter = $texte. $texteTwitter;
			} elseif (strlen($texteTwitter) < 135) {
				$texteTwitter = substr($texte, 0, 135-strlen($texteTwitter)). $texteTwitter;
			}
		}

		if ((strlen($texteTwitter) +  strlen($titre)) < 135) {
			if ($titre != null && $titre != "") {
				$texteTwitter = $titre.".".$texteTwitter;
			}
		}

		$response = $twitter->status->update($texteTwitter);
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
				"titre_info_jeu" => $i["titre_info_jeu"],
				"lien_info_jeu" => $i["lien_info_jeu"],
				"lien_wiki_info_jeu" => $i["lien_wiki_info_jeu"],
				"type_info_jeu" => $i["type_info_jeu"],
			);
		}

		$this->view->infosJeu = $infosJeu;
	}
}

