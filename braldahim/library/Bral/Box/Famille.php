<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Famille extends Bral_Box_Box
{

	function getTitreOnglet()
	{
		return "Famille";
	}

	function getNomInterne()
	{
		return "box_famille";
	}

	function getChargementInBoxes()
	{
		return false;
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		if ($this->view->affichageInterne) {
			$this->data();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/famille.phtml");
	}

	private function data()
	{
		Zend_Loader::loadClass('AncienBraldun');
		$braldunTable = new Braldun();
		$ancienBraldunTable = new AncienBraldun();

		$this->view->pereMereOk = false;
		$pere = null;
		$mere = null;

		$this->view->mereAncienne = false;
		$this->view->pereAncien = false;

		if ($this->view->user->id_fk_mere_braldun != null && $this->view->user->id_fk_pere_braldun != null &&
			$this->view->user->id_fk_mere_braldun != 0 && $this->view->user->id_fk_pere_braldun != 0
		) {

			$pere = $braldunTable->findById($this->view->user->id_fk_pere_braldun);
			$mere = $braldunTable->findById($this->view->user->id_fk_mere_braldun);

			if ($pere == null) {
				$this->view->pereAncien = true;
				$pere = $ancienBraldunTable->findById($this->view->user->id_fk_pere_braldun);
			}

			if ($mere == null) {
				$this->view->mereAncienne = true;
				$mere = $ancienBraldunTable->findById($this->view->user->id_fk_mere_braldun);
			}

			$this->view->pereMereOk = true;
		}

		$this->view->pere = $pere;
		$this->view->mere = $mere;

		// on regarde s'il y a des enfants
		$enfants = null;
		$enfantsRowset = $braldunTable->findEnfants($this->view->user->sexe_braldun, $this->view->user->id_braldun);
		unset($braldunTable);
		$this->view->nbEnfants = count($enfantsRowset);

		if (count($this->view->nbEnfants) > 0) {
			foreach ($enfantsRowset as $e) {
				$enfants[] = array("prenom" => $e["prenom_braldun"],
					"nom" => $e["nom_braldun"],
					"id_braldun" => $e["id_braldun"],
					"sexe_braldun" => $e["sexe_braldun"],
					"date_naissance" => $e["date_creation_braldun"]);
			}
			unset($enfantsRowset);
		}
		$this->view->enfants = $enfants;

		// on va chercher les informations du conjoint
		Zend_Loader::loadClass("Bral_Util_Conjoints");
		$this->view->conjoint = Bral_Util_Conjoints::getConjoint($this->view->user->sexe_braldun, $this->view->user->id_braldun);

		$this->view->dateNaissance = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y \&\a\g\r\a\v\e; H:i:s', $this->view->user->date_creation_braldun);
		$this->view->nom_interne = $this->getNomInterne();
	}
}
