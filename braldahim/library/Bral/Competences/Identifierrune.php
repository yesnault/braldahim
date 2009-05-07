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
class Bral_Competences_Identifierrune extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('LabanRune');
		// on verifie que le hobbit possede au moins une rune
		
		$tabRunes = null;
		$labanRuneTable = new LabanRune();
		$runes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit, 'non');
		
		if (count($runes) == 0) {
			$this->view->identifierRuneOk = false;
			return;
		}
		
		$this->view->identifierRuneOk = true;
		foreach ($runes as $r) {
			$tabRunes[] = array(
				"id_rune" => $r["id_rune_laban_rune"],
				"type" => $r["nom_type_rune"],
				"sagesse_type_rune" => $r["sagesse_type_rune"],
				"image" => $r["image_type_rune"],
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
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}
		
		// Verification abattre arbre
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
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	/*
	 * jet de SAG > SAG de la rune
	 * En cas de jet inferieur mettre une échelle afin de donner un ordre d'idee au joueur
	 * s'il peut l'identifier ou si le niveau de sagesse est vraiment trop important pour lui. 
	 */
	private function calculIdentifierRune($rune) {
		$jetHobbit = 0;
		for ($i = 1; $i <= $this->view->config->game->base_sagesse + $this->view->user->sagesse_base_hobbit; $i++) {
			$jetHobbit = $jetHobbit + Bral_Util_De::get_1d6();
		}
		$this->view->jetHobbit = $jetHobbit + $this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit;
		
		if ($this->view->jetHobbit >= $rune["sagesse_type_rune"]) {
			$this->view->identificationReussieOk = true;
		} else {
			$this->view->identificationReussieOk  = false;
			if ($this->view->jetHobbit < $rune["sagesse_type_rune"] / 2) {
				$this->view->jetHobbitLoin = true;
			} else {
				$this->view->jetHobbitLoin = false;
			}
			return;
		}
		$this->view->rune = $rune;
		
		$labanRuneTable = new LabanRune();
		$data["est_identifiee_laban_rune"] = 'oui';
		$where = 'id_rune_laban_rune = '.$rune["id_rune"];
		$labanRuneTable->update($data, $where);
	}
	
	public function calculPx() {
		parent::calculPx();
		$this->view->calcul_px_generique = false;
		if ($this->view->identificationReussieOk === true) {
			$this->view->nb_px_perso = $this->view->nb_px_perso + 1;
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_communes", "box_laban"));
	}
}
