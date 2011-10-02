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
class Bral_Palmares_Combattantspveniveau extends Bral_Palmares_Box
{

	function getTitreOnglet()
	{
		return "Niveaux";
	}

	function getNomInterne()
	{
		return "box_onglet_combattantspveniveau";
	}

	function getNomClasse()
	{
		return "combattantspveniveau";
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
		return $this->view->render("palmares/combattantspve_niveau.phtml");
	}

	private function prepare()
	{
		Zend_Loader::loadClass("Evenement");
		$mdate = $this->getTabDateFiltre();
		$evenementTable = new Evenement();
		$type = $this->view->config->game->evenements->type->killmonstre;
		$rowset = $evenementTable->findByNiveau($mdate["dateDebut"], $mdate["dateFin"], $type, true);
		$this->view->niveaux = $rowset;
	}
}