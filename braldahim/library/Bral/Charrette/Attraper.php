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
class Bral_Charrette_Attraper extends Bral_Charrette_Charrette {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Attraper une charrette";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");

		$tabCharrettes = null;
		$this->view->possedeCharrette = false;
		$this->view->attraperCharrettePossible = false;

		$charretteTable = new Charrette();

		$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
		if ($nombre > 0) {
			$this->view->possedeCharrette = true;
			return;
		}

		$provenance = $this->request->get("provenance");

		$charrettes = null;
		if ($provenance == "echoppe") {
			Zend_Loader::loadClass("Echoppe");
			// On regarde si le hobbit est dans une de ses echopppes
			$echoppeTable = new Echoppe();

			$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit);

			if (count($echoppes) == 1) {
				$echoppe = $echoppes[0];
				if ($echoppe["x_echoppe"] != $this->view->user->x_hobbit || $echoppe["y_echoppe"] != $this->view->user->y_hobbit) {
					throw new Zend_Exception(get_class($this)." Echoppe invalide. idh:".$this->view->user->id_hobbit);
				}

				Zend_Loader::loadClass("EchoppeMateriel");

				$echoppeMaterielTable = new EchoppeMateriel();
				$materiels = $echoppeMaterielTable->findByIdEchoppe($echoppe["id_echoppe"]);
				foreach ($materiels as $m) {
					if (substr($m["nom_systeme_type_materiel"], 0, 9) == "charrette") {
						$charrettes[] = $m;
					}
				}
				$typeProvenance = "echoppe";
				$nomIdCharrette = "id_echoppe_materiel";
			}
		} else {
			$charrettes = $charretteTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->user->z_hobbit);
			$typeProvenance = "sol";
			$nomIdCharrette = "id_charrette";
		}

		if (count($charrettes) > 0) {
			Zend_Loader::loadClass("Bral_Util_Metier");
			$tab = Bral_Util_Metier::prepareMetier($this->view->user->id_hobbit, $this->view->user->sexe_hobbit);
			$estMenuisierOuBucheron = false;
			if ($tab["tabMetierCourant"]["nom_systeme"] == "bucheron" || $tab["tabMetierCourant"]["nom_systeme"] == "menuisier") {
				$estMenuisierOuBucheron = true;
			}

			Zend_Loader::loadClass("Bral_Util_Charrette");
			foreach ($charrettes as $c) {
				$this->view->attraperCharrettePossible = true;

				$tab = Bral_Util_Charrette::calculAttraperPossible($c, $this->view->user, $estMenuisierOuBucheron);
				$possible = $tab["possible"];
				$detail = $tab["detail"];

				$tabCharrettes[] = array (
					"id_charrette" => $c[$nomIdCharrette],
					"nom" => $c["nom_type_materiel"], 
					"possible" => $possible, 
					"detail" => $detail, 
					"provenance" => $typeProvenance,
					"id_type_materiel" => $c["id_type_materiel"],
					"durabilite_type_materiel" => $c["durabilite_type_materiel"],
					"capacite_type_materiel" => $c["capacite_type_materiel"],
				);
			}
		}
		$this->view->charrettes = $tabCharrettes;
		$this->view->provenance = $provenance;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		// Verification abattre arbre
		if ($this->view->possedeCharrette == true) {
			throw new Zend_Exception(get_class($this)." Possede deja charrette ");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Charrette invalide : ".$this->request->get("valeur_1"));
		} else {
			$this->view->idCharrette = (int)$this->request->get("valeur_1");
		}

		$charrette = null;

		foreach ($this->view->charrettes as $c) {
			if ($this->view->idCharrette == $c["id_charrette"] && $c["possible"] == true) {
				$charrette = $c;
				break;
			}
		}
		if ($charrette == null) {
			throw new Zend_Exception(get_class($this)." Charrette invalide idh:".$this->view->user->pa_hobbit. " ihc:".$this->view->idCharrette);
		}

		$this->calculAttrapperCharrette($charrette);
		$this->calculBalanceFaim();

		$id_type = $this->view->config->game->evenements->type->ramasser;
		$details = "[h".$this->view->user->id_hobbit."] a attrapé une charrette";
		$this->setDetailsEvenement($details, $id_type);

		$details = "[h".$this->view->user->id_hobbit."] a attrapé la charrette n°".$charrette["id_charrette"];
		Zend_Loader::loadClass("Bral_Util_Materiel");
		Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_UTILISER_ID, $charrette["id_charrette"], $details);
	}

	private function calculAttrapperCharrette($charrette) {

		$charretteTable = new Charrette();

		$dataUpdate = array(
			"id_fk_hobbit_charrette" => $this->view->user->id_hobbit,
			"x_charrette" => null,
			"y_charrette" => null,
			"z_charrette" => null,
		);
			
		if ($charrette["provenance"] == "sol") {
			$where = "id_charrette = ".$charrette["id_charrette"];
			$charretteTable->update($dataUpdate, $where);
		} else if ($this->view->provenance == "echoppe") {
			$dataUpdate["id_charrette"] = $charrette["id_charrette"];
				
			$dataUpdate["durabilite_max_charrette"] = $charrette["durabilite_type_materiel"];
			$dataUpdate["durabilite_actuelle_charrette"] = $charrette["durabilite_type_materiel"];
			$dataUpdate["poids_transportable_charrette"] = $charrette["capacite_type_materiel"];
			$dataUpdate["poids_transporte_charrette"] = 0;
				
			$where = "id_charrette = ".$charrette["id_charrette"];
			$charretteTable->insert($dataUpdate);

			$echoppeMaterielTable = new EchoppeMateriel();
			$where = "id_echoppe_materiel=".$charrette["id_charrette"];
			$echoppeMaterielTable->delete($where);
		}

		Zend_Loader::loadClass("Bral_Util_Charrette");
		Bral_Util_Charrette::calculAmeliorationsCharrette($this->view->user->id_hobbit);
	}

	function getListBoxRefresh() {
		if ($this->view->provenance == "echoppe") {
			$tab = array("box_echoppes");
		} else {
			$tab = array("box_vue");
		}
		return $this->constructListBoxRefresh($tab);
	}
}
