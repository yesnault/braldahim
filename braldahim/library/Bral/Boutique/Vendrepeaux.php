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
class Bral_Boutique_Vendrepeaux extends Bral_Boutique_Boutique {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Vendre des peaux";
	}

	function prepareCommun() {
		throw new Zend_Exception("Boutique fermee");
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('Laban');
		Zend_Loader::loadClass('BoutiquePeau');
		Zend_Loader::loadClass('StockPeau');

		$idSourceCourante = $this->request->get("id_source_courante");

		$selectedLaban = "";
		$selectedCharrette = "";
		if ($idSourceCourante == "laban") {
			$selectedLaban = "selected";
		} else if ($idSourceCourante == "charrette") {
			$selectedCharrette = "selected";
		}
		$tabSourceTransfert[] = array("id_source" => "laban", "texte" => "votre laban", "selected" => $selectedLaban);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$tabSourceTransfert[] = array("id_source" => "charrette", "texte" => "votre charrette", "selected" => $selectedCharrette);
		}
		$this->view->sourceTransfertCourante = $idSourceCourante;
		$this->view->sourceTransfert = $tabSourceTransfert;
		$this->view->charrette = $charrette;

		$this->view->vendrePeauxOk = false;
		$this->prepareCommunRessources();
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->vendrePeauxOk == false) {
			throw new Zend_Exception(get_class($this)." Vendre interdit");
		}

		$this->view->limitePoidsCastars = false;
		$this->view->elementsVendus = "";
		$this->calculPeaux();
	}

	private function prepareCommunRessources() {
		Zend_Loader::loadClass('Bral_Util_BoutiquePeaux');
		$this->view->peauxPrix = Bral_Util_BoutiquePeaux::construireTabStockPrix($this->idRegion);

		if ($this->view->sourceTransfertCourante != null) {
			$this->prepareCommunRessourcesPeaux();
		}
	}

	private function prepareCommunRessourcesPeaux() {
		if ($this->view->sourceTransfertCourante == "charrette") {
			Zend_Loader::loadClass("Charrette");
			$table = new Charrette();
			$peaux = $table->findByIdHobbit($this->view->user->id_hobbit);
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("Laban");
			$table = new Laban();
			$peaux = $table->findByIdHobbit($this->view->user->id_hobbit);
			$suffixe = "laban";
		}

		$nbPeaux = 0;

		if ($peaux != null && count($peaux) == 1) {
			$peaux = $peaux[0];
			if ($peaux["quantite_peau_".$suffixe] > 0) {
				$nbPeaux = $peaux["quantite_peau_".$suffixe];
				$this->view->vendrePeauxOk = true;
			}
		}

		$stockPeauTable = new StockPeau();
		$stockPeauRowset = $stockPeauTable->findDernierStockByIdRegion($this->idRegion);
		if (count($stockPeauRowset) != 1) {
			throw new Zend_Exception(get_class($this)."::count(stockPeauRowset) != 1 :".count($stockPeauRowset));
		}
		$this->view->prixRepriseUnitaire = intval($stockPeauRowset[0]["prix_unitaire_reprise_stock_peau"]);
		$this->view->nb_peaux = $nbPeaux;
	}

	private function calculPeaux() {
		Zend_Loader::loadClass('Laban');

		if ($this->view->sourceTransfertCourante == "charrette") {
			Zend_Loader::loadClass("Charrette");
			$table = new Charrette();
			$suffixe = "charrette";
			$poidsRestant = $this->view->charrette["poids_transportable_charrette"] - $this->view->charrette["poids_transporte_charrette"];
			$poidsTransporte = $this->view->charrete["poids_transporte_charrette"];
			$castars = $this->view->charrette["quantite_castar_charrette"];
		} else {
			Zend_Loader::loadClass("Laban");
			$table = new Laban();
			$suffixe = "laban";
			$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
			$poidsTransporte = $this->view->user->poids_transporte_hobbit;
			$castars = $this->view->user->castars_hobbit;
		}

		$gainsCastars = 0;

		$nb = $this->request->get("valeur_2");

		if ((int) $nb."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." NB Peaux invalide=".$nb);
		} else {
			$nb = (int)$nb;
		}
		if ($nb > $this->view->nb_peaux) {
			throw new Zend_Exception(get_class($this)." NB Peaux interdit=".$nb);
		}

		// Poids restant - le poids de ce qu'on vend
		$poidsRestant = $poidsRestant + ($nb * Bral_Util_Poids::POIDS_PEAU);
		if ($poidsRestant < 0) $poidsRestant = 0;
		$nbCastarsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CASTARS);
		$nbCastarsAGagner = $this->view->prixRepriseUnitaire * $nb;

		if ($nbCastarsAGagner > $nbCastarsPossible) {
			$this->view->limitePoidsCastars = true;
			$nb = 0;
		}

		if ($nb > 0) {

			$gainsCastars = $gainsCastars + ($this->view->prixRepriseUnitaire * $nb);
			$castars = $castars + ($this->view->prixRepriseUnitaire * $nb);
			$poidsTransporte = $poidsTransporte - ($nb * Bral_Util_Poids::POIDS_PEAU) + ($nb * Bral_Util_Poids::POIDS_CASTARS);

			$data = array(
				"quantite_peau_".$suffixe => -$nb,
			);

			if ($this->view->sourceTransfertCourante == "charrette") {
				$data["id_fk_hobbit_charrette"] = $this->view->user->id_hobbit;
			} else {
				$data["id_fk_hobbit_laban"] = $this->view->user->id_hobbit;
			}

			$table->insertOrUpdate($data);

			$data = array(
					"date_achat_boutique_peau" => date("Y-m-d H:i:s"),
					"id_fk_lieu_boutique_peau" => $this->view->idBoutique,
					"id_fk_hobbit_boutique_peau" => $this->view->user->id_hobbit,
					"quantite_peau_boutique_peau" => $nb,
					"prix_unitaire_boutique_peau" => $this->view->prixRepriseUnitaire,
					"id_fk_region_boutique_peau" => $this->idRegion,
					"action_boutique_peau" => "reprise",
			);
			$boutiqueMineraiTable = new BoutiquePeau();
			$boutiqueMineraiTable->insert($data);

			$s = "";
			if ($nb > 1) $s = "x";
			$this->view->elementsVendus .= $nb. " peau".$s;
		}

		if ($this->view->sourceTransfertCourante == "charrette") {
			$this->view->charrette["quantite_castar_charrette"] = $castars;
			$this->view->charrette["poids_transporte_charrette"] = $poidsTransporte; // poids mis à jour dans calculPoidsCharrette
			$charretteTable = new Charrette();
			$data = array(
				"quantite_castar_charrette" => $this->view->charrette["quantite_castar_charrette"] ,
			);
			$where = "id_charrette = ".$this->view->charrette["id_charrette"];
			$charretteTable->update($data, $where);
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		} else {
			$this->view->user->castars_hobbit = $castars;
			$this->view->user->poids_transporte_hobbit = $poidsTransporte;
		}

		$this->view->gainCastars = $gainsCastars;
	}

	function getListBoxRefresh() {
		if ($this->view->destination["id_destination"] == "charrette") {
			$boxToRefresh = "box_charrette";
		} else {
			$boxToRefresh = "box_laban";
		}
		return $this->constructListBoxRefresh(array($boxToRefresh, "box_bpeaux"));
	}
}