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
class Bral_Lieux_Echangeurrune extends Bral_Lieux_Lieu {

	function prepareCommun() {
		$this->view->achatPossibleCastars = false;
		$this->view->tabAAfficher = false;

		Zend_Loader::loadClass("LabanRune");
		$tabLabanRune = null;
		$labanRuneTable = new LabanRune();
		$labanRunes = $labanRuneTable->findByIdBraldun($this->view->user->id_braldun, "oui");

		foreach($labanRunes as $l) {
			if ($l["niveau_type_rune"] == "d" || $l["niveau_type_rune"] == "c") {
				$tabLabanRune[$l["id_type_rune"]]["id_type_rune"] = $l["id_type_rune"];
				$tabLabanRune[$l["id_type_rune"]]["a_afficher"] = false;
				$tabLabanRune[$l["id_type_rune"]]["cout_castars"] = 1000;
				$tabLabanRune[$l["id_type_rune"]]["niveau_type_rune"] = $l["niveau_type_rune"];
				$tabLabanRune[$l["id_type_rune"]]["nom_type_rune"] = $l["nom_type_rune"];
				$tabLabanRune[$l["id_type_rune"]]["image_type_rune"] = $l["image_type_rune"];

				$tabLabanRune[$l["id_type_rune"]]["runes"][] = array(
						"id_rune_laban_rune" => $l["id_rune_laban_rune"],
						"id_fk_type_rune" => $l["id_fk_type_rune"],
						"nom_type_rune" => $l["nom_type_rune"],
						"image_type_rune" => $l["image_type_rune"],
						"effet_type_rune" => $l["effet_type_rune"],
						"id_rune_laban_rune" => $l["id_rune_laban_rune"],
				);
				if (count($tabLabanRune[$l["id_type_rune"]]["runes"]) >= 3) {
					$tabLabanRune[$l["id_type_rune"]]["a_afficher"] = true;
					$tabLabanRune[$l["id_type_rune"]]["achat_possible"] = false;
					$this->view->tabAAfficher = true;

					if ($l["niveau_type_rune"] == "d") {
						$tabLabanRune[$l["id_type_rune"]]["cout_castars"] = 15;
					} else if ($l["niveau_type_rune"] == "c") {
						$tabLabanRune[$l["id_type_rune"]]["cout_castars"] = 21;
					}

					if ($this->view->user->castars_braldun >= $tabLabanRune[$l["id_type_rune"]]["cout_castars"]) {
						$tabLabanRune[$l["id_type_rune"]]["achat_possible"] = true;
						$this->view->achatPossibleCastars = true;
					}
				}
			}
		}
		$this->view->nbLabanRune = count($tabLabanRune);
		$this->view->labanRunes = $tabLabanRune;
	}

	function prepareFormulaire() {

	}

	function prepareResultat() {

		// verification qu'il a assez de PA
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PA:".$this->view->user->pa_braldun);
		}

		// verification que la valeur recue est bien numerique
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$this->request->get("valeur_1"));
		} else {
			$idTypeRune = (int)$this->request->get("valeur_1");
		}

		if (!array_key_exists($idTypeRune, $this->view->labanRunes)) {
			throw new Zend_Exception(get_class($this)." idTypeRune interdit A=".$idTypeRune);
		}

		if ($this->view->labanRunes[$idTypeRune]["achat_possible"] !== true || $this->view->labanRunes[$idTypeRune]["cout_castars"] > $this->view->user->castars_braldun) {
			throw new Zend_Exception(get_class($this)." Achat impossible");
		}

		$this->echange($idTypeRune);
		$this->majBraldun();
	}

	private function echange($idTypeRune) {
		Zend_Loader::loadClass("Bral_Util_Rune");

		$this->view->cout = $this->view->labanRunes[$idTypeRune]["cout_castars"];
		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->cout;

		$texte = "";
		$labanRuneTable = new LabanRune();
		for ($i = 0; $i < 3; $i++) {
			$where = "id_rune_laban_rune = ".(int)$this->view->labanRunes[$idTypeRune]["runes"][$i]["id_rune_laban_rune"];
			$labanRuneTable->delete($where);
			$texte .= " n°".$this->view->labanRunes[$idTypeRune]["runes"][$i]["id_rune_laban_rune"];
			if ($i < 2) {
				$texte .= ",";
			}
			$details = "[h".$this->view->user->id_braldun."] a échangé la rune n°".$this->view->labanRunes[$idTypeRune]["runes"][$i]["id_rune_laban_rune"]. " chez l'échangeur";
			Bral_Util_Rune::insertHistorique(Bral_Util_Rune::HISTORIQUE_CREATION_ID, $this->view->labanRunes[$idTypeRune]["runes"][$i]["id_rune_laban_rune"], $details);
		}

		$niveauRune = "c";
		if ($this->view->labanRunes[$idTypeRune]["niveau_type_rune"] == "c") {
			$niveauRune = "b";
		}

		Zend_Loader::loadClass("TypeRune");
		$typeRuneTable = new TypeRune();
		$typeRuneRowset = $typeRuneTable->findByNiveau($niveauRune);

		if (!isset($typeRuneRowset) || count($typeRuneRowset) == 0) {
			throw new Zend_Exception(get_class($this)." niveauRune invalide:".$niveauRune);
		}

		$nbType = count($typeRuneRowset);
		$numeroRune = Bral_Util_De::get_de_specifique(0, $nbType-1);

		$typeRune = $typeRuneRowset[$numeroRune];

		Zend_Loader::loadClass("IdsRune");
		$idsRuneTable = new IdsRune();
		$idRune = $idsRuneTable->prepareNext();

		Zend_Loader::loadClass("Rune");
		$runeTable = new Rune();
		$dataRune = array (
			"id_rune" => $idRune,
			"id_fk_type_rune" => $typeRune["id_type_rune"],
			"est_identifiee_rune" => "non",
		);
		$runeTable->insert($dataRune);

		$labanRuneTable = new LabanRune();
		$dataLaban = array (
			"id_rune_laban_rune" => $idRune,
			"id_fk_braldun_laban_rune" => $this->view->user->id_braldun,
		);
		$labanRuneTable->insert($dataLaban);

		$this->view->texte = $texte;

		$details = "L'échangeur de rune a donné la rune n°".$idRune. " à [h".$this->view->user->id_braldun."]";
		Bral_Util_Rune::insertHistorique(Bral_Util_Rune::HISTORIQUE_CREATION_ID, $idRune, $details);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban"));
	}

}