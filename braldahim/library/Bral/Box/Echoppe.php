<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Echoppe extends Bral_Box_Box
{

	function getTitreOnglet()
	{
		return "&Eacute;choppe";
	}

	function getNomInterne()
	{
		return "box_lieu";
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		Zend_Loader::loadClass("Echoppe");

		$echoppesTable = new Echoppe();
		$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		if (count($echoppeRowset) > 1) {
			throw new Zend_Exception(get_class($this) . "::nombre d'echoppe invalide > 1 !");
		} else if (count($echoppeRowset) == 0) {
			throw new Zend_Exception(get_class($this) . "::nombre d'echoppe invalide = 0 !");
		}

		$echoppe = $echoppeRowset[0];
		$this->view->estLieuCourant = true;

		$nom = "Échoppe";
		if ($echoppe["nom_masculin_metier"]{0} == "A") {
			$nom .= " d'";
		} else {
			$nom .= " de ";
		}
		if ($echoppe["sexe_braldun"] == "masculin") {
			$nom .= $echoppe["nom_masculin_metier"];
		} else {
			$nom .= $echoppe["nom_feminin_metier"];
		}
		$nom = htmlspecialchars($nom) . "<br />";
		$nom .= " appartenant &agrave " . htmlspecialchars($echoppe["prenom_braldun"]);
		$nom .= " " . htmlspecialchars($echoppe["nom_braldun"]);
		$nom .= " n°" . $echoppe["id_braldun"];

		Zend_Loader::loadClass("Bral_Util_Lot");
		$this->view->lots = Bral_Util_Lot::getLotsByIdEchoppe($echoppe["id_echoppe"], true, $this->view->user->id_braldun);

		$this->view->echoppe = $echoppe;

		$this->view->nomEchoppe = $nom;

		$this->view->estElementsEtal = true;
		$this->view->estElementsEtalAchat = true;
		$this->view->estElementsAchat = false;

		return $this->view->render("interface/echoppe.phtml");
	}

}
