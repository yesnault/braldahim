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
class Bral_Competences_Manger extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("Ville");
		Zend_Loader::loadClass("Bral_Util_Quete");

		$labanAlimentTable = new LabanAliment();
		$aliments = $labanAlimentTable->findByIdHobbit($this->view->user->id_hobbit);

		$tabAliments = null;
		foreach ($aliments as $p) {
			$tabAliments[$p["id_aliment"]] = array(
					"id_aliment" => $p["id_aliment"],
					"id_fk_type_aliment" => $p["id_fk_type_aliment"],
					"id_fk_type_qualite_aliment" => $p["id_fk_type_qualite_aliment"],
					"nom" => $p["nom_type_aliment"],
					"qualite" => $p["nom_type_qualite"],
					"bbdf" => $p["bbdf_aliment"],
			);
		}

		if (isset($tabAliments) && count($tabAliments) > 0) {
			$this->view->mangerNbAlimentOk = true;
		}

		$this->view->tabAliments = $tabAliments;
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

		// Verification cuisiner
		if ($this->view->mangerNbAlimentOk == false) {
			throw new Zend_Exception(get_class($this)." Manger interdit ");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Aliment invalide : ".$this->request->get("valeur_1"));
		} else {
			$idAliment = (int)$this->request->get("valeur_1");
		}
		
		$aliment = null;
		foreach ($this->view->tabAliments as $a) {
			if ($a["id_aliment"] == $idAliment) {
				$aliment = $a;
				break;
			}
		}

		if ($aliment == null) {
			throw new Zend_Exception(get_class($this)." Aliment invalide (".$idAliment.")");
		}

		// cacul de la quete avant, pour avoir le controle sur l'état repu ou affame.
		$this->view->estQueteEvenement = Bral_Util_Quete::etapeManger($this->view->user, false);

		$this->calculManger($aliment);
		
		$idType = $this->view->config->game->evenements->type->competence;
		$details = "[h".$this->view->user->id_hobbit."] a mangé";
		$this->setDetailsEvenement($details, $idType);
		$this->setEvenementQueSurOkJet1(false);

		Zend_Loader::loadClass("Bral_Util_Quete");

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}

	private function calculManger($aliment) {
		Zend_Loader::loadClass("LabanAliment");

		$labanAlimentTable = new LabanAliment();
		$where = 'id_laban_aliment = '.(int)$aliment["id_aliment"];
		$labanAlimentTable->delete($where);

		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($this->view->user->id_hobbit);
		$hobbit = $hobbitRowset->current();

		$this->view->user->balance_faim_hobbit = $this->view->user->balance_faim_hobbit + $aliment["bbdf"];

		if ($this->view->user->balance_faim_hobbit > 100) {
			$this->view->user->balance_faim_hobbit = 100;
		}

		$data = array(
			'balance_faim_hobbit' => $this->view->user->balance_faim_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		$hobbitTable->update($data, $where);
		
		$this->view->aliment = $aliment;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban"));
	}
}
