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
class Bral_Palmares_Gredinstop10 extends Bral_Palmares_Box
{

	function getTitreOnglet()
	{
		return "Top 10";
	}

	function getNomInterne()
	{
		return "box_onglet_gredinstop10";
	}

	function getNomClasse()
	{
		return "gredinstop10";
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/gredins_top10.phtml");
	}

	private function prepare()
	{
		Zend_Loader::loadClass("StatsReputation");
		$mdate = $this->getTabDateFiltre();
		$statsReputationTable = new StatsReputation();
		$rowset = $statsReputationTable->findTop10($mdate["dateDebut"], $mdate["dateFin"], "gredin");
		$this->view->top10 = $rowset;
	}
}