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
class Bral_Echoppes_Transferequipement extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Bral_Util_Equipement");
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("TypeUnite");
		Zend_Loader::loadClass("TypeMinerai");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		Zend_Loader::loadClass("Charrette");

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

		$tabEquipementsArriereBoutique = null;
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($id_echoppe);

		$poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;

		if ($idDestinationCourante != null) {
			if ($idDestinationCourante == "charrette" && $charrette != null) {
				$poidsRestant = $charrette["poids_transportable_charrette"] - $charrette["poids_transporte_charrette"];
			} else {
				$poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
			}

			if (count($equipements) > 0) {
				foreach($equipements as $e) {
					if ($e["type_vente_echoppe_equipement"] == "aucune") {
						if ($poidsRestant < $e["poids_equipement"]) {
							$placeDispo = false;
						} else {
							$placeDispo = true;
						}

						$tabEquipementsArriereBoutique[] = array(
							"id_echoppe_equipement" => $e["id_echoppe_equipement"],
							"id_fk_recette_equipement" => $e["id_fk_recette_equipement"],
							"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]),
							"qualite" => $e["nom_type_qualite"],
							"niveau" => $e["niveau_recette_equipement"],
							"nb_runes" => $e["nb_runes_equipement"],
							"poids" => $e["poids_equipement"],
							"place_dispo" => $placeDispo,
							"nom_systeme_type_emplacement" => $e["nom_systeme_type_emplacement"],
							"id_fk_type_munition_type_equipement" => $e["id_fk_type_munition_type_equipement"],
							"nb_munition_type_equipement" => $e["nb_munition_type_equipement"],
							"id_fk_region" => $e["id_fk_region_equipement"],
						);
					}
				}
			}
		}

		$this->view->destinationTransfertCourante = $idDestinationCourante;
		$this->view->destinationTransfert = $tabDestinationTransfert;
		$this->view->equipementsArriereBoutique = $tabEquipementsArriereBoutique;
		$this->view->nbEquipementsArriereBoutique = count($tabEquipementsArriereBoutique);
		$this->view->charrette = $charrette;
			
		if ($this->view->nbEquipementsArriereBoutique > 0) {
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

		$id_equipement = $this->request->get("valeur_2");
		$id_destination = $this->request->get("valeur_3");

		if ((int) $id_equipement."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." id equipement invalide=".$id_equipement);
		} else {
			$id_equipement = (int)$id_equipement;
		}

		if ($this->request->get("id_destination_courante") != $id_destination) {
			throw new Zend_Exception(get_class($this)." Transferer interdit 2");
		}

		if ($this->view->charrette == null && $this->request->get("id_destination_courante") == "charrette") {
			throw new Zend_Exception(get_class($this)." Transferer interdit 3");
		}

		// on regarde si l'equipement est dans la liste
		$flag = false;
		$equipement = null;
		foreach($this->view->equipementsArriereBoutique  as $e) {
			if ($e["id_echoppe_equipement"] == $id_equipement && $e["place_dispo"] === true) {
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

		$this->calculTransfert($id_destination, $equipement);

		$this->view->equipement = $equipement;
		$this->view->destination = $destination;
	}

	private function calculTransfert($idDestination, $equipement) {

		if ($equipement["nom_systeme_type_emplacement"] == 'laban') {
			if ($idDestination == "charrette") {
				Zend_Loader::loadClass("CharretteMunition");
				$table = new CharretteMunition();
				$suffixe = "charrette";
			} else {
				Zend_Loader::loadClass("LabanMunition");
				$table = new LabanMunition();
				$suffixe = "laban";
			}

			$data = array(
				"id_fk_type_".$suffixe."_munition" => $equipement["id_fk_type_munition_type_equipement"],
				"quantite_".$suffixe."_munition" => $equipement["nb_munition_type_equipement"],
			);

			if ($idDestination == "charrette") {
				$data["id_fk_charrette_munition"] = $this->view->charrette["id_charrette"];
			} else {
				$data["id_fk_braldun_laban_munition"] = $this->view->user->id_braldun;
			}
			$table->insertOrUpdate($data);
		} else {
			if ($idDestination == "charrette") {
				Zend_Loader::loadClass("CharretteEquipement");
				$table = new CharretteEquipement();
				$suffixe = "charrette";
			} else {
				Zend_Loader::loadClass("LabanEquipement");
				$table = new LabanEquipement();
				$suffixe = "laban";
			}

			$data = array(
				"id_".$suffixe."_equipement" => $equipement["id_echoppe_equipement"],
			);

			if ($idDestination == "charrette") {
				$data["id_fk_charrette_equipement"] = $this->view->charrette["id_charrette"];
			} else {
				$data["id_fk_braldun_laban_equipement"] = $this->view->user->id_braldun;
			}
			$table->insert($data);
		}

		$echoppeEquipementTable = new EchoppeEquipement();
		$where = "id_echoppe_equipement=".$equipement["id_echoppe_equipement"];
		$echoppeEquipementTable->delete($where);

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