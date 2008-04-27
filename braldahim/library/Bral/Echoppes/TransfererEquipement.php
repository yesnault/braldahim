<?php

class Bral_Echoppes_TransfererEquipement extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("TypeUnite");
		Zend_Loader::loadClass("TypeMinerai");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartiePlante");
		
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
		
		$tabEquipementsArriereBoutique = null;
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($id_echoppe);

		if (count($equipements) > 0) {
			foreach($equipements as $e) {
				if ($e["type_vente_echoppe_equipement"] == "aucune") {
					$tabEquipementsArriereBoutique[] = array(
					"id_echoppe_equipement" => $e["id_echoppe_equipement"],
					"id_fk_recette_echoppe_equipement" => $e["id_fk_recette_echoppe_equipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_echoppe_equipement"]
					);
				}
			}
		}
		
		$tabDestinationTransfert = null;
		
		$tabDestinationTransfert[] = array("id_destination" => "laban", "texte" => "votre laban");
		// TODO Autre ECHOPPE
		
		$this->view->destinationTransfert = $tabDestinationTransfert;
		$this->view->equipementsArriereBoutique = $tabEquipementsArriereBoutique;
		$this->view->nbEquipementsArriereBoutique = count($tabEquipementsArriereBoutique);
			
		if ($this->view->nbEquipementsArriereBoutique > 0) {
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
		
		$id_equipement = $this->request->get("valeur_2");
		$id_destination = $this->request->get("valeur_3");
		
		if ((int) $id_equipement."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." id equipement invalide=".$id_equipement);
		} else {
			$id_equipement = (int)$id_equipement;
		}
		
		// on regarde si l'equipement est dans la liste
		$flag = false;
		$equipement = null;
		foreach($this->view->equipementsArriereBoutique  as $e) {
			if ($e["id_echoppe_equipement"] == $id_equipement) {
				$equipement = $e;
				$flag = true;
				break;
			}
		}
		
		if ($flag == false) {
			throw new Zend_Exception(get_class($this)." id equipement inconnu=".$id_equipement);
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
			$this->calculTranfertVersLaban($equipement);
		}
		$this->view->equipement = $equipement;
		$this->view->destination = $destination;
		
		$this->calculPoids();
		$this->majHobbit();
	}
	
	private function calculTranfertVersLaban($equipement) {
		Zend_Loader::loadClass("LabanEquipement");
		$labanEquipementTable = new LabanEquipement();
		$data = array(
			'id_laban_equipement' => $equipement["id_echoppe_equipement"],
			'id_fk_recette_laban_equipement' => $equipement["id_fk_recette_echoppe_equipement"],
			'id_fk_hobbit_laban_equipement' => $this->view->user->id_hobbit,
			'nb_runes_laban_equipement' => $equipement["nb_runes"],
		);
		$labanEquipementTable->insert($data);
		
		$echoppeEquipementTable = new EchoppeEquipement();
		$where = "id_echoppe_equipement=".$equipement["id_echoppe_equipement"];
		$echoppeEquipementTable->delete($where);
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array("box_laban");
	}
}