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
class Bral_Echoppes_Retirermateriel extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("EchoppeMateriel");
		Zend_Loader::loadClass("Echoppe");

		$id_echoppe = $this->request->get("valeur_1");

		if ($id_echoppe == "" || $id_echoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide=".$id_echoppe);
		}

		// on verifie que c'est bien l'echoppe du joueur
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);

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

		$tabMaterielsEtal = null;
		$echoppeMaterielTable = new EchoppeMateriel();
		$materiels = $echoppeMaterielTable->findByIdEchoppe($id_echoppe);

		if (count($materiels) > 0) {
			foreach($materiels as $e) {
				if ($e["type_vente_echoppe_materiel"] != "aucune") {
					$tabMaterielsEtal[] = array(
					"id_echoppe_materiel" => $e["id_echoppe_materiel"],
					"nom" => $e["nom_type_materiel"],
					);
				}
			}
		}
		$this->view->materielsEtal = $tabMaterielsEtal;
		$this->view->nbMaterielsEtal = count($tabMaterielsEtal);
		$this->view->idEchoppe = $id_echoppe;

		if ($this->view->nbMaterielsEtal > 0) {
			$this->view->retirermaterielOk = true;
		} else {
			$this->view->retirermaterielOk = false;
			return;
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->retirermaterielOk == false) {
			throw new Zend_Exception(get_class($this)." Retirermateriel interdit");
		}
		$id_materiel = $this->request->get("valeur_2");

		if ((int) $id_materiel."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." Materiel invalide=".$id_materiel);
		} else {
			$id_materiel = (int)$id_materiel;
		}

		$materielOk = false;
		foreach($this->view->materielsEtal as $e) {
			if ($e["id_echoppe_materiel"] == $id_materiel) {
				$materielOk = true;
				$this->view->materiel = $e;
				break;
			}
		}

		if ($materielOk == false) {
			throw new Zend_Exception(get_class($this)." Materiel inconnu=".$id_materiel);
		}

		$this->calculRetirermateriel($id_materiel);
	}

	private function calculRetirermateriel($id_materiel) {
		Zend_Loader::loadClass("EchoppeMateriel");
		$data = array("prix_1_vente_echoppe_materiel" => null,
					  "prix_2_vente_echoppe_materiel" => null,
					  "prix_3_vente_echoppe_materiel" => null,
					  "unite_1_vente_echoppe_materiel" => null,
					  "unite_2_vente_echoppe_materiel" => null,
					  "unite_3_vente_echoppe_materiel" => null,
					  "type_vente_echoppe_materiel" => "aucune");

		$where = "id_echoppe_materiel=".$id_materiel;
		$echoppeMaterielTable = new EchoppeMateriel();
		$echoppeMaterielTable->update($data, $where);

		Zend_Loader::loadClass("EchoppeMaterielMinerai");
		$echoppeMaterielMineraiTable = new EchoppeMaterielMinerai();
		$where = "id_fk_echoppe_materiel_minerai=".$id_materiel;
		$echoppeMaterielMineraiTable->delete($where);

		Zend_Loader::loadClass("EchoppeMaterielPartiePlante");
		$echoppeMaterielPartiePlanteTable = new EchoppeMaterielPartiePlante();
		$where = "id_fk_echoppe_materiel_partieplante=".$id_materiel;
		$echoppeMaterielPartiePlanteTable->delete($where);
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