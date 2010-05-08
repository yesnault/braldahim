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
class Bral_Soule_Desinscription extends Bral_Soule_Soule {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Désinscription au match de soule";
	}

	function prepareCommun() {
		Zend_Loader::loadClass('SouleMatch');
		Zend_Loader::loadClass('SouleTerrain');
		Zend_Loader::loadClass('Bral_Util_Soule');

		$this->view->deinscriptionPossible = false;
		$this->matchDesinscription = null;

		$this->calculNbPa();
		$this->calculNbCastars();

		if ($this->view->assezDePa && $this->view->user->est_engage_braldun == "non") {
			$match = Bral_Util_Soule::desincriptionPrepareTerrain($this->view->user->id_braldun);
			$this->view->match = $match;
			if ($this->view->match != null) {
				$this->view->desinscriptionPossible = true;
			}
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {

		if ($this->view->desinscriptionPossible !== true) {
			throw new Zend_Exception(get_class($this)." Erreur deinscriptionPossible == false");
		}

		if ($this->view->match == null) {
			throw new Zend_Exception(get_class($this)."match invalide");
		}

		Bral_Util_Soule::calculDesinscriptionBd($this->view->match["id_soule_match"], $this->view->user->id_braldun);

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->nb_castars;
		if ($this->view->user->castars_braldun < 0) {
			$this->view->user->castars_braldun = 0;
		}

		$details = "[b".$this->view->user->id_braldun."] s'est désinscrit du match sur le ".$this->view->match["nom_soule_terrain"];
		$idType = $this->view->config->game->evenements->type->soule;
		$this->setDetailsEvenement($details, $idType);

		$this->majBraldun();
	}

	public function calculNbPa() {
		$this->view->nb_pa = 2;
		if ($this->view->user->pa_braldun - $this->view->nb_pa < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
	}

	public function calculNbCastars() {
		$this->view->nb_castars = 10;
		if ($this->view->user->castars_braldun - $this->view->nb_castars < 0) {
			$this->view->assezDeCastars = false;
		} else {
			$this->view->assezDeCastars = true;
		}
	}

	function getListBoxRefresh() {
		$tab = array("box_soule", "box_laban");
		return $this->constructListBoxRefresh($tab);
	}
}