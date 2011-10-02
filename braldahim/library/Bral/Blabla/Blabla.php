<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Blabla_Blabla
{

	private $majEvenement = true;

	function __construct($nomSystemeAction, $request, $view, $action, $idTerrainDefaut = null)
	{
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeAction;
		$this->idTerrainDefaut = $idTerrainDefaut;
		$this->estEvenementAuto = true;

		$this->prepareCommun();

		switch ($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
			default:
				throw new Zend_Exception(get_class($this) . "::action invalide :" . $this->action);
		}
	}

	abstract function prepareCommun();

	abstract function prepareFormulaire();

	abstract function prepareResultat();

	abstract function getListBoxRefresh();

	abstract function getNomInterne();

	abstract function getTitreAction();

	abstract function calculNbPa();

	protected function setEstEvenementAuto($flag)
	{
		$this->estEvenementAuto = $flag;
	}

	protected function constructListBoxRefresh($tab = null)
	{
		return $tab;
	}

	protected function setDetailsEvenement($details, $idType)
	{
		$this->detailEvenement = $details;
		$this->idTypeEvenement = $idType;
	}

	public function getIdEchoppeCourante()
	{
		return false;
	}

	public function getIdChampCourant()
	{
		return false;
	}

	public function getTablesHtmlTri()
	{
		return false;
	}

	protected function majBraldun()
	{
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();

		$data = array(
			'nb_blabla_braldun' => $this->view->user->nb_blabla_braldun,
			'nb_tour_blabla_braldun' => $this->view->user->nb_tour_blabla_braldun,
		);
		$where = "id_braldun=" . $this->view->user->id_braldun;
		$braldunTable->update($data, $where);
	}

	function render()
	{
		$this->view->nomAction = $this->getTitreAction();
		$this->view->nomSysteme = $this->nom_systeme;
		switch ($this->action) {
			case "ask":
				$texte = $this->view->render("blabla/" . $this->nom_systeme . "_formulaire.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				return $this->view->render("competences/commun_formulaire.phtml");
				break;
			case "do":
				$texte = $this->view->render("blabla/" . $this->nom_systeme . "_resultat.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				return $this->view->render("competences/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this) . "::action invalide :" . $this->action);
		}
	}
}