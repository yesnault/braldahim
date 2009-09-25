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

/*
 * La réparation de la charrette peut être effectuée chez le Menuisier. Le cout varie en fonction du ratio Durabilité actuelle/Durabilité Maximum :
 Ratio à calculer : Dmax/100-Usure+Capacité
 Planches
 Da/Dm  <25%	Ratio/5	Tout en arrondi classique
 Da/Dm 26-50 %	Ratio/10
 Da/Dm 51-75%	Ratio/15
 Da/Dm >75%	Ratio/20
 */
class Bral_Competences_Reparermenuisier extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");

		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit);

		$this->view->reparermenuisierEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->reparermenuisierEchoppeOk = false;
			return;
		}

		$idEchoppe = -1;
		$metier = substr($this->nom_systeme, 7, strlen($this->nom_systeme) - 7);
		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
			$e["nom_systeme_metier"] == $metier &&
			$e["x_echoppe"] == $this->view->user->x_hobbit &&
			$e["y_echoppe"] == $this->view->user->y_hobbit && 
			$e["z_echoppe"] == $this->view->user->z_hobbit) {
				$this->view->reparermenuisierEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];

				$echoppeCourante = array(
					'id_echoppe' => $e["id_echoppe"],
					'x_echoppe' => $e["x_echoppe"],
					'y_echoppe' => $e["y_echoppe"],
					'z_echoppe' => $e["z_echoppe"],
					'id_metier' => $e["id_metier"],
					'quantite_planche_arriere_echoppe' => $e["quantite_planche_arriere_echoppe"],
				);
				break;
			}

		}
		if ($this->view->reparermenuisierEchoppeOk == false) {
			return;
		}

		$this->echoppeCourante = $echoppeCourante;

		$tabCharrettes = $this->prepareCharrettes();

		$this->view->charrettes = $tabCharrettes;
		$this->idEchoppe = $idEchoppe;

		$this->view->nom_systeme = $this->nom_systeme;
	}

	// Récupération des charrettes portées par les hobbits, sur la cases de l'échoppe.
	private function prepareCharrettes() {
		$tabCharrettes = null;

		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();
		$charrettesRowset = $charretteTable->findByPositionAvecHobbit($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit);

		if ($charrettesRowset != null && count($charrettesRowset) > 0) {

			foreach($charrettesRowset as $c) {
				$cout = $this->calculCoutPlanche($c);

				$possiblePlanche = false;
				if ($this->echoppeCourante["quantite_planche_arriere_echoppe"] >= $cout) {
					$possiblePlanche = true;
				}

				$possibleCout = false;
				if ($cout > 0) {
					$possibleCout = true;
				}

				$possible = false;
				if ($possibleCout == true && $possiblePlanche == true) {
					$possible = true;
				}

				$tabCharrettes[] = array(
					"id_charrette" => $c["id_charrette"],
					"nom_type_materiel" => $c["nom_type_materiel"],
					"id_hobbit" => $c["id_hobbit"],
					"nom_hobbit" => $c["nom_hobbit"],
					"prenom_hobbit" => $c["prenom_hobbit"],
					"durabilite_max_charrette" => $c["durabilite_max_charrette"],
					"durabilite_actuelle_charrette" => $c["durabilite_actuelle_charrette"],
					"poids_transportable_charrette" => $c["poids_transportable_charrette"],
					"cout_reparation_planche" => $cout,
					"possible_planche" => $possiblePlanche,
					"possible_cout" => $possibleCout,
					"possible" => $possible,
				);
			}
		}

		return $tabCharrettes;
	}

	private function calculCoutPlanche($charrette) {

		Zend_Loader::loadClass("CharretteMaterielAssemble");
		$charretteMaterielAssembleTable = new CharretteMaterielAssemble();
		$materielsAssembles = $charretteMaterielAssembleTable->findByIdCharrette($charrette["id_charrette"]);

		$usureJournaliere = $charrette["usure_type_materiel"];

		if ($materielsAssembles != null && count($materielsAssembles) > 0) {
			foreach($materielsAssembles as $m) {
				$usureJournaliere = $usureJournaliere - $m["usure_type_materiel"];
			}
		}
			
		$ratio = ($charrette["durabilite_max_charrette"] / 100) + $usureJournaliere + $charrette["poids_transportable_charrette"];

		$coef =  ($charrette["durabilite_actuelle_charrette"] * 100) / $charrette["durabilite_max_charrette"];
		$retour = 0;
		if ($coef >= 100){
			$retour = 0;
		} else if ($coef <= 25 ) {
			$retour = 	round($ratio / 5);
		} else if ($coef <= 50) {
			$retour = 	round($ratio / 10);
		} else if ($coef <= 75) {
			$retour = round($ratio / 15);
		} else {
			$retour = round($ratio / 20);
		}
		return $retour;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification reparer
		if ($this->view->reparermenuisierEchoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Reparer Echoppe interdit ");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Charrette invalide : ".$this->request->get("valeur_1"));
		} else {
			$idCharrette = (int)$this->request->get("valeur_1");
		}

		$charrette = null;
		foreach($this->view->charrettes as $c) {
			if ($c["id_charrette"] == $idCharrette) {
				if ($c["possible"] == true) {
					$charrette = $c;
				}
				break;
			}
		}

		if ($charrette == null) {
			throw new Zend_Exception(get_class($this)." idCharrette interdit A=".$idCharrette. " idh=".$this->view->user->id_hobbit);
		}

		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculReparer($charrette);
			$id_type = $this->view->config->game->evenements->type->competence;
			$details = "[h".$this->view->user->id_hobbit."] a réparé un matériel";
			$this->setDetailsEvenement($details, $id_type);
		}
		$this->setEvenementQueSurOkJet1(false);

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();

		$this->view->charrette = $charrette;
	}

	private function calculReparer($charrette) {
		$charretteTable = new Charrette();
		$data = array("durabilite_actuelle_charrette" => $charrette["durabilite_max_charrette"]);
		$where = "id_charrette = ".$charrette["id_charrette"];
		$charretteTable->update($data, $where);

		$echoppeTable = new Echoppe();
		$data = array(
			"quantite_planche_arriere_echoppe" => -$charrette["cout_reparation_planche"],
			"id_echoppe" => $this->idEchoppe,
		);
		$echoppeTable->insertOrUpdate($data);
	}

	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_echoppes", "box_charrette"));
	}
}
