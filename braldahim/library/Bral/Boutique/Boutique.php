<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Boutique_Boutique
{

	protected $reloadInterface = false;

	function __construct($nomSystemeAction, $request, $view, $action)
	{
		Zend_Loader::loadClass("Bral_Util_Evenement");

		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeAction;
		$this->view->nom_systeme = $this->nom_systeme;

		Zend_Loader::loadClass("Lieu");

		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		unset($lieuxTable);

		Zend_Loader::loadClass("Region");

		$regionTable = new Region();
		$this->idRegion = $regionTable->findIdRegionByCase($this->view->user->x_braldun, $this->view->user->y_braldun);
		unset($regionTable);

		if (count($lieuRowset) <= 0) {
			throw new Zend_Exception("Bral_Box_Boutique::nombre de lieux invalide <= 0 !");
		} elseif (count($lieuRowset) > 1) {
			throw new Zend_Exception("Bral_Box_Boutique::nombre de lieux invalide > 1 !");
		} elseif (count($lieuRowset) == 1) {
			$lieu = $lieuRowset[0];
			unset($lieuRowset);
			$this->view->idBoutique = $lieu["id_lieu"];
			$this->view->nomLieu = $lieu["nom_lieu"];
			$this->paUtilisationBoutique = $lieu["pa_utilisation_type_lieu"];
		}

		$this->calculNbPa();
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

	protected function constructListBoxRefresh($tab = null)
	{
		$tab[] = "box_profil";
		$tab[] = "box_evenements";
		$tab[] = "box_cockpit";
		if ($this->view->user->pa_braldun < 1) {
			Zend_Loader::loadClass("Bral_Util_Box");
			Bral_Util_Box::calculBoxToRefresh0PA($tab);
		}
		return $tab;
	}

	function getIdEchoppeCourante()
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

	public function calculNbPa()
	{
		if ($this->view->user->pa_braldun - $this->paUtilisationBoutique < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
		$this->view->nb_pa = $this->paUtilisationBoutique;
	}

	/*
		  * Mise à jour des événements du Braldûn : type : compétence.
		  */
	private function majEvenementsBoutique($detailsBot)
	{
		$this->idTypeEvenement = $this->view->config->game->evenements->type->boutique;
		$this->detailEvenement = "[b" . $this->view->user->id_braldun . "] a utilisé les services d'une boutique";
		Bral_Util_Evenement::majEvenements($this->view->user->id_braldun, $this->idTypeEvenement, $this->detailEvenement, $detailsBot, $this->view->user->niveau_braldun);
	}

	function render()
	{
		$this->view->titreAction = $this->getTitreAction();
		switch ($this->action) {
			case "ask":
				return $this->view->render("boutique/" . $this->nom_systeme . "_formulaire.phtml");
				break;
			case "do":
				$this->view->reloadInterface = $this->reloadInterface;
				$texte = $this->view->render("boutique/" . $this->nom_systeme . "_resultat.phtml");

				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				$this->majEvenementsBoutique(Bral_Helper_Affiche::copie($this->view->texte));
				$this->majBraldun();
				return $this->view->render("commun/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this) . "::action invalide :" . $this->action);
		}
	}

	private function majBraldun()
	{
		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();

		$this->view->user->poids_transporte_braldun = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_braldun, $this->view->user->castars_braldun);
		$this->view->user->pa_braldun = $this->view->user->pa_braldun - $this->view->nb_pa;

		$data = array(
			'pa_braldun' => $this->view->user->pa_braldun,
			'castars_braldun' => $this->view->user->castars_braldun,
			'poids_transporte_braldun' => $this->view->user->poids_transporte_braldun,
		);
		$where = "id_braldun=" . $this->view->user->id_braldun;
		$braldunTable->update($data, $where);
	}

}