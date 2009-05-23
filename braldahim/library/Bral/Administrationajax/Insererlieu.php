<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Administrationajax_Insererlieu extends Bral_Administrationajax_Administrationajax {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Admin : insérer un lieu";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Ville");

		$xyLieu = $this->request->get("xy_lieu");
		if ($xyLieu != null) {
			$xyLieu = $this->request->get("xy_lieu");
			list ($xLieu, $yLieu) = split("h", $xyLieu);
			Bral_Util_Controle::getValeurIntVerif($xLieu);
			Bral_Util_Controle::getValeurIntVerif($yLieu);
		}

		$typeLieuTable = new TypeLieu();
		$typesLieux = $typeLieuTable->fetchAll(null, "id_type_lieu");

		$tabTypesLieux = null;
		foreach($typesLieux as $t) {
			$tabTypesLieux[$t["id_type_lieu"]]["type"] = $t;
			$tabTypesLieux[$t["id_type_lieu"]]["selected"] = "";
		}

		$tabTypesLieux[count($tabTypesLieux)]["selected"] = "selected";

		$villeTable = new Ville();
		$villesRowset = $villeTable->fetchAll();

		$tabVilles = null;
		$villeEnCours = "";

		foreach($villesRowset as $v) {
			$tabVilles[$v["id_ville"]]["ville"] = $v;
			$tabVilles[$v["id_ville"]]["selected"] = "";
			$tabVilles[$v["id_ville"]]["info"] = "";
			if ($xyLieu != null) {
				if ($xLieu >= $v["x_min_ville"] && $xLieu <= $v["x_max_ville"] &&
				$yLieu >= $v["y_min_ville"] && $yLieu <= $v["y_max_ville"]) {
					$tabVilles[$v["id_ville"]]["selected"] = "selected";
					$villeEnCours = " de ".$v["nom_ville"];
				} else {
					$tabVilles[$v["id_ville"]]["info"] = "x:".$xLieu." y:".$yLieu. " en dehors de cette ville";
				}
			}
		}

		$tabEstSoule = array("non" => "non", "oui" => "oui");

		$this->view->villeEnCours = $tabTypesLieux[count($tabTypesLieux)]["type"]["nom_type_lieu"].$villeEnCours;
		if ($xyLieu != null) {
			$this->view->xLieu = $xLieu;
			$this->view->yLieu = $yLieu;
		}
		$this->view->villes = $tabVilles;
		$this->view->typeLieux = $tabTypesLieux;
		$this->view->estSoule = $tabEstSoule;
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		$xLieu = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		$yLieu = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_2"));
		$idTypeLieu = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_3"));
		$nomLieu = $this->request->getPost("valeur_4");
		$descriptionLieu = $this->request->getPost("valeur_5");
		$idVille = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_6"));
		$estSoule = $this->request->getPost("valeur_7");

		$lieuTable = new Lieu();

		$data = array(
			"nom_lieu" => $nomLieu,
			"description_lieu" => $descriptionLieu,
			"id_fk_ville_lieu" => $idVille,
			"id_fk_type_lieu" => $idTypeLieu,
			"date_creation_lieu" => date("Y-m-d H:i:s"),
			"x_lieu" => $xLieu,
			"y_lieu" => $yLieu,
			"est_soule_lieu" => $estSoule,
		);
		$idLieu = $lieuTable->insert($data);
		$this->view->dataLieu = $data;
		$this->view->dataLieu["id_lieu"] = $idLieu;
	}

	function getListBoxRefresh() {
		return array("box_lieu", "box_vue");
	}
}