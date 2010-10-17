<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Identifierrune extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('LabanRune');
		// on verifie que le braldun possede au moins une rune

		$tabRunes = null;
		$labanRuneTable = new LabanRune();
		$runes = $labanRuneTable->findByIdBraldun($this->view->user->id_braldun, 'non');

		foreach ($runes as $r) {
			$tabRunes[] = array(
				"id_rune" => $r["id_rune_laban_rune"],
				"type" => $r["nom_type_rune"],
				"sagesse_type_rune" => $r["sagesse_type_rune"],
				"image" => $r["image_type_rune"],
				"id_braldun_possesseur" => null,
			);
		}

		// Récupération des runes identifiables dans les laban des autres braldûn présents sur la case
		$runes = $labanRuneTable->findNonIdentifieeByIdIdentifieurBraldun($this->view->user->id_braldun, $this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		foreach ($runes as $r) {
			$tabRunes[] = array(
				"id_rune" => $r["id_rune_laban_rune"],
				"type" => $r["nom_type_rune"],
				"sagesse_type_rune" => $r["sagesse_type_rune"],
				"image" => $r["image_type_rune"],
				"id_braldun_possesseur" => $r["id_braldun"],
				"nom_braldun_possesseur" => $r["nom_braldun"],
				"prenom_braldun_possesseur" => $r["prenom_braldun"],
			);
		}

		if (count($tabRunes) == 0) {
			$this->view->identifierRuneOk = false;
			return;
		} else {
			$this->view->identifierRuneOk = true;
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

		if ($this->view->identifierRuneOk == false) {
			throw new Zend_Exception(get_class($this)." Identifier Rune : pas de rune");
		}

		$idRune = intval($this->request->get("valeur_1"));

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
			throw new Zend_Exception(get_class($this)." Identifier Rune : rune invalide:".$idRune);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculIdentifierRune($rune);
		}

		$this->view->rune = $rune;
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	/*
	 * jet de SAG > SAG de la rune
	 * En cas de jet inferieur mettre une échelle afin de donner un ordre d'idee au joueur
	 * s'il peut l'identifier ou si le niveau de sagesse est vraiment trop important pour lui.
	 */
	private function calculIdentifierRune($rune) {
		$jetBraldun = Bral_Util_De::getLanceDe6($this->view->config->game->base_sagesse + $this->view->user->sagesse_base_braldun);
		$this->view->jetBraldun = $jetBraldun + $this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun;

		if ($this->view->jetBraldun > $rune["sagesse_type_rune"]) {
			$this->view->identificationReussieOk = true;
		} else {
			$this->view->identificationReussieOk  = false;
			if ($this->view->jetBraldun <= $rune["sagesse_type_rune"] / 2) {
				$this->view->jetBraldunLoin = true;
			} else {
				$this->view->jetBraldunLoin = false;
			}
			return;
		}
		$this->view->rune = $rune;

		Zend_Loader::loadClass("Rune");
		$runeTable = new Rune();
		$data["est_identifiee_rune"] = 'oui';
		$where = 'id_rune = '.$rune["id_rune"];
		$runeTable->update($data, $where);

		$labanRuneTable = new LabanRune();
		$data = null;
		$data["id_fk_braldun_identification_laban_rune"] = null;
		$where = 'id_rune_laban_rune = '.$rune["id_rune"];
		$labanRuneTable->update($data, $where);
		
		$details = "[b".$this->view->user->id_braldun."] a identifié la rune n°".$rune["id_rune"];
		Zend_Loader::loadClass("Bral_Util_Rune");
		Bral_Util_Rune::insertHistorique(Bral_Util_Rune::HISTORIQUE_IDENTIFIER_ID, $rune["id_rune"], $details);

		if ($rune["id_braldun_possesseur"] != null) {
			Zend_Loader::loadClass("Bral_Util_Messagerie");
			$message = "[Ceci est un message automatique d'identification de rune]".PHP_EOL;
			$message .= $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun. " a identifié votre rune n°".$rune["id_rune"]." présente dans votre laban.".PHP_EOL;
			$message .= "C'est une rune de type ".$rune["type"].".".PHP_EOL;
			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $rune["id_braldun_possesseur"], $message, $this->view);
		}
	}

	public function calculPx() {
		if ($this->view->nbGainCommunParDlaOk === true) {
			parent::calculPx();
			$this->view->calcul_px_generique = false;
			if ($this->view->identificationReussieOk === true) {
				$this->view->nb_px_perso = $this->view->nb_px_perso + 1;
			}
			$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_communes", "box_laban"));
	}
}
