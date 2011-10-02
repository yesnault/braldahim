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
class Bral_Palmares_Fabricantstop10 extends Bral_Palmares_Box
{

	function getTitreOnglet()
	{
		return "Top 10";
	}

	function getNomInterne()
	{
		return "box_onglet_fabricantstop10";
	}

	function getNomClasse()
	{
		return "fabricantstop10";
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
		return $this->view->render("palmares/fabricants_top10.phtml");
	}

	private function prepare()
	{
		if ($this->view->type == "bucheronsroutes") {
			Zend_Loader::loadClass("StatsRoutes");
			$this->view->titreColonne2 = $this->getSelectTypeFabricant($this->view->type);
			$mdate = $this->getTabDateFiltre();
			$statsRoutesTable = new StatsRoutes();
			$rowset = $statsRoutesTable->findTop10($mdate["dateDebut"], $mdate["dateFin"], $this->view->type);
			$this->view->top10 = $rowset;
		} else {
			Zend_Loader::loadClass("StatsFabricants");
			$this->view->titreColonne2 = $this->getSelectTypeFabricant($this->view->type);
			$mdate = $this->getTabDateFiltre();
			$statsFabricantsTable = new StatsFabricants();
			$rowset = $statsFabricantsTable->findTop10($mdate["dateDebut"], $mdate["dateFin"], $this->view->type);
			$this->view->top10 = $rowset;
		}

	}
}