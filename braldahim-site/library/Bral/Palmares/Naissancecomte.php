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
class Bral_Palmares_Naissancecomte extends Bral_Palmares_Box
{

	function getTitreOnglet()
	{
		return "Comtés";
	}

	function getNomInterne()
	{
		return "box_onglet_naissancecomte";
	}

	function getNomClasse()
	{
		return "naissancecomte";
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
		return $this->view->render("palmares/naissance_comte.phtml");
	}

	private function prepare()
	{
		Zend_Loader::loadClass("Braldun");
		$mdate = $this->getTabDateFiltre();
		$braldunTable = new Braldun();
		$rowset = $braldunTable->findAllByDateCreationAndRegion($mdate["dateDebut"], $mdate["dateFin"]);
		$regions = null;
		$total = 0;
		foreach ($rowset as $r) {
			$regions[] = array("nom_region" => $r["nom_region"], "nombre" => $r["nombre"]);
			$total = $total + $r["nombre"];
		}
		$this->view->total = $total;
		$this->view->regions = $regions;
	}
}