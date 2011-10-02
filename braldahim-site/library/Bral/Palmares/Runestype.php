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
class Bral_Palmares_Runestype extends Bral_Palmares_Box
{

	function getTitreOnglet()
	{
		return "Type";
	}

	function getNomInterne()
	{
		return "box_onglet_runestype";
	}

	function getNomClasse()
	{
		return "runestype";
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->view->afficheMoisEnCours = false;
		$this->prepare();
		return $this->view->render("palmares/runes_type.phtml");
	}

	private function prepare()
	{
		Zend_Loader::loadClass("StatsRunes");
		if ($this->view->filtre == 1) {
			throw new Zend_Exception('filtre par mois courant interdit, type rune');
		}
		$mdate = $this->getTabDateFiltre(1);
		$statsRunesTable = new StatsRunes();
		$rowset = $statsRunesTable->findByType($mdate["dateDebut"], $mdate["dateFin"]);
		$this->view->types = $rowset;
	}
}