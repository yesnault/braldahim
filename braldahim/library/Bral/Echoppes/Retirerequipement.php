<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Echoppes_Retirerequipement extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("EchoppeEquipement");
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
		
		$tabEquipementsEtal = null;
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($id_echoppe);

		if (count($equipements) > 0) {
			foreach($equipements as $e) {
				if ($e["type_vente_echoppe_equipement"] != "aucune") {
					$tabEquipementsEtal[] = array(
					"id_echoppe_equipement" => $e["id_echoppe_equipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_echoppe_equipement"]
					);
				}
			}
		}
		$this->view->equipementsEtal = $tabEquipementsEtal;
		$this->view->nbEquipementsEtal = count($tabEquipementsEtal);
		$this->view->idEchoppe = $id_echoppe;
		
		if ($this->view->nbEquipementsEtal > 0) {
			$this->view->retirerequipementOk = true;
		} else {
			$this->view->retirerequipementOk = false;
			return;
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->retirerequipementOk == false) {
			throw new Zend_Exception(get_class($this)." Retirerequipement interdit");
		}
		$id_equipement = $this->request->get("valeur_2");
		
		if ((int) $id_equipement."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." Equipement invalide=".$id_equipement);
		} else {
			$id_equipement = (int)$id_equipement;
		}
		
		$equipementOk = false;
		foreach($this->view->equipementsEtal as $e) {
			if ($e["id_echoppe_equipement"] == $id_equipement) {
				$equipementOk = true;
				$this->view->equipement = $e;
				break;
			}
		}

		if ($equipementOk == false) {
			throw new Zend_Exception(get_class($this)." Equipement inconnu=".$id_equipement);
		}
		
		$this->calculRetirerequipement($id_equipement);
	}
	
	private function calculRetirerequipement($id_equipement) {
		Zend_Loader::loadClass("EchoppeEquipement");
		$data = array("prix_1_vente_echoppe_equipement" => null,
					  "prix_2_vente_echoppe_equipement" => null,
					  "prix_3_vente_echoppe_equipement" => null,
					  "unite_1_vente_echoppe_equipement" => null,
					  "unite_2_vente_echoppe_equipement" => null,
					  "unite_3_vente_echoppe_equipement" => null,
					  "type_vente_echoppe_equipement" => "aucune");
		
		$where = "id_echoppe_equipement=".$id_equipement; 
		$echoppeEquipementTable = new EchoppeEquipement();
		$echoppeEquipementTable->update($data, $where);
		
		Zend_Loader::loadClass("EchoppeEquipementMinerai");
		$echoppeEquipementMineraiTable = new EchoppeEquipementMinerai();
		$where = "id_fk_echoppe_equipement_minerai=".$id_equipement;
		$echoppeEquipementMineraiTable->delete($where);
		
		Zend_Loader::loadClass("EchoppeEquipementPartiePlante");
		$echoppeEquipementPartiePlanteTable = new EchoppeEquipementPartiePlante();
		$where = "id_fk_echoppe_equipement_partieplante=".$id_equipement;
		$echoppeEquipementPartiePlanteTable->delete($where);
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