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
abstract class Bral_Charrette_Charrette {

	protected $reloadInterface = false;
	protected $idCharrette = null;

	function __construct($nomSystemeAction, $request, $view, $action) {
		Zend_Loader::loadClass("Bral_Util_Evenement");
		Zend_Loader::loadClass("Charrette");

		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeAction;
		$this->view->nom_systeme = $this->nom_systeme;

		$this->prepareCharrette();

		$this->calculNbPa();
		$this->prepareCommun();

		switch($this->action) {
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

	private function prepareCharrette() {
		$charretteTable = new Charrette();
		$charretteRowset = $charretteTable->findByIdBraldun($this->view->user->id_braldun);
		if (count($charretteRowset) == 1) {
			$this->view->possedeCharrette = true;
			$this->view->charrette = $charretteRowset[0];
		} else {
			$this->view->possedeCharrette = false;
			$this->view->charrette = null;
		}
	}

	abstract function prepareCommun();
	abstract function prepareFormulaire();
	abstract function prepareResultat();
	abstract function getListBoxRefresh();
	abstract function getNomInterne();
	abstract function getTitreAction();

	public function getIdEchoppeCourante() {
		return false;
	}

	public function getIdChampCourant() {
		return false;
	}
	
	public function calculNbPa() {
		if ($this->view->user->pa_braldun - $this->view->config->game->charrette->nb_pa_action < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
		$this->view->nb_pa = $this->view->config->game->echoppe->nb_pa_service;
	}

	protected function setDetailsEvenement($details, $idType) {
		$this->detailEvenement = $details;
		$this->idTypeEvenement = $idType;
	}

	/*
	 * Mise à jour des événements du braldun : type : compétence.
	 */
	private function majEvenementsCharrette($detailsBot) {
		Bral_Util_Evenement::majEvenements($this->view->user->id_braldun, $this->idTypeEvenement, $this->detailEvenement, $detailsBot, $this->view->user->niveau_braldun);
	}

	function render() {
		$this->view->titreAction = $this->getTitreAction();
		switch($this->action) {
			case "ask":
				return $this->view->render("charrette/".$this->nom_systeme."_formulaire.phtml");
				break;
			case "do":
				$this->view->reloadInterface = $this->reloadInterface;
				$texte = $this->view->render("charrette/".$this->nom_systeme."_resultat.phtml");

				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				$this->majEvenementsCharrette(Bral_Helper_Affiche::copie($this->view->texte));
				$this->majBraldun();
				return $this->view->render("commun/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	protected function constructListBoxRefresh($tab = null) {
		$tab[] = "box_profil";
		$tab[] = "box_evenements";
		$tab[] = "box_charrette";
		if ($this->view->user->pa_braldun < 1) {
			Zend_Loader::loadClass("Bral_Util_Box");
			Bral_Util_Box::calculBoxToRefresh0PA($tab);
		}
		return $tab;
	}

	protected function calculBalanceFaim() {
		$this->view->balanceFaimUtilisee = true;
		$this->view->balance_faim = -1;
		$this->view->user->balance_faim_braldun = $this->view->user->balance_faim_braldun + $this->view->balance_faim;
		Zend_Loader::loadClass("Bral_Util_Faim");
		Bral_Util_Faim::calculBalanceFaim($this->view->user);
	}

	private function majBraldun() {
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();

		$this->view->user->poids_transporte_braldun = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_braldun, $this->view->user->castars_braldun);
		$this->view->user->pa_braldun = $this->view->user->pa_braldun - $this->view->nb_pa ;

		$data = array(
			'pa_braldun' => $this->view->user->pa_braldun,
			'castars_braldun' => $this->view->user->castars_braldun,
			'poids_transporte_braldun' => $this->view->user->poids_transporte_braldun,
		);
		$where = "id_braldun=".$this->view->user->id_braldun;
		$braldunTable->update($data, $where);
	}

}