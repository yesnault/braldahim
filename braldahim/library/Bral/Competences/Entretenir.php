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
class Bral_Competences_Entretenir extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Champ");

		if ($this->verificationChamp() == false) {
			return null;
		}

		$this->verificationChamp();
		$this->preparePositions();
	}

	private function verificationChamp() {
		$this->view->entretenirChampOk = false;

		$champTable = new Champ();
		$champs = $champTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit, $this->view->user->id_hobbit);

		$retour = false;
		if (count($champs) == 1) {
			$this->champ = $champs[0];
			if ($this->champ["phase_champ"] == "seme") {
				$this->view->entretenirChampOk = true;
				$retour = true;
			}
		}

		return $retour;
	}

	private function preparePositions() {

		$selection = $this->request->get("position"); // si l'on vient de la vue (clic sur l'icone marcher)
		$tabPositions = null;

		for($i=1; $i<=10; $i++) {
			for($j=1; $j<=10; $j++) {
				$selected = "";
				if ($selection == $i."-".$j) {
					$selected = "selected";
				}
				$tabPositions[$i.'t'.$j] = array(
					'possible' => true,	
					'selected' => $selected,
					'x' => $i,
					'y' => $j,
				);
			}
		}

		Zend_Loader::loadClass("ChampTaupe");
		$champTaupeTable = new ChampTaupe();
		$taupes = $champTaupeTable->findByIdChamp($this->champ["id_champ"]);

		$tabTaupes = array();
		if ($this->champ["phase_champ"] == 'seme') {
			if ($taupes != null) {
				foreach($taupes as $t) {
					if ($t["etat_champ_taupe"] != 'vivant') {
						$tabPositions[$t["x_champ_taupe"].'t'.$t["y_champ_taupe"]]["possible"] = false;
						$tabPositions[$t["x_champ_taupe"].'t'.$t["y_champ_taupe"]]["selected"] = "";
					}
				}
			}
		}

		$this->view->positions = $tabPositions;
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

		// Verification semer
		if ($this->view->entretenirChampOk == false) {
			throw new Zend_Exception(get_class($this)." Entretenir Champ interdit");
		}

		$x_y = $this->request->get("valeur_1");
		list ($x, $y) = split("t", $x_y);

		if ($x < 0 || $x > 10 || $y < 0 || $y > 10) {
			throw new Zend_Exception(get_class($this)." XY invalides : ".$x_y);
		}

		if ($this->view->positions[$x."t".$y]["possible"] !== true) {
			throw new Zend_Exception(get_class($this)." XY impossibles : ".$x_y);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->entretenir($x, $y);
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function entretenir($x, $y) {

		Zend_Loader::loadClass("ChampTaupe");
		$champTaupeTable = new ChampTaupe();
		
		$data = array(
			'x_champ_taupe' => $x,
			'y_champ_taupe' => $y,
			'id_fk_champ_taupe' => $this->champ["id_champ"],
			'date_entretien_champ_taupe' => date('Y-m-d H:i:s'),
		);
		
		$etatZone = $champTaupeTable->entretenir($data);
		
		$etatZone["x"] = $x;
		$etatZone["y"] = $y;
		$this->view->etatZone = $etatZone;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_communes", "box_champs"));
	}
}