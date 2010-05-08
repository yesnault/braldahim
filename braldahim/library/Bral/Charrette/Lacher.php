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
class Bral_Charrette_Lacher extends Bral_Charrette_Charrette {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Lâcher la charrette";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Bral_Util_Charrette");
		
		$tabCharrettes = null;
		$this->view->possedeCharrette = false;
		$this->view->possedeCaleFrein = false;

		$charretteTable = new Charrette();

		$charrette = $charretteTable->findByIdBraldun($this->view->user->id_braldun);
		if ($charrette != null && count($charrette) > 0) {
			foreach ($charrette as $c) {
				$this->view->idCharrette = $c["id_charrette"];
				$this->view->possedeCharrette = true;
				$this->view->possedeCaleFrein = Bral_Util_Charrette::possedeCaleFrein($c["id_charrette"]);
				break;
			}
		}
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		// Verification abattre arbre
		if ($this->view->possedeCharrette == false) {
			throw new Zend_Exception(get_class($this)." Possede aucune charrette ");
		}

		if ($this->view->possedeCaleFrein == false) {
			throw new Zend_Exception(get_class($this)." Possede cale-frein false charrette ");
		}

		$this->calculLacherCharrette();
		$this->calculBalanceFaim();

		$id_type = $this->view->config->game->evenements->type->deposer;
		$details = "[b".$this->view->user->id_braldun."] a lâché sa charrette";
		$this->setDetailsEvenement($details, $id_type);
		
		$details = "[b".$this->view->user->id_braldun."] a lâché la charrette n°".$this->view->idCharrette ;
		Zend_Loader::loadClass("Bral_Util_Materiel");
		Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_UTILISER_ID, $this->view->idCharrette , $details);
	}

	private function calculLacherCharrette() {
		$charretteTable = new Charrette();
		$dataUpdate = array(
			"id_fk_braldun_charrette" => null,
			"x_charrette" => $this->view->user->x_braldun,
			"y_charrette" => $this->view->user->y_braldun,
			"z_charrette" => $this->view->user->z_braldun,
		);
		$where = "id_fk_braldun_charrette = ".$this->view->user->id_braldun;
		$charretteTable->update($dataUpdate, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue"));
	}
}
