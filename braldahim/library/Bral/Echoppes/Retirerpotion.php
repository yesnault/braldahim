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
class Bral_Echoppes_Retirerpotion extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("EchoppePotion");
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
		
		$tabPotionsEtal = null;
		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByIdEchoppe($id_echoppe);

		if (count($potions) > 0) {
			foreach($potions as $e) {
				if ($e["type_vente_echoppe_potion"] != "aucune") {
					$tabPotionsEtal[] = array(
						"id_echoppe_potion" => $e["id_echoppe_potion"],
						"nom" => $e["nom_type_potion"],
						"qualite" => $e["nom_type_qualite"],
						"niveau" => $e["niveau_echoppe_potion"]
					);
				}
			}
		}
		$this->view->potionsEtal = $tabPotionsEtal;
		$this->view->nbPotionsEtal = count($tabPotionsEtal);
		$this->view->idEchoppe = $id_echoppe;
		
		if ($this->view->nbPotionsEtal > 0) {
			$this->view->retirerpotionOk = true;
		} else {
			$this->view->retirerpotionOk = false;
			return;
		}
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->retirerpotionOk == false) {
			throw new Zend_Exception(get_class($this)." Retirerpotion interdit");
		}
		$id_potion = $this->request->get("valeur_2");
		
		if ((int) $id_potion."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." Potion invalide=".$id_potion);
		} else {
			$id_potion = (int)$id_potion;
		}
		
		$potionOk = false;
		foreach($this->view->potionsEtal as $e) {
			if ($e["id_echoppe_potion"] == $id_potion) {
				$potionOk = true;
				$this->view->potion = $e;
				break;
			}
		}

		if ($potionOk == false) {
			throw new Zend_Exception(get_class($this)." Potion inconnu=".$id_potion);
		}
		
		$this->calculRetirerpotion($id_potion);
	}
	
	private function calculRetirerpotion($id_potion) {
		Zend_Loader::loadClass("EchoppePotion");
		$data = array("prix_1_vente_echoppe_potion" => null,
					  "prix_2_vente_echoppe_potion" => null,
					  "prix_3_vente_echoppe_potion" => null,
					  "unite_1_vente_echoppe_potion" => null,
					  "unite_2_vente_echoppe_potion" => null,
					  "unite_3_vente_echoppe_potion" => null,
					  "type_vente_echoppe_potion" => "aucune");
		
		$where = "id_echoppe_potion=".$id_potion; 
		$echoppePotionTable = new EchoppePotion();
		$echoppePotionTable->update($data, $where);
		
		Zend_Loader::loadClass("EchoppePotionMinerai");
		$echoppePotionMineraiTable = new EchoppePotionMinerai();
		$where = "id_fk_echoppe_potion_minerai=".$id_potion;
		$echoppePotionMineraiTable->delete($where);
		
		Zend_Loader::loadClass("EchoppePotionPartiePlante");
		$echoppePotionPartiePlanteTable = new EchoppePotionPartiePlante();
		$where = "id_fk_echoppe_potion_partieplante=".$id_potion;
		$echoppePotionPartiePlanteTable->delete($where);
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