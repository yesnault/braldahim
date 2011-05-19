<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Communaute_Communaute {

	function __construct($nomSystemeAction, $request, $view, $action, $interne = null) {
		Zend_Loader::loadClass('Bral_Util_Communaute');

		$this->view = $view;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeAction;
		$this->_request = $request;

		$this->view->nb_pa = 0;
		$this->view->titreAction = $this->getTitre();

		$this->prepareCommun();
		$this->calculNbPa();

		switch($this->action) {
			case "box" :
				break;
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	abstract function getTitre();
	abstract function getListBoxRefresh();
	abstract function getTitreOnglet();
	abstract function setDisplay($display) ;
	abstract function getNomInterne();

	public function getTablesHtmlTri() {
		return false;
	}

	public function getIdEchoppeCourante() {
		return false;
	}

	public function getIdChampCourant() {
		return false;
	}

	protected function calculNbPa() {
		if ($this->view->user->pa_braldun - $this->view->nb_pa < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
	}

	function anotherXmlEntry() {
		return null;
	}

	public function render() {
		switch($this->action) {
			case "ask":
				$texte = $this->view->render("communaute/".$this->nom_systeme."_formulaire.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				return $this->view->render("commun/commun_formulaire.phtml");
				break;
			case "do":
				$texte = $this->view->render("communaute/".$this->nom_systeme."_resultat.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				return $this->view->render("commun/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	public function majBraldun() {
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();

		$this->view->user->pa_braldun = $this->view->user->pa_braldun - $this->view->nb_pa;
		$this->view->user->poids_transporte_braldun = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_braldun, $this->view->user->castars_braldun);

		$data = array(
			'pa_braldun' => $this->view->user->pa_braldun,
			'castars_braldun' => $this->view->user->castars_braldun,
			'id_fk_lieu_resurrection_braldun' => $this->view->user->id_fk_lieu_resurrection_braldun,
		);
		$where = "id_braldun=".$this->view->user->id_braldun;
		$braldunTable->update($data, $where);
	}
}