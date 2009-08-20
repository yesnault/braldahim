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
class Bral_Competences_Decouper extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");

		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);

		$this->view->decouperEchoppeOk = false;
		$this->view->decouperPlancheOk = false;

		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->decouperEchoppeOk = false;
			return;
		}
		$idEchoppe = -1;

		$this->view->nbRondinsMax = $this->view->user->sagesse_base_hobbit;
		if ($this->view->nbRondinsMax < 1) {
			$this->view->nbRondinsMax = 1;
		}

		$this->view->nbArriereRondin = 0;

		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
			$e["nom_systeme_metier"] == "menuisier" &&
			$e["x_echoppe"] == $this->view->user->x_hobbit &&
			$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->decouperEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];

				$echoppeCourante = array(
					'id_echoppe' => $e["id_echoppe"],
					'x_echoppe' => $e["x_echoppe"],
					'y_echoppe' => $e["y_echoppe"],
					'id_metier' => $e["id_metier"],
					'quantite_rondin_arriere_echoppe' => $e["quantite_rondin_arriere_echoppe"],
				);
				if ($e["quantite_rondin_arriere_echoppe"] >= 1) {
					$this->view->decouperPlancheOk = true;
					$this->view->nbArriereRondin = $this->view->nbArriereRondin + $e["quantite_rondin_arriere_echoppe"];
				}
				break;
			}
		}

		if ($this->view->decouperEchoppeOk == false) {
			return;
		}

		if ($this->view->nbRondinsMax > $this->view->nbArriereRondin) {
			$this->view->nbRondinsMax = $this->view->nbArriereRondin;
		}

		if ($this->view->nbRondinsMax < 1) {
			$this->view->decouperPlancheOk = false;
		}

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

		if ($this->view->decouperEchoppeOk == false || $this->view->decouperPlancheOk == false) {
			throw new Zend_Exception(get_class($this)." decouper interdit ");
		}

		if ((int)$this->request->get("valeur_1")."" != $this->request->get("valeur_1")."") {
			throw new Zend_Exception(get_class($this)." Nombre invalide");
		} else {
			$nombre = (int)$this->request->get("valeur_1");
		}

		if ($nombre < 0 || $nombre > $this->view->nbRondinsMax) {
			throw new Zend_Exception(get_class($this)." Nombre invalide b");
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculDecouper($nombre);
		}

		$this->calculPx();
		$this->calculPoids();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	/*Découpe un rondin présent dans l'échoppe en planches.
	 */
	private function calculDecouper($nb) {

		$this->view->quantitePlanches = 0;

		for($j = 1; $j <= $nb; $j++) {
			$tirage = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_hobbit);
			$tirage = $tirage + $this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit;

			$tirage2 = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_hobbit);

			if ($tirage > $tirage2) {
				$this->view->quantitePlanches = $this->view->quantitePlanches + 1;
			}
		}

		$echoppeTable = new Echoppe();
		$data = array(
				'id_echoppe' => $this->idEchoppe,
				'quantite_rondin_arriere_echoppe' => -$nb,
				'quantite_planche_arriere_echoppe' => $this->view->quantitePlanches,
		);
		$echoppeTable->insertOrUpdate($data);

		$this->view->quantiteRondinsUtilisee = $nb;
	}

	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_echoppes"));
	}
}
