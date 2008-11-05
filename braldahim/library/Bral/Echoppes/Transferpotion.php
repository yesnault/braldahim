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
class Bral_Echoppes_Transferpotion extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("TypeUnite");
		Zend_Loader::loadClass("TypeMinerai");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		
		$id_echoppe = $this->request->get("valeur_1");
		
		if ($id_echoppe == "" || $id_echoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide=".$id_echoppe);
		}
		
		// on verifie que c'est bien l'echoppe du joueur
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$echoppeOk = false;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe && 
				$e["x_echoppe"] == $this->view->user->x_hobbit && 
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$echoppeOk = true;
				break;
			}
		}
		
		if ($echoppeOk == false) {
			throw new Zend_Exception(get_class($this)." Echoppe interdite=".$id_echoppe);
		}
		
		$tabPotionsArriereBoutique = null;
		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByIdEchoppe($id_echoppe);

		if (count($potions) > 0) {
			foreach($potions as $e) {
				if ($e["type_vente_echoppe_potion"] == "aucune") {
					$tabPotionsArriereBoutique[] = array(
						"id_echoppe_potion" => $e["id_echoppe_potion"],
						"id_fk_type_potion_echoppe_potion" => $e["id_fk_type_potion_echoppe_potion"],
						"id_fk_type_qualite_laban_potion" => $e["id_fk_type_qualite_echoppe_potion"],
						"nom" => $e["nom_type_potion"],
						"qualite" => $e["nom_type_qualite"],
						"niveau" => $e["niveau_echoppe_potion"],
					);
				}
			}
		}
		
		$tabDestinationTransfert = null;
		
		$tabDestinationTransfert[] = array("id_destination" => "laban", "texte" => "votre laban");
		// TODO Autre ECHOPPE
		
		$this->view->destinationTransfert = $tabDestinationTransfert;
		$this->view->potionsArriereBoutique = $tabPotionsArriereBoutique;
		$this->view->nbPotionsArriereBoutique = count($tabPotionsArriereBoutique);
		
		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		
		if ($poidsRestant < Bral_Util_Poids::POIDS_POTION) {
			$this->view->placeDispo = false;
		} else {
			$this->view->placeDispo = true;
		}
		
		if ($this->view->nbPotionsArriereBoutique > 0) {
			$this->view->transfererOk = true;
		} else {
			$this->view->transfererOk = false;
			return;
		}
		
		$this->view->idEchoppe = $id_echoppe;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->transfererOk == false) {
			throw new Zend_Exception(get_class($this)." Transferer interdit");
		}
		
		$id_potion = $this->request->get("valeur_2");
		$id_destination = $this->request->get("valeur_3");
		
		if ((int) $id_potion."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." id potion invalide=".$id_potion);
		} else {
			$id_potion = (int)$id_potion;
		}
		
		if ($this->view->placeDispo == false) {
			throw new Zend_Exception(get_class($this)." place invalide=");
		}
		
		// on regarde si l'potion est dans la liste
		$flag = false;
		$potion = null;
		foreach($this->view->potionsArriereBoutique  as $e) {
			if ($e["id_echoppe_potion"] == $id_potion) {
				$potion = $e;
				$flag = true;
				break;
			}
		}
		
		if ($flag == false) {
			throw new Zend_Exception(get_class($this)." id potion inconnu=".$id_potion);
		}
		
		// on regarde si l'on connait la destination
		$flag = false;
		$destination = null;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["id_destination"] == $id_destination) {
				$destination = $d;
				$flag = true;
				break;
			}
		}
		
		if ($flag == false) {
			throw new Zend_Exception(get_class($this)." destination inconnue=".$destination);
		}
		
		if ($id_destination == "laban") {
			$this->calculTranfertVersLaban($potion);
		}
		$this->view->potion = $potion;
		$this->view->destination = $destination;
	}
	
	private function calculTranfertVersLaban($potion) {
		Zend_Loader::loadClass("LabanPotion");
		$labanPotionTable = new LabanPotion();
		$data = array(
			'id_laban_potion' => $potion["id_echoppe_potion"],
			'id_fk_type_laban_potion' => $potion["id_fk_type_potion_echoppe_potion"],
			'id_fk_hobbit_laban_potion' => $this->view->user->id_hobbit,
			'id_fk_type_qualite_laban_potion' => $potion["id_fk_type_qualite_laban_potion"],
			'niveau_laban_potion' => $potion["niveau"],
		);
		$labanPotionTable->insert($data);
		
		$echoppePotionTable = new EchoppePotion();
		$where = "id_echoppe_potion=".$potion["id_echoppe_potion"];
		$echoppePotionTable->delete($where);
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_equipement", "box_echoppe", "box_echoppes", "box_laban", "box_evenements");
	}
}