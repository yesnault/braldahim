<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Donnerballon extends Bral_Competences_Competence {

	function prepareCommun() {
		$this->view->donnerballonOk = false;
		$this->view->possedeBallon = false;

		Zend_Loader::loadClass("SouleMatch");
		$souleMatch = new SouleMatch();
		$matchsRowset = $souleMatch->findByIdBraldunBallon($this->view->user->id_braldun);

		if ($matchsRowset != null && count($matchsRowset) == 1) {
			$this->match = $matchsRowset[0];
			$this->view->possedeBallon = true;
		} else {
			$this->view->possedeBallon = false;
			return;
		}

		// recuperation des bralduns qui sont presents sur la vue
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun, false);
		$tabBralduns = null;
		foreach($bralduns as $h) {
			if ($h["soule_camp_braldun"] == $this->view->user->soule_camp_braldun) {
				$tab = array(
				'id_braldun' => $h["id_braldun"],
				'nom_braldun' => $h["nom_braldun"],
				'prenom_braldun' => $h["prenom_braldun"],
				);
				$tabBralduns[] = $tab;
				$this->view->donnerballonOk = true;
			}
		}
		$this->view->tabBralduns = $tabBralduns;
		$this->view->nBralduns = count($tabBralduns);
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		// Verification donner
		if ($this->view->donnerballonOk == false) {
			throw new Zend_Exception(get_class($this)." Donner ballon interdit ");
		}

		// Verification donner
		if ($this->view->possedeBallon == false) {
			throw new Zend_Exception(get_class($this)." Donner ballon interdit 2 ");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Braldun invalide : ".$this->request->get("valeur_1"));
		} else {
			$idBraldun = (int)$this->request->get("valeur_1");
		}

		$donnerBallonBraldun = false;
		if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
			foreach ($this->view->tabBralduns as $h) {
				if ($h["id_braldun"] == $idBraldun) {
					$donnerBallonBraldun = true;
					break;
				}
			}
		}
		if ($donnerBallonBraldun === false) {
			throw new Zend_Exception(get_class($this)." Braldun invalide (".$idBraldun.")");
		}
			
		$this->detailEvenement = "";

		$this->calculDonnerballon($idBraldun);

		$braldunTable = new Braldun();
		$braldunDestinataire = $braldunTable->findById($idBraldun);

		$this->detailEvenement = "[b".$this->view->user->id_braldun."] a donné le ballon";
		$this->detailEvenement .= " à [b".$braldunDestinataire->id_braldun."]";
		$this->view->destinataire = $braldunDestinataire->prenom_braldun." ".$braldunDestinataire->nom_braldun." (".$braldunDestinataire->id_braldun.")";
		$this->setDetailsEvenement($this->detailEvenement, $this->view->config->game->evenements->type->soule);
		$this->idMatchSoule = $this->match["id_soule_match"];

		// evenements du destinataire
		$detailsBotDestinataire = "Vous avez reçu le ballon de soule !";
		$detailEvenementDestinataire = "[b".$braldunDestinataire->id_braldun."] a reçu le ballon de la part de [b".$this->view->user->id_braldun."]";
		Bral_Util_Evenement::majEvenements($braldunDestinataire->id_braldun, $this->view->config->game->evenements->type->soule, $detailEvenementDestinataire, $detailsBotDestinataire, $braldunDestinataire->niveau_braldun, "braldun", true, $this->view);

		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	private function calculDonnerballon($idBraldun) {
		$souleMatch = new SouleMatch();
		$data = array(
			"x_ballon_soule_match" => null,
			"y_ballon_soule_match" => null,
			"id_fk_joueur_ballon_soule_match" => $idBraldun,
		);
		$where = "id_soule_match = ".$this->match["id_soule_match"];
		$souleMatch->update($data, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_soule"));
	}

}
