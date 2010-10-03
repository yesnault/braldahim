<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Demandeidentificationrune extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('LabanRune');
		// on verifie que le braldun possede au moins une rune dans son laban

		$tabRunes = null;
		$labanRuneTable = new LabanRune();
		$runes = $labanRuneTable->findByIdBraldun($this->view->user->id_braldun, 'non', null, true);

		if (count($runes) == 0) {
			$this->view->nbRuneOk = false;
			return;
		}

		$this->view->nbRuneOk = true;
		foreach ($runes as $r) {
			$tabRunes[] = array(
				"id_rune" => $r["id_rune_laban_rune"],
				"type" => $r["nom_type_rune"],
				"sagesse_type_rune" => $r["sagesse_type_rune"],
				"image" => $r["image_type_rune"],
				"id_identification_braldun" => $r["id_braldun"],
				"prenom_identification_braldun" => $r["prenom_braldun"],
				"nom_identification_braldun" => $r["nom_braldun"],
			);
		}
		$this->view->runes = $tabRunes;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		if ($this->view->nbRuneOk == false) {
			throw new Zend_Exception(get_class($this)." Demandeidentificationrune : pas de rune");
		}

		$idRune = intval($this->request->get("valeur_1"));
		$idBraldun = intval($this->request->get("valeur_2"));
		if ($idBraldun == -1 || $idBraldun == $this->view->user->id_braldun) {
			$idBraldun = null;
		}

		// on regarde si la rune choisie fait bien partie des runes a identifier
		$runeValide = false;
		$rune = null;
		foreach($this->view->runes as $r) {
			if ($r["id_rune"] == $idRune) {
				$rune = $r;
				$runeValide = true;
				break;
			}
		}
		if ($runeValide == false || $rune == null) {
			throw new Zend_Exception(get_class($this)." Demandeidentificationrune B : rune invalide:".$idRune);
		}

		$this->calcul($rune, $idBraldun);
	}

	private function calcul($rune, $idBraldun) {
		Zend_Loader::loadClass("LabanRune");
		$labanRuneTable = new LabanRune();
		$data["id_fk_braldun_identification_laban_rune"] = $idBraldun;
		$where = 'id_rune_laban_rune = '.$rune["id_rune"];
		$labanRuneTable->update($data, $where);

		$runes = $labanRuneTable->findByIdBraldun($this->view->user->id_braldun, 'non', null, true, $rune["id_rune"]);
		if (count($runes) != 1) {
			throw new Zend_Exception(get_class($this)." Demandeidentificationrune C : rune invalide:".$rune["id_rune"]);
		}
		$this->view->rune = $runes[0];

		if ($idBraldun != null) {
			Zend_Loader::loadClass("Bral_Util_Messagerie");
			$message = "[Ceci est un message automatique de demande d'identification de rune]".PHP_EOL;
			$message .= $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun. " vous a proposé d'identifier la rune n°".$rune["id_rune"]." présente dans son laban.".PHP_EOL;
			$message .= "Vous pouvez tenter une identification de cette rune en utilisant la compétence \"Identification des runes\"".PHP_EOL;
			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $idBraldun, $message, $this->view);
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban"));
	}
}
