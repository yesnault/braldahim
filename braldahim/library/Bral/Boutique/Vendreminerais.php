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
class Bral_Boutique_Vendreminerais extends Bral_Boutique_Boutique {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Vendre du minerai";
	}

	function prepareCommun() {
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('Laban');
		Zend_Loader::loadClass('BoutiqueMinerai');

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

		$this->view->vendreMineraisOk = false;
		$this->prepareCommunRessources();
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->vendreMineraisOk == false) {
			throw new Zend_Exception(get_class($this)." Vendre interdit");
		}

		$this->view->limitePoidsCastars = false;
		$this->view->elementsVendus = "";
		$this->calculMinerais();
		if ($this->view->elementsVendus != "") {
			$this->view->elementsVendus = mb_substr($this->view->elementsVendus, 0, -2);
		}
	}

	private function prepareCommunRessources() {
		Zend_Loader::loadClass('Bral_Util_BoutiqueMinerais');

		$this->view->mineraisPrix = Bral_Util_BoutiqueMinerais::construireTabPrix(true, $this->idRegion);

		$this->view->nb_valeurs = 1;

		if ($this->view->sourceTransfertCourante != null) {
			$this->prepareCommunRessourcesMinerais();
		}
	}

	private function prepareCommunRessourcesMinerais() {
		if ($this->view->sourceTransfertCourante == "charrette") {
			Zend_Loader::loadClass("CharretteMinerai");
			$table = new CharretteMinerai();
			$minerais = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanMinerai");
			$table = new LabanMinerai();
			$minerais = $table->findByIdHobbit($this->view->user->id_hobbit);
			$suffixe = "laban";
		}

		$tabMinerais = null;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				foreach($this->view->mineraisPrix as $mp) {
					if ($m["quantite_brut_".$suffixe."_minerai"] > 0 && $m["id_fk_type_".$suffixe."_minerai"] == $mp["id_type_minerai"]) {
						$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brut
						$tabMinerais[$this->view->nb_valeurs] = array(
							"type" => $m["nom_type_minerai"],
							"id_fk_type_".$suffixe."_minerai" => $m["id_fk_type_".$suffixe."_minerai"],
							"quantite_brut_".$suffixe."_minerai" => $m["quantite_brut_".$suffixe."_minerai"],
							"quantite_lingots_".$suffixe."_minerai" => $m["quantite_lingots_".$suffixe."_minerai"],
							"prixUnitaireReprise" => $mp["prixUnitaireReprise"],
							"indice_valeur" => $this->view->nb_valeurs,
						);
						$this->view->vendreMineraisOk = true;
						$this->view->nb_minerai_brut = $this->view->nb_minerai_brut + $m["quantite_brut_".$suffixe."_minerai"];
						break;
					}
				}
			}
		}
		$this->view->minerais = $tabMinerais;
	}

	private function calculMinerais() {
		Zend_Loader::loadClass('LabanMinerai');

		if ($this->view->sourceTransfertCourante == "charrette") {
			Zend_Loader::loadClass("CharretteMinerai");
			$table = new CharretteMinerai();
			$suffixe = "charrette";
			$poidsRestant = $this->view->charrette["poids_transportable_charrette"] - $this->view->charrette["poids_transporte_charrette"];
			$poidsTransporte = $this->view->charrete["poids_transporte_charrette"];
			$castars = $this->view->charrette["quantite_castar_charrette"];
		} else {
			Zend_Loader::loadClass("LabanMinerai");
			$table = new LabanMinerai();
			$suffixe = "laban";
			$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
			$poidsTransporte = $this->view->user->poids_transporte_hobbit;
			$castars = $this->view->user->castars_hobbit;
		}

		$gainsCastars = 0;
		
		for ($i=2; $i<=$this->view->nb_valeurs; $i++) {
			$indice = $i;
			$nbBrut = $this->request->get("valeur_".$indice);

			if ((int) $nbBrut."" != $this->request->get("valeur_".$indice)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai brut invalide=".$nbBrut. " indice=".$indice);
			} else {
				$nbBrut = (int)$nbBrut;
			}
			if ($nbBrut > $this->view->minerais[$indice]["quantite_brut_".$suffixe."_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai brut interdit=".$nbBrut);
			}

			// Poids restant - le poids de ce qu'on vend
			$poidsRestant = $poidsRestant + ($nbBrut * Bral_Util_Poids::POIDS_MINERAI);
			if ($poidsRestant < 0) $poidsRestant = 0;
			$nbCastarsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CASTARS);
			$nbCastarsAGagner = $this->view->minerais[$indice]["prixUnitaireReprise"] * $nbBrut;

			if ($nbCastarsAGagner > $nbCastarsPossible) {
				$this->view->limitePoidsCastars = true;
				$nbBrut = 0;
			}

			if ($nbBrut > 0) {

				$gainsCastars = $gainsCastars + ($this->view->minerais[$indice]["prixUnitaireReprise"] * $nbBrut);
				$castars = $castars + ($this->view->minerais[$indice]["prixUnitaireReprise"] * $nbBrut);
				$poidsTransporte = $poidsTransporte - ($nbBrut * Bral_Util_Poids::POIDS_MINERAI) + ($nbBrut * Bral_Util_Poids::POIDS_CASTARS);

				$data = array(
					"id_fk_type_".$suffixe."_minerai" => $this->view->minerais[$indice]["id_fk_type_".$suffixe."_minerai"],
					"quantite_brut_".$suffixe."_minerai" => -$nbBrut,
				);

				if ($this->view->sourceTransfertCourante == "charrette") {
					$data["id_fk_charrette_minerai"] = $this->view->charrette["id_charrette"];
				} else {
					$data["id_fk_hobbit_laban_minerai"] = $this->view->user->id_hobbit;
				}

				$table->insertOrUpdate($data);

				$data = array(
					"date_achat_boutique_minerai" => date("Y-m-d H:i:s"),
					"id_fk_type_boutique_minerai" => $this->view->minerais[$indice]["id_fk_type_".$suffixe."_minerai"],
					"id_fk_lieu_boutique_minerai" => $this->view->idBoutique,
					"id_fk_hobbit_boutique_minerai" => $this->view->user->id_hobbit,
					"quantite_brut_boutique_minerai" => $nbBrut,
					"prix_unitaire_boutique_minerai" => $this->view->minerais[$indice]["prixUnitaireReprise"],
					"id_fk_region_boutique_minerai" => $this->idRegion,
					"action_boutique_minerai" => "reprise",
				);
				$boutiqueMineraiTable = new BoutiqueMinerai();
				$boutiqueMineraiTable->insert($data);

				$sbrut = "";
				if ($nbBrut > 1) $sbrut = "s";
				$this->view->elementsVendus .= $this->view->minerais[$indice]["type"]. " : ".$nbBrut. " minerai".$sbrut." brut".$sbrut;
				$this->view->elementsVendus .= ", ";
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
		}
		
		$this->view->gainCastars = $gainsCastars;
	}

	function getListBoxRefresh() {
		if ($this->view->destination["id_destination"] == "charrette") {
			$boxToRefresh = "box_charrette";
		} else {
			$boxToRefresh = "box_laban";
		}
		return $this->constructListBoxRefresh(array($boxToRefresh, "box_bminerais"));
	}
}