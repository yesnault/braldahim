<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Gare extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabDestinations = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
		$this->_coutCastars = $this->calculCoutCastars();
		$this->_utilisationPossible = (($this->view->user->castars_braldun -  $this->_coutCastars) >= 0);

		//	$this->prepareDestinationsBeta(); //Beta
		$this->prepareDestinations();

	}

	function prepareDestinations() {
		$lieuTable = new Lieu();
		$gareCourant = $lieuTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		$gareCourant = $gareCourant[0];

		Zend_Loader::loadClass("RouteNumero");
		$routeNumeroTable = new RouteNumero();
		$routes = $routeNumeroTable->findOuverteByIdLieu($gareCourant["id_lieu"], $gareCourant["est_capitale_ville"]);

		if (count($routes) > 0) {
			foreach($routes as $e) {
				$this->_tabDestinations[] = array("id_lieu" => $e["id_lieu"], "nom" => $e["nom_lieu"], "x" => $e["x_lieu"], "y" => $e["y_lieu"], "est_capitale" => ($e["est_capitale_ville"] == "oui")) ;
			}
		}
	}

	function prepareDestinationsBeta() {
		Zend_Loader::loadClass("TypeLieu");
		$lieuTable = new Lieu();
		$gareCourant = $lieuTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		$gareCourant = $gareCourant[0];
		$gareRowset = $lieuTable->findByType(TypeLieu::ID_TYPE_GARE);

		foreach($gareRowset as $e) {
			if ($e["x_lieu"] == $this->view->user->x_braldun && $e["y_lieu"] == $this->view->user->y_braldun) {
				// on ne propose pas le lieu ou le Braldûn est present
			} else {
				$est_capitale = ($e["est_capitale_ville"] == "oui");
				if ($gareCourant["est_capitale_ville"] == "oui") {
					// deplacement vers les ville de la Comté et vers les capitales
					if ($est_capitale === true) {
						$this->_tabDestinations[] = array("id_lieu" => $e["id_lieu"], "nom" => $e["nom_lieu"], "x" => $e["x_lieu"], "y" => $e["y_lieu"], "est_capitale" => $est_capitale) ;
					} else if ($gareCourant["id_fk_region_ville"] == $e["id_fk_region_ville"]) {
						$this->_tabDestinations[] = array("id_lieu" => $e["id_lieu"], "nom" => $e["nom_lieu"], "x" => $e["x_lieu"], "y" => $e["y_lieu"], "est_capitale" => $est_capitale) ;
					}
				} else {
					// deplacement uniquement vers la capitale
					if ($gareCourant["id_fk_region_ville"] == $e["id_fk_region_ville"] && $e["est_capitale_ville"] == "oui") {
						$this->_tabDestinations[] = array("id_lieu" => $e["id_lieu"], "nom" => $e["nom_lieu"], "x" => $e["x_lieu"], "y" => $e["y_lieu"], "est_capitale" => $est_capitale) ;
					}
				}
			}
		}
	}

	function prepareFormulaire() {
		$this->view->tabDestinations = $this->_tabDestinations;
		$this->view->utilisationPossible = $this->_utilisationPossible;
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {
		$idDestination = intval($this->request->get("valeur_1"));
		$xDestination = null;
		$yDestination = null;

		// verification qu'il y a assez de castars
		if ($this->_utilisationPossible == false) {
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_braldun." cout:".$this->_coutCastars);
		}

		// verification que la destination etait bien dans la liste des destinations proposees
		$destinationOk = false;
		foreach($this->_tabDestinations as $d) {
			if ($d["id_lieu"] == $idDestination) {
				$xDestination = $d["x"];
				$yDestination = $d["y"];
				$destinationOk = true;
				break;
			}
		}

		if ($destinationOk === false) {
			throw new Zend_Exception(get_class($this)."::Destination invalide:".$idDestination);
		}

		$braldunTable = new Braldun();
		$this->view->user->x_braldun = $xDestination;
		$this->view->user->y_braldun = $yDestination;
		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->_coutCastars;

		$this->majBraldun();
		Zend_Loader::loadClass("Bral_Util_Filature");
		Bral_Util_Filature::action($this->view->user, $this->view);
	}


	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban", "box_competences_metiers", "box_vue", "box_lieu", "box_blabla"));
	}

	private function calculCoutCastars() {
		return 50;
	}
}