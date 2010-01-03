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
abstract class Bral_Hotel_Hotel {

	protected $reloadInterface = false;

	function __construct($nomSystemeAction, $request, $view, $action) {
		Zend_Loader::loadClass("Bral_Util_Evenement");

		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeAction;
		$this->view->nom_systeme = $this->nom_systeme;

		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("TypeLieu");
		$lieuxTable = new Lieu();
		$lieuRowset = $lieuxTable->findByTypeAndCase(TypeLieu::ID_TYPE_HOTEL, $this->view->user->x_hobbit, $this->view->user->y_hobbit);
		unset($lieuxTable);

		if (count($lieuRowset) <= 0) {
			//throw new Zend_Exception("Bral_Box_Hotel::nombre de lieux invalide <= 0 !");
			// on verifie que l'on n'est pas dans une échoppe
			Zend_Loader::loadClass("Echoppe");
			$echoppesTable = new Echoppe();
			$echoppeTable = new Echoppe();
			$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);
			$tabEchoppe = null;
			$this->idEchoppe = null;
			$this->view->estSurEchoppe = false;
			foreach ($echoppes as $e) {
				if ($e["x_echoppe"] == $this->view->user->x_hobbit &&
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
					$tabEchoppe = array('id_echoppe' => $e["id_echoppe"]);
					$this->view->idHotel = null;
					$this->idEchoppe = $e["id_echoppe"];
					$this->view->nomLieu = 'Echoppe';
					$this->view->paUtilisationHotel = 1;
					$this->view->estSurEchoppe = true;
					break;
				}
			}
			if ($tabEchoppe == null) {
				throw new Zend_Exception(get_class($this)." Echoppe ou hotel invalide idh:".$this->view->user->id_hobbit);
			}
		} elseif (count($lieuRowset) > 1) {
			throw new Zend_Exception("Bral_Box_Hotel::nombre de lieux invalide > 1 !");
		} elseif (count($lieuRowset) == 1) {
			$lieu = $lieuRowset[0];
			unset($lieuRowset);
			$this->view->idHotel = $lieu["id_lieu"];
			$this->view->nomLieu = $lieu["nom_lieu"];
			$this->view->paUtilisationHotel = $lieu["pa_utilisation_type_lieu"];
		}

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

	abstract function prepareCommun();
	abstract function prepareFormulaire();
	abstract function prepareResultat();
	abstract function getListBoxRefresh();
	abstract function getNomInterne();
	abstract function getTitreAction();

	function getIdEchoppeCourante() {
		return false;
	}

	public function getIdChampCourant() {
		return false;
	}

	public function calculNbPa() {
		if ($this->view->user->pa_hobbit - $this->view->paUtilisationHotel < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
		$this->view->nb_pa = $this->view->paUtilisationHotel;
	}

	/*
	 * Mise à jour des événements du hobbit : type : service.
	 */
	private function majEvenementsHotel($detailsBot) {
		$this->idTypeEvenement = $this->view->config->game->evenements->type->service;
		$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a utilisé les services de l'Hôtel des Ventes";
		Bral_Util_Evenement::majEvenements($this->view->user->id_hobbit, $this->idTypeEvenement, $this->detailEvenement, $detailsBot, $this->view->user->niveau_hobbit);
	}

	function render() {
		$this->view->titreAction = $this->getTitreAction();
		switch($this->action) {
			case "ask":
				return $this->view->render("hotel/".$this->nom_systeme."_formulaire.phtml");
				break;
			case "do":
				$this->view->reloadInterface = $this->reloadInterface;
				$texte = $this->view->render("hotel/".$this->nom_systeme."_resultat.phtml");

				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				$this->majEvenementsHotel(Bral_Helper_Affiche::copie($this->view->texte));
				$this->majHobbit();
				return $this->view->render("commun/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	protected function constructListBoxRefresh($tab = null) {
		$tab[] = "box_profil";
		$tab[] = "box_evenements";
		if ($this->view->user->pa_hobbit < 1) {
			Zend_Loader::loadClass("Bral_Util_Box");
			Bral_Util_Box::calculBoxToRefresh0PA($tab);
		}
		return $tab;
	}

	private function majHobbit() {
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();

		$this->view->user->poids_transporte_hobbit = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_hobbit, $this->view->user->castars_hobbit);
		$this->view->user->pa_hobbit = $this->view->user->pa_hobbit - $this->view->nb_pa ;

		$data = array(
			'pa_hobbit' => $this->view->user->pa_hobbit,
			'castars_hobbit' => $this->view->user->castars_hobbit,
			'poids_transporte_hobbit' => $this->view->user->poids_transporte_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
	}

}