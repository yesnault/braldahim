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
class Bral_Competences_Concocter extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");

		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);

		$this->view->concocterEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->concocterEchoppeOk = false;
			return;
		}
		$idEchoppe = -1;

		$this->view->nbPlantesMax = $this->view->user->agilite_base_hobbit;
		if ($this->view->nbPlantesMax < 1) {
			$this->view->nbPlantesMax = 1;
		}

		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
			$e["nom_systeme_metier"] == "apothicaire" &&
			$e["x_echoppe"] == $this->view->user->x_hobbit &&
			$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->concocterEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];
				break;
			}
		}

		if ($this->view->concocterEchoppeOk == false) {
			return;
		}

		Zend_Loader::loadClass("EchoppePartieplante");
		$tabPartiePlantes = null;
		$echoppePlanteTable = new EchoppePartieplante();
		$partiesPlantes = $echoppePlanteTable->findByIdEchoppe($idEchoppe);

		$this->view->nbArrierePlante = 0;
		$this->view->concocterPlanteOk = false;

		$i = 0;
		if ($partiesPlantes != null) {
			foreach ($partiesPlantes as $m) {
				if ($m["quantite_arriere_echoppe_partieplante"] >= 1) {
					$i++;
					$tabPartiePlantes[] = array(
						"indicateur" => $i,
						"id_type" => $m["id_fk_type_echoppe_partieplante"],
						"id_type_plante" => $m["id_fk_type_plante_echoppe_partieplante"],
						"nom_type_partieplante" => $m["nom_type_partieplante"],
						"nom_type" => $m["nom_type_plante"],
						"quantite_arriere" => $m["quantite_arriere_echoppe_partieplante"],
						"quantite_preparees" => $m["quantite_preparees_echoppe_partieplante"],
					);
					$this->view->concocterPlanteOk = true;
					$this->view->nbArrierePlante = $this->view->nbArrierePlante + $m["quantite_arriere_echoppe_partieplante"];
				}
			}
		}
		if ($this->view->nbPlantesMax > $this->view->nbPlantesMax) {
			$this->view->nbPlantesMax = $this->view->nbArrierePlante;
		}

		if ($this->view->nbPlantesMax < 1) {
			$this->view->concocterPlanteOk = false;
		}

		$this->view->partiesPlantes = $tabPartiePlantes;

		$this->idEchoppe = $idEchoppe;
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

		// Verification concocter
		if ($this->view->concocterEchoppeOk == false || $this->view->concocterPlanteOk == false) {
			throw new Zend_Exception(get_class($this)." Concocter interdit ");
		}

		$indicateur = $this->request->get("valeur_1");
		if ($indicateur == null ) {
			throw new Zend_Exception(get_class($this)." Plante inconnue ");
		}

		if ((int)$this->request->get("valeur_2")."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." Nombre invalide");
		} else {
			$nombre = (int)$this->request->get("valeur_2");
		}

		if ($nombre < 0 || $nombre > $this->view->nbPlantesMax) {
			throw new Zend_Exception(get_class($this)." Nombre invalide b");
		}

		$planteOk = false;;
		foreach($this->view->partiesPlantes as $t) {
			if ($t["indicateur"] == $indicateur) {
				$planteOk = true;
				$this->view->planteNomType = $t["nom_type"];
				$this->view->planteNomTypePartiePlante = $t["nom_type_partieplante"];
				$idTypePartiePlante = $t["id_type"];
				$idTypePlante = $t["id_type_plante"];
				break;
			}
		}
		if ($planteOk == false) {
			throw new Zend_Exception(get_class($this)." Plante invalide");
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculConcocter($idTypePartiePlante, $idTypePlante, $nombre);
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculConcocter($idTypePartiePlante, $idTypePlante, $nb) {

		$this->view->nbPartiesPlantesPreparees = 0;
		
		for($j = 1; $j <= $nb; $j++) {
			$tirage = 0;
			for ($i=1; $i <= ($this->view->config->game->base_agilite + $this->view->user->agilite_base_hobbit) ; $i++) {
				$tirage = $tirage + Bral_Util_De::get_1d6();
			}
			$tirage = $tirage + $this->view->user->agilite_bm_hobbit + $this->view->user->agilite_bbdf_hobbit;

			$tirage2 = 0;
			for ($i=1; $i <= ($this->view->config->game->base_agilite + $this->view->user->agilite_base_hobbit) ; $i++) {
				$tirage2 = $tirage2 + Bral_Util_De::get_1d6();
			}

			if ($tirage > $tirage2) {
				$this->view->nbPartiesPlantesPreparees = $this->view->nbPartiesPlantesPreparees + 1;
			}
		}

		$echoppePlanteTable = new EchoppePartieplante();
		$data = array(
			'id_fk_type_echoppe_partieplante' => $idTypePartiePlante,
			'id_fk_type_plante_echoppe_partieplante' => $idTypePlante,
			'id_fk_echoppe_echoppe_partieplante' => $this->idEchoppe,
			'quantite_preparees_echoppe_partieplante' => $this->view->nbPartiesPlantesPreparees,
			'quantite_arriere_echoppe_partieplante' => -$nb,
		);
		$echoppePlanteTable->insertOrUpdate($data);
		
		$this->view->nbPlantesUtilisees = $nb;
	}

	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_laban", "box_echoppes"));
	}
}
