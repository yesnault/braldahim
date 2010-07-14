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
class Bral_Echoppes_Retireraliment extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("EchoppeAliment");
		Zend_Loader::loadClass("Echoppe");

		$id_echoppe = $this->request->get("valeur_1");

		if ($id_echoppe == "" || $id_echoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide=".$id_echoppe);
		}

		// on verifie que c'est bien l'echoppe du joueur
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdBraldun($this->view->user->id_braldun);

		$echoppeOk = false;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe) {
				$echoppeOk = true;
				break;
			}
		}

		if ($echoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Echoppe interdite=".$id_echoppe);
		}

		$tabAlimentsEtal = null;
		$echoppeAlimentTable = new EchoppeAliment();
		$aliments = $echoppeAlimentTable->findByIdEchoppe($id_echoppe);

		if (count($aliments) > 0) {
			foreach($aliments as $e) {
				if ($e["type_vente_echoppe_aliment"] != "aucune") {
					$tabAlimentsEtal[] = array(
					"id_echoppe_aliment" => $e["id_echoppe_aliment"],
					"nom" => $e["nom_type_aliment"],
					);
				}
			}
		}
		$this->view->alimentsEtal = $tabAlimentsEtal;
		$this->view->nbAlimentsEtal = count($tabAlimentsEtal);
		$this->view->idEchoppe = $id_echoppe;

		if ($this->view->nbAlimentsEtal > 0) {
			$this->view->retireralimentOk = true;
		} else {
			$this->view->retireralimentOk = false;
			return;
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->retireralimentOk == false) {
			throw new Zend_Exception(get_class($this)." Retireraliment interdit");
		}
		$id_aliment = $this->request->get("valeur_2");

		if ((int) $id_aliment."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." Aliment invalide=".$id_aliment);
		} else {
			$id_aliment = (int)$id_aliment;
		}

		$alimentOk = false;
		foreach($this->view->alimentsEtal as $e) {
			if ($e["id_echoppe_aliment"] == $id_aliment) {
				$alimentOk = true;
				$this->view->aliment = $e;
				break;
			}
		}

		if ($alimentOk == false) {
			throw new Zend_Exception(get_class($this)." Aliment inconnu=".$id_aliment);
		}

		$this->calculRetireraliment($id_aliment);
	}

	private function calculRetireraliment($id_aliment) {
		Zend_Loader::loadClass("EchoppeAliment");
		$data = array("prix_1_vente_echoppe_aliment" => null,
					  "prix_2_vente_echoppe_aliment" => null,
					  "prix_3_vente_echoppe_aliment" => null,
					  "unite_1_vente_echoppe_aliment" => null,
					  "unite_2_vente_echoppe_aliment" => null,
					  "unite_3_vente_echoppe_aliment" => null,
					  "type_vente_echoppe_aliment" => "aucune");

		$where = "id_echoppe_aliment=".$id_aliment;
		$echoppeAlimentTable = new EchoppeAliment();
		$echoppeAlimentTable->update($data, $where);

		Zend_Loader::loadClass("EchoppeAlimentMinerai");
		$echoppeAlimentMineraiTable = new EchoppeAlimentMinerai();
		$where = "id_fk_echoppe_aliment_minerai=".$id_aliment;
		$echoppeAlimentMineraiTable->delete($where);

		Zend_Loader::loadClass("EchoppeAlimentPartiePlante");
		$echoppeAlimentPartiePlanteTable = new EchoppeAlimentPartiePlante();
		$where = "id_fk_echoppe_aliment_partieplante=".$id_aliment;
		$echoppeAlimentPartiePlanteTable->delete($where);
	}

	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
	}
}