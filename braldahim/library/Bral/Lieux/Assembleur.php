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
class Bral_Lieux_Assembleur extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;

	function prepareCommun() {

		Zend_Loader::loadClass("Bral_Util_Charrette");
		Bral_Util_Charrette::calculAmeliorationsCharrette($this->view->user->id_hobbit);
		
		$this->view->coutCastars = $this->calculCoutCastars();
		$this->view->achatPossibleCastars = ($this->view->user->castars_hobbit - $this->view->coutCastars >= 0);
		if ($this->view->achatPossibleCastars == false) {
			return;
		}

		$idMaterielCourant = $this->request->get("id_materiel_courant");

		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("TypeMaterielAssemble");
		Zend_Loader::loadClass("LabanMateriel");
		Zend_Loader::loadClass("CharretteMateriel");
		Zend_Loader::loadClass("CharretteMaterielAssemble");

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		$tabMaterielsBase = null;
		$materielCourant = null;

		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$selected = "";
			if ($idMaterielCourant ==  $charrette["id_charrette"]) {
				$selected = "selected";
			}
			$m = array(
				"type" => 'charrette', 
				"id_materiel" => $charrette["id_charrette"], 
				"nom_type_materiel" => $charrette["nom_type_materiel"],
				"id_type_materiel" => $charrette["id_type_materiel"],
				"selected" => $selected,
				"origine" => "",
			);

			$tabMaterielsBase[] = $m;

			if ($idMaterielCourant ==  $charrette["id_charrette"]) {
				$materielCourant = $m;
			}
		} else if (count($charrettes) > 1) {
			throw new Zend_Exception(get_class($this)." Nb Charrettes invalide:".count($charrettes). " idh=".$this->view->user->id_hobbit);
		}

		$this->prepareMaterielBase("laban", $materielCourant, $tabMaterielsBase);
		$this->prepareMaterielBase("charrette", $materielCourant, $tabMaterielsBase);

		$tabMaterielsAAssembler = null;

		if ($materielCourant != null) {
			$this->prepareMaterielsAAssembler("laban", $materielCourant, $tabMaterielsAAssembler);
			$this->prepareMaterielsAAssembler("charrette", $materielCourant, $tabMaterielsAAssembler);
		}
		
		$this->view->materielCourant = $materielCourant;
		$this->view->materielsBase = $tabMaterielsBase;
		$this->view->materielsAAssembler = $tabMaterielsAAssembler;
	}

	private function prepareMaterielBase($type, &$materielCourant, &$tabMaterielsBase) {
		if ($type == "laban") {
			$table = new LabanMateriel();
			$suffixe = "laban";
			$origine = "le laban";
			$materielsBase = $table->findByIdHobbit($this->view->user->id_hobbit);
		} elseif ($type == "charrette") {
			$table = new CharretteMateriel();
			$suffixe = "charrette";
			$origine = "la charrette";
			$materielsBase = $table->findByIdCharrette($materielCourant["id_materiel"]);
		}

		if (count($materielsBase) > 0) {
			$typeMaterielAssembleTable = new TypeMaterielAssemble();

			$listIdType = null;
			foreach($materielsBase as $l) {
				$listIdType[] = $l["id_".$suffixe."_materiel"];
			}

			$listeTypesBase = $typeMaterielAssembleTable->findByIdListTypeBase($listIdType);

			foreach($materielsBase as $l) {
				foreach($listeTypesBase as $t) {
					if ($l["id_type_materiel"] == $t["id_base_type_materiel_assemble"]) { // si c'est un matériel de base
						$selected = "";
						if ($idMaterielCourant ==  $l["id_".$suffixe."_materiel"]) {
							$selected = "selected";
						}
						$m = array(
							"type" => $suffixe, 
							"id_materiel" => $l["id_".$suffixe."_materiel"], 
							"nom_type_materiel" => $l["nom_type_materiel"],
							"id_type_materiel" => $l["id_type_materiel"],
							"selected" => $selected,
							"origine" => $origine,
						);

						$tabMaterielsBase[] = $m;

						if ($idMaterielCourant ==  $l["id_".$suffixe."_materiel"]) {
							$materielCourant = $m;
						}
					}
				}
			}
		}
	}

	private function prepareMaterielsAAssembler($type, $materielCourant, &$tabMaterielsAAssembler) {

		if ($type == "laban") {
			$table = new LabanMateriel();
			$suffixe = "laban";
			$origine = "le laban";
			$materiels = $table->findByIdHobbit($this->view->user->id_hobbit);
		} elseif ($type == "charrette") {
			$table = new CharretteMateriel();
			$suffixe = "charrette";
			$origine = "la charrette";
			$materiels = $table->findByIdCharrette($materielCourant["id_materiel"]);
		}

		$materielsAssembles = null;
		if ($materielCourant["type"] == "charrette") {
			$charretteMaterielAssembleTable = new CharretteMaterielAssemble();
			$materielsDejaAssembles = $charretteMaterielAssembleTable->findByIdCharrette($materielCourant["id_materiel"]);
		}


		$typeMaterielAssembleTable = new TypeMaterielAssemble();
		$typesBase = $typeMaterielAssembleTable->findByIdTypeBase($materielCourant["id_type_materiel"]);

		foreach($materiels as $m) {
			foreach($typesBase as $t) {
				// on verifie que le materiel peut être assemblé avec le matériel de base choisi
				if ($t["id_supplement_type_materiel_assemble"] == $m["id_fk_type_materiel"] &&
				$this->estDejaAssembleSurCharrette($materielsDejaAssembles, $m["id_fk_type_materiel"]) == false) {

					$tabMaterielsAAssembler[] = array(
							"type" => $suffixe,
							"id_materiel" => $m["id_".$suffixe."_materiel"], 
							"nom_type_materiel" => $m["nom_type_materiel"],
							"id_type_materiel" => $m["id_type_materiel"],
							"selected" => "",
							"origine" => $origine,
					);
					break;
				}
			}
		}
	}

	private function estDejaAssembleSurCharrette($materielsDejaAssembles, $idTypeMateriel) {
		$retour = false;
		foreach($materielsDejaAssembles as $m) {
			if ($m["id_fk_type_materiel"] == $idTypeMateriel) {
				$retour = true;
				break;
			}
		}
		return $retour;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		// verification que la valeur recue est bien numerique
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$this->request->get("valeur_1"));
		} else {
			$idMaterielBase = (int)$this->request->get("valeur_1");
		}

		if ($idMaterielBase == -1) {
			throw new Zend_Exception(get_class($this)." Id Matériel base invalide:-1");
		}

		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$this->request->get("valeur_2"));
		} else {
			$idMaterielAAssembler = (int)$this->request->get("valeur_2");
		}

		if ($idMaterielAAssembler == -1) {
			throw new Zend_Exception(get_class($this)." Id Matériel A Assembler invalide:-1");
		}

		// verification qu'il a assez de PA
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PA:".$this->view->user->pa_hobbit);
		}

		// verification qu'il y a assez de castars
		if ($this->view->achatPossibleCastars == false) {
			throw new Zend_Exception(get_class($this)." Achat impossible : castars:".$this->view->user->castars_hobbit." cout:".$this->view->coutCastars);
		}

		$trouve = false;
		$materielBase = null;
		foreach($this->view->materielsBase as $m) {
			if ($m["id_materiel"] == $idMaterielBase) {
				$trouve = true;
				$materielBase = $m;
				break;
			}
		}
		if ($trouve == false) {
			throw new Zend_Exception(get_class($this)." Materiel base invalide");
		}

		$trouve = false;
		$materielAAssembler = null;
		foreach($this->view->materielsAAssembler as $m) {
			if ($m["id_materiel"] == $idMaterielAAssembler) {
				$trouve = true;
				$materielAAssembler = $m;
				break;
			}
		}
		if ($trouve == false) {
			throw new Zend_Exception(get_class($this)." Materiel a assembler invalide");
		}

		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;

		$this->assembler($materielBase, $materielAAssembler);
		$this->majHobbit();
	}

	private function assembler($materielBase, $materielAAssembler) {

		if ($materielBase["type"] == "charrette") {
			$this->assemblerSurCharrette($materielBase, $materielAAssembler);
		} else {
			$this->assemblerSurMateriel($materielBase, $materielAAssembler);
		}

		$this->view->materielBase = $materielBase;
		$this->view->materielAAssembler = $materielAAssembler;
		
		$details = "[h".$this->view->user->id_hobbit."] a assemblé le matériel n°".$materielAAssembler["id_materiel"];
		Zend_Loader::loadClass("Bral_Util_Materiel");
		Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_UTILISER_ID, $materielAAssembler["id_materiel"], $details);
	}

	private function assemblerSurCharrette($materielBase, $materielAAssembler) {
		Zend_Loader::loadClass("CharretteMaterielAssemble");

		$charretteMaterielAssembleTable = new CharretteMaterielAssemble();

		$data = array(
			"id_charrette_materiel_assemble" => $materielBase["id_materiel"],
			"id_materiel_materiel_assemble" => $materielAAssembler["id_materiel"],
		);
		$charretteMaterielAssembleTable->insert($data);
		$this->supprimeMaterielAAsembler($materielAAssembler);

		Zend_Loader::loadClass("Bral_Util_Charrette");
		Bral_Util_Charrette::calculAmeliorationsCharrette($this->view->user->id_hobbit);
	}

	private function supprimeMaterielAAsembler($materielAAssembler) {
		if ($materielAAssembler["type"] == "laban") {
			$table = new LabanMateriel();
			$suffixe = "laban";
		} elseif ($materielAAssembler["type"] == "charrette") {
			$table = new CharretteMateriel();
			$suffixe = "charrette";
		}
		$where = "id_".$suffixe."_materiel = ".$materielAAssembler["id_materiel"];
		$table->delete($where);
	}

	private function assemblerSurMateriel($materielBase, $materielAAssembler) {
		throw new Zend_Exception(get_class($this)." assemblerSurMateriel non implémenté");
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban", "box_charrette"));
	}

	private function calculCoutCastars() {
		return 10;
	}
}