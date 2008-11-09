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
class Bral_Lieux_Essenecehef extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabDestinations = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
		$this->_coutCastars = $this->calculCoutCastars();
		$this->_utilisationPossible = (($this->view->user->castars_hobbit -  $this->_coutCastars) >= 0);

		$lieuTable = new Lieu();
		$esseneCehefCourantt = $lieuTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$esseneCehefCourant = $esseneCehefCourantt[0];
		$esseneCehefRowset = $lieuTable->findByType($this->view->config->game->lieu->type->essene_cehef);

		foreach($esseneCehefRowset as $e) {
			if ($e["x_lieu"] == $this->view->user->x_hobbit && $e["y_lieu"] == $this->view->user->y_hobbit) {
				// on ne propose pas le lieu ou le hobbit est present
			} else {
				$est_capitale = ($e["est_capitale_ville"] == "oui");
				if ($esseneCehefCourant["est_capitale_ville"] == "oui") {
					// deplacement vers les ville de la comtée et vers les capitales
					if ($est_capitale === true) { 
						$this->_tabDestinations[] = array("id_lieu" => $e["id_lieu"], "nom" => $e["nom_lieu"], "x" => $e["x_lieu"], "y" => $e["y_lieu"], "est_capitale" => $est_capitale) ;
					} else if ($esseneCehefCourant["id_fk_region_ville"] == $e["id_fk_region_ville"]) {
						$this->_tabDestinations[] = array("id_lieu" => $e["id_lieu"], "nom" => $e["nom_lieu"], "x" => $e["x_lieu"], "y" => $e["y_lieu"], "est_capitale" => $est_capitale) ;
					}
				} else {
					// deplacement uniquement vers la capitale
					if ($esseneCehefCourant["id_fk_region_ville"] == $e["id_fk_region_ville"] && $e["est_capitale_ville"] == "oui") {
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
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_hobbit." cout:".$this->_coutCastars);
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

		$hobbitTable = new Hobbit();
		$this->view->user->x_hobbit = $xDestination;
		$this->view->user->y_hobbit = $yDestination;
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->_coutCastars;
		
		$this->majHobbit();
	}


	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_competences_metiers", "box_vue", "box_lieu");
	}

	private function calculCoutCastars() {
		return 50;
	}
}