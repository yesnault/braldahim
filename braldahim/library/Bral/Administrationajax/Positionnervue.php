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
class Bral_Administrationajax_Positionnervue extends Bral_Administrationajax_Administrationajax {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Admin : Se positionner sur la vue";
	}

	function prepareCommun() {
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		
		$idVille = $this->request->get("id_ville");
		$idLieu = $this->request->get("id_lieu");
		$xyPosition = $this->request->get("xy_position");
		
		$x = $this->view->user->x_hobbit;
		$y = $this->view->user->y_hobbit;
		
		if ($idVille != null) {
			Bral_Util_Controle::getValeurIntVerif($idVille);
			Zend_Loader::loadClass("Ville");
			$villeTable = new Ville();
			$ville = $villeTable->findById($idVille);
			$x = $ville->x_min_ville + floor(($ville->x_max_ville - $ville->x_min_ville) / 2);
			$y = $ville->y_min_ville + floor(($ville->y_max_ville - $ville->y_min_ville) / 2);
				
		} elseif ($idLieu != null) {
			Bral_Util_Controle::getValeurIntVerif($idLieu);
			Zend_Loader::loadClass("Lieu");
			$lieuTable = new Lieu();
			$lieu = $lieuTable->findById($idLieu);
			$x = $lieu->x_lieu;
			$y = $lieu->y_lieu;
		} elseif ($xyPosition != null) {
			list ($x, $y) = split("h", $xyPosition);
			Bral_Util_Controle::getValeurIntVerif($x);
			Bral_Util_Controle::getValeurIntVerif($y);
		}
		
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["x_position"] = $x;
		Zend_Auth::getInstance()->getIdentity()->administrationvueDonnees["y_position"] = $y;
	}

	function getListBoxRefresh() {
		return array("box_vue");
	}
}