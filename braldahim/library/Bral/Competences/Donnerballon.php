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
class Bral_Competences_Donnerballon extends Bral_Competences_Competence {

	function prepareCommun() {
		$this->view->donnerballonOk = false;
		$this->view->possedeBallon = false;

		Zend_Loader::loadClass("SouleMatch");
		$souleMatch = new SouleMatch();
		$matchsRowset = $souleMatch->findByIdHobbitBallon($this->view->user->id_hobbit);
		
		if ($matchsRowset != null && count($matchsRowset) == 1) {
			$this->match = $matchsRowset[0];
			$this->view->possedeBallon = true;
		} else {
			$this->view->possedeBallon = false;
			return;
		}
		
		// recuperation des hobbits qui sont presents sur la vue
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->id_hobbit, false);
		foreach($hobbits as $h) {
			$tab = array(
				'id_hobbit' => $h["id_hobbit"],
				'nom_hobbit' => $h["nom_hobbit"],
				'prenom_hobbit' => $h["prenom_hobbit"],
			);
			$tabHobbits[] = $tab;
			$this->view->donnerballonOk = true;
		}
		$this->view->tabHobbits = $tabHobbits;
		$this->view->nHobbits = count($tabHobbits);

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

		// Verification donner
		if ($this->view->donnerballonOk == false) {
			throw new Zend_Exception(get_class($this)." Donner ballon interdit ");
		}
		
		// Verification donner
		if ($this->view->possedeBallon == false) {
			throw new Zend_Exception(get_class($this)." Donner ballon interdit 2 ");
		}
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Hobbit invalide : ".$this->request->get("valeur_1"));
		} else {
			$idHobbit = (int)$this->request->get("valeur_1");
		}
		
		$donnerBallonHobbit = false;
		if (isset($this->view->tabHobbits) && count($this->view->tabHobbits) > 0) {
			foreach ($this->view->tabHobbits as $h) {
				if ($h["id_hobbit"] == $idHobbit) {
					$donnerBallonHobbit = true;
					break;
				}
			}
		}
		if ($donnerBallonHobbit === false) {
			throw new Zend_Exception(get_class($this)." Hobbit invalide (".$idHobbit.")");
		}
			
		$this->detailEvenement = "";

		$this->calculDonnerballon($idHobbit);

		$hobbitTable = new Hobbit();
		$hobbitDestinataire = $hobbitTable->findById($idHobbit);
		
		$this->detailEvenement = "[h".$this->view->user->id_hobbit."] a donné le ballon";
		$this->detailEvenement .= " à [h".$hobbitDestinataire->id_hobbit."]";
		$this->view->destinataire = $hobbitDestinataire->prenom_hobbit." ".$hobbitDestinataire->nom_hobbit." (".$hobbitDestinataire->id_hobbit.")";
		$this->setDetailsEvenement($this->detailEvenement, $this->view->config->game->evenements->type->soule);

		$this->setEvenementQueSurOkJet1(false);

		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}
	
	private function calculDonnerballon($idHobbit) {
		$souleMatch = new SouleMatch();
		$data = array(
			"x_ballon_soule_match" => null,
			"y_ballon_soule_match" => null,
			"id_fk_joueur_ballon_soule_match" => $idHobbit,
		);
		$where = "id_soule_match = ".$this->match["id_soule_match"];
		$souleMatch->update($data, $where);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_soule"));
	}

}
