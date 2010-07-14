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
class Bral_Echoppes_Transferaliment extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("EchoppeAliment");
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
		$echoppes = $echoppeTable->findByIdBraldun($this->view->user->id_braldun);

		$echoppeOk = false;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe &&
			$e["x_echoppe"] == $this->view->user->x_braldun &&
			$e["y_echoppe"] == $this->view->user->y_braldun) {
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
		$charrettes = $charretteTable->findByIdBraldun($this->view->user->id_braldun);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$tabDestinationTransfert[] = array("id_destination" => "charrette", "texte" => "votre charrette", "selected" => $selectedCharrette);
		}

		$tabAlimentsArriereBoutique = null;
		$echoppeAlimentTable = new EchoppeAliment();
		$aliments = $echoppeAlimentTable->findByIdEchoppe($id_echoppe);

		if ($idDestinationCourante != null) {
			if ($idDestinationCourante == "charrette" && $charrette != null) {
				$poidsRestant = $charrette["poids_transportable_charrette"] - $charrette["poids_transporte_charrette"];
			} else {
				$poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
			}

			if (count($aliments) > 0) {
				foreach($aliments as $e) {
					if ($e["type_vente_echoppe_aliment"] == "aucune") {
						if ($poidsRestant < $e["poids_unitaire_type_aliment"]) {
							$placeDispo = false;
						} else {
							$placeDispo = true;
						}

						$tabAlimentsArriereBoutique[] = array(
							"id_echoppe_aliment" => $e["id_echoppe_aliment"],
							"id_fk_type_aliment" => $e["id_fk_type_aliment"],
							"nom" => $e["nom_type_aliment"],
							"poids" => $e["poids_unitaire_type_aliment"],
							"place_dispo" => $placeDispo,
						);
					}
				}
			}
		}

		$this->view->destinationTransfertCourante = $idDestinationCourante;
		$this->view->destinationTransfert = $tabDestinationTransfert;
		$this->view->alimentsArriereBoutique = $tabAlimentsArriereBoutique;
		$this->view->nbAlimentsArriereBoutique = count($tabAlimentsArriereBoutique);
		$this->view->charrette = $charrette;

		if ($this->view->nbAlimentsArriereBoutique > 0) {
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

		$id_aliment = $this->request->get("valeur_2");
		$id_destination = $this->request->get("valeur_3");

		if ((int) $id_aliment."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." id aliment invalide=".$id_aliment);
		} else {
			$id_aliment = (int)$id_aliment;
		}

		if ($this->request->get("id_destination_courante") != $id_destination) {
			throw new Zend_Exception(get_class($this)." Transferer interdit 2");
		}

		if ($this->view->charrette == null && $this->request->get("id_destination_courante") == "charrette") {
			throw new Zend_Exception(get_class($this)." Transferer interdit 3");
		}
		
		// on regarde si l'aliment est dans la liste
		$flag = false;
		$aliment = null;
		foreach($this->view->alimentsArriereBoutique  as $e) {
			if ($e["id_echoppe_aliment"] == $id_aliment && $e["place_dispo"] === true) {
				$aliment = $e;
				$flag = true;
				break;
			}
		}

		if ($flag == false) {
			throw new Zend_Exception(get_class($this)." id aliment inconnu=".$id_aliment);
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

		$this->calculTranfert($id_destination, $aliment);

		$this->view->aliment = $aliment;
		$this->view->destination = $destination;
	}

	private function calculTranfert($idDestination, $aliment) {

		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteAliment");
			$table = new CharretteAliment();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanAliment");
			$table = new LabanAliment();
			$suffixe = "laban";
		}

		$data = array(
				"id_".$suffixe."_aliment" => $aliment["id_echoppe_aliment"],
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_aliment"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_braldun_laban_aliment"] = $this->view->user->id_braldun;
		}
		$table->insert($data);

		$echoppeAlimentTable = new EchoppeAliment();
		$where = "id_echoppe_aliment=".$aliment["id_echoppe_aliment"];
		$echoppeAlimentTable->delete($where);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
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