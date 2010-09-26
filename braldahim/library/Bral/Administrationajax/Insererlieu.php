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

		$xyzLieu = $this->request->get("xyz_lieu");
		if ($xyzLieu != null) {
			list ($xLieu, $yLieu, $zLieu) = preg_split("/h/", $xyzLieu);
			Bral_Util_Controle::getValeurIntVerif($xLieu);
			Bral_Util_Controle::getValeurIntVerif($yLieu);
			Bral_Util_Controle::getValeurIntVerif($zLieu);
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
			if ($xyzLieu != null) {
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
		if ($xyzLieu != null) {
			$this->view->xLieu = $xLieu;
			$this->view->yLieu = $yLieu;
			$this->view->zLieu = $zLieu;
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
		$zLieu = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_3"));
		$idTypeLieu = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_4"));
		$nomLieu = stripslashes($this->request->getPost("valeur_5"));
		$descriptionLieu = stripslashes($this->request->getPost("valeur_6"));
		$idVille = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_7"));
		$estSoule = $this->request->getPost("valeur_8");
		
		$debutDistinction = $this->request->getPost("valeur_9");

		$lieuTable = new Lieu();
		
		if ($idVille == -1) {
			$idVille = null;
		}

		$data = array(
			"nom_lieu" => $nomLieu,
			"description_lieu" => $descriptionLieu,
			"id_fk_ville_lieu" => $idVille,
			"id_fk_type_lieu" => $idTypeLieu,
			"date_creation_lieu" => date("Y-m-d H:i:s"),
			"x_lieu" => $xLieu,
			"y_lieu" => $yLieu,
			"z_lieu" => $zLieu,
			"est_soule_lieu" => $estSoule,
			"etat_lieu" => 100,
		);
		$idLieu = $lieuTable->insert($data);
		$this->view->dataLieu = $data;
		$this->view->dataLieu["id_lieu"] = $idLieu;
		
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("TypeCategorie");
		
		if ($idTypeLieu == TypeLieu::ID_TYPE_LIEUMYTHIQUE) {
			Zend_Loader::loadClass("TypeDistinction");
			$typeDistinctionTable = new TypeDistinction();
			$data = array(
				'nom_systeme_type_distinction' => 'mythique_'.$idLieu,
				'nom_type_distinction' => $debutDistinction.' '.$nomLieu,
				'id_fk_lieu_type_distinction' => $idLieu,
				'id_fk_type_categorie_distinction' => TypeCategorie::ID_TYPE_VOYAGEUR,
				'points_type_distinction' => 5,
			);
			$typeDistinctionTable->insert($data);
		}
	}

	function getListBoxRefresh() {
		return array("box_lieu", "box_vue");
	}
}