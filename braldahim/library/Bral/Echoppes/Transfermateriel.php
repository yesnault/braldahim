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
class Bral_Echoppes_Transfermateriel extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("EchoppeMateriel");
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

		$idDestinationCourante = $this->request->get("id_destination_courante");

		$selectedLaban = "";
		$selectedCharrette = "";
		if ($idDestinationCourante == "laban") {
			$selectedLaban = "selected";
		} else if ($idDestinationCourante == "charrette") {
			$selectedCharrette = "selected";
		}
		$tabDestinationTransfert[] = array("id_destination" => "laban", "texte" => "votre laban", "selected" => $selectedLaban);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$tabDestinationTransfert[] = array("id_destination" => "charrette", "texte" => "votre charrette", "selected" => $selectedCharrette);
		}

		$tabMaterielsArriereBoutique = null;
		$echoppeMaterielTable = new EchoppeMateriel();
		$materiels = $echoppeMaterielTable->findByIdEchoppe($id_echoppe);

		if ($idDestinationCourante != null) {
			if ($idDestinationCourante == "charrette" && $charrette != null) {
				$poidsRestant = $charrette["poids_transportable_charrette"] - $charrette["poids_transporte_charrette"];
			} else {
				$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
			}

			if (count($materiels) > 0) {
				foreach($materiels as $e) {
					if ($e["type_vente_echoppe_materiel"] == "aucune") {
						if ($poidsRestant < $e["poids_type_materiel"]) {
							$placeDispo = false;
						} else {
							$placeDispo = true;
						}

						$tabMaterielsArriereBoutique[] = array(
						"id_echoppe_materiel" => $e["id_echoppe_materiel"],
						"id_fk_type_echoppe_materiel" => $e["id_fk_type_echoppe_materiel"],
						"nom" => $e["nom_type_materiel"],
						"poids" => $e["poids_type_materiel"],
						"place_dispo" => $placeDispo,
						);
					}
				}
			}
		}

		$this->view->destinationTransfertCourante = $idDestinationCourante;
		$this->view->destinationTransfert = $tabDestinationTransfert;
		$this->view->materielsArriereBoutique = $tabMaterielsArriereBoutique;
		$this->view->nbMaterielsArriereBoutique = count($tabMaterielsArriereBoutique);
		$this->view->charrette = $charrette;

		if ($this->view->nbMaterielsArriereBoutique > 0) {
			$this->view->transfererOk = true;
		} else {
			$this->view->transfererOk = false;
		}
		$this->view->idEchoppe = $id_echoppe;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->transfererOk == false) {
			throw new Zend_Exception(get_class($this)." Transferer interdit");
		}

		$id_materiel = $this->request->get("valeur_2");
		$id_destination = $this->request->get("valeur_3");

		if ((int) $id_materiel."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." id materiel invalide=".$id_materiel);
		} else {
			$id_materiel = (int)$id_materiel;
		}

		if ($this->request->get("id_destination_courante") != $id_destination) {
			throw new Zend_Exception(get_class($this)." Transferer interdit 2");
		}

		if ($this->view->charrette == null && $this->request->get("id_destination_courante") == "charrette") {
			throw new Zend_Exception(get_class($this)." Transferer interdit 3");
		}
		
		// on regarde si l'materiel est dans la liste
		$flag = false;
		$materiel = null;
		foreach($this->view->materielsArriereBoutique  as $e) {
			if ($e["id_echoppe_materiel"] == $id_materiel && $e["place_dispo"] === true) {
				$materiel = $e;
				$flag = true;
				break;
			}
		}

		if ($flag == false) {
			throw new Zend_Exception(get_class($this)." id materiel inconnu=".$id_materiel);
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

		$this->calculTranfert($id_destination, $materiel);

		$this->view->materiel = $materiel;
		$this->view->destination = $destination;
	}

	private function calculTranfert($idDestination, $materiel) {

		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteMateriel");
			$table = new CharretteMateriel();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanMateriel");
			$table = new LabanMateriel();
			$suffixe = "laban";
		}

		$data = array(
				"id_".$suffixe."_materiel" => $materiel["id_echoppe_materiel"],
				"id_fk_type_".$suffixe."_materiel" => $materiel["id_fk_type_echoppe_materiel"],
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_materiel"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_materiel"] = $this->view->user->id_hobbit;
		}
		$table->insert($data);

		$echoppeMaterielTable = new EchoppeMateriel();
		$where = "id_echoppe_materiel=".$materiel["id_echoppe_materiel"];
		$echoppeMaterielTable->delete($where);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}
	}

	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		if ($this->view->destination["id_destination"] == "charrette") {
			$boxToRefresh = "box_charrette";
		} else {
			$boxToRefresh = "box_laban";
		}
		return array("box_profil", "box_echoppe", "box_echoppes", $boxToRefresh, "box_evenements");
	}
}