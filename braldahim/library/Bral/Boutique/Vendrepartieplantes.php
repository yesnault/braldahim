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
class Bral_Boutique_Vendrepartieplantes extends Bral_Boutique_Boutique {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Vendre des plantes";
	}

	function prepareCommun() {
		throw new Zend_Exception("Boutique fermee");
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('Laban');

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

		$this->view->vendrePartieplantesOk = false;
		$this->prepareCommunRessources();
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->vendrePartieplantesOk == false) {
			throw new Zend_Exception(get_class($this)." Retirer interdit");
		}

		$this->view->limitePoidsCastars = false;
		$this->view->elementsVendus = "";
		$this->calculPartiesPlantes();
		if ($this->view->elementsVendus != "") {
			$this->view->elementsVendus = mb_substr($this->view->elementsVendus, 0, -2);
		}
	}

	private function prepareCommunRessources() {
		Zend_Loader::loadClass("LabanPartieplante");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass('Bral_Util_BoutiquePlantes');
		Zend_Loader::loadClass('BoutiquePartieplante');

		$regionTable = new Region();
		$idRegion = $regionTable->findIdRegionByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$this->view->typePlantes = Bral_Util_BoutiquePlantes::construireTabPrix(true, $idRegion);

		$this->view->nb_valeurs = 1;
		$this->view->nb_partiePlantes = 0;

		if ($this->view->sourceTransfertCourante != null) {
			$this->prepareCommunRessourcesPartieplante();
		}
	}

	private function prepareCommunRessourcesPartieplante() {
		if ($this->view->sourceTransfertCourante == "charrette") {
			Zend_Loader::loadClass("CharrettePartieplante");
			$table = new CharrettePartieplante();
			$partiePlantes = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanPartieplante");
			$table = new LabanPartieplante();
			$partiePlantes = $table->findByIdHobbit($this->view->user->id_hobbit);
			$suffixe = "laban";
		}

		$tabPartiePlantes = null;

		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				for ($i = 2; $i <= $this->view->typePlantes["nb_valeurs"] + 1; $i++) {
					if ($p["quantite_".$suffixe."_partieplante"] > 0 && $p["id_fk_type_".$suffixe."_partieplante"] == $this->view->typePlantes["valeurs"]["valeur_".$i]["id_type_partieplante"] && $p["id_fk_type_plante_".$suffixe."_partieplante"] == $this->view->typePlantes["valeurs"]["valeur_".$i]["id_type_plante"]) {
						$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brute
						$tabPartiePlantes[$this->view->nb_valeurs] = array(
							"nom_type" => $p["nom_type_partieplante"],
							"nom_plante" => $p["nom_type_plante"],
							"id_fk_type_".$suffixe."_partieplante" => $p["id_fk_type_".$suffixe."_partieplante"],
							"id_fk_type_plante_".$suffixe."_partieplante" => $p["id_fk_type_plante_".$suffixe."_partieplante"],
							"quantite_".$suffixe."_partieplante" => $p["quantite_".$suffixe."_partieplante"],
							"quantite_preparee_".$suffixe."_partieplante" => $p["quantite_preparee_".$suffixe."_partieplante"],
							"indice_valeur" => $this->view->nb_valeurs,
							"prixUnitaireReprise" => $this->view->typePlantes["valeurs"]["valeur_".$i]["prixUnitaireReprise"],
						);
						$this->view->vendrePartieplantesOk = true;
						$this->view->nb_partiePlantes = $this->view->nb_partiePlantes + $p["quantite_".$suffixe."_partieplante"];
						break;
					}
				}
			}
		}
		$this->view->partieplantes = $tabPartiePlantes;
	}

	private function calculPartiesPlantes() {
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("LabanPartieplante");

		$echoppePartiePlanteTable = new EchoppePartieplante();

		if ($this->view->sourceTransfertCourante == "charrette") {
			Zend_Loader::loadClass("CharrettePartieplante");
			$table = new CharrettePartieplante();
			$suffixe = "charrette";
			$poidsRestant = $this->view->charrette["poids_transportable_charrette"] - $this->view->charrette["poids_transporte_charrette"];
			$poidsTransporte = $this->view->charrete["poids_transporte_charrette"];
			$castars = $this->view->charrette["quantite_castar_charrette"];
		} else {
			Zend_Loader::loadClass("LabanPartieplante");
			$table = new LabanPartieplante();
			$suffixe = "laban";
			$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
			$poidsTransporte = $this->view->user->poids_transporte_hobbit;
			$castars = $this->view->user->castars_hobbit;
		}

		$gainsCastars = 0;

		for ($i=2; $i<=$this->view->nb_valeurs; $i++) {
			$indice = $i;
			$nbBrutes = $this->request->get("valeur_".$indice);

			if ((int) $nbBrutes."" != $this->request->get("valeur_".$indice)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute invalide(".$indice.")=".$nbBrutes);
			} else {
				$nbBrutes = (int)$nbBrutes;
			}
			if ($nbBrutes > $this->view->partieplantes[$indice]["quantite_".$suffixe."_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute interdit(".$indice.")=".$nbBrutes);
			}

			// Poids restant - le poids de ce qu'on vend
			$nbCastarsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CASTARS);
			$nbCastarsAGagner = $this->view->partieplantes[$indice]["prixUnitaireReprise"] * $nbBrutes;

			if ($nbCastarsAGagner > $nbCastarsPossible) {
				$this->view->limitePoidsCastars = true;
				$nbBrutes = 0;
			}

			if ($nbBrutes > 0) {

				$gainsCastars = $gainsCastars + ($this->view->partieplantes[$indice]["prixUnitaireReprise"] * $nbBrutes);
				$castars = $castars + ($this->view->partieplantes[$indice]["prixUnitaireReprise"] * $nbBrutes);
				$poidsTransporte = $poidsTransporte - ($nbBrutes * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE) + ($nbBrutes * Bral_Util_Poids::POIDS_CASTARS);

				$data = array(
					"id_fk_type_".$suffixe."_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_".$suffixe."_partieplante"],
					"id_fk_type_plante_".$suffixe."_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_plante_".$suffixe."_partieplante"],
					"quantite_".$suffixe."_partieplante" => -$nbBrutes,
				);

				if ($this->view->sourceTransfertCourante == "charrette") {
					$data["id_fk_charrette_partieplante"] = $this->view->charrette["id_charrette"];
				} else {
					$data["id_fk_hobbit_laban_partieplante"] = $this->view->user->id_hobbit;
				}

				$table->insertOrUpdate($data);

				$data = array(
					"date_achat_boutique_partieplante" => date("Y-m-d H:i:s"),
					"id_fk_type_boutique_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_".$suffixe."_partieplante"],
					"id_fk_type_plante_boutique_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_plante_".$suffixe."_partieplante"],
					"id_fk_lieu_boutique_partieplante" => $this->view->idBoutique,
					"id_fk_hobbit_boutique_partieplante" => $this->view->user->id_hobbit,
					"quantite_brut_boutique_partieplante" => $nbBrutes,
					"prix_unitaire_boutique_partieplante" => $this->view->partieplantes[$indice]["prixUnitaireReprise"],
					"id_fk_region_boutique_partieplante" => $this->idRegion,
					"action_boutique_partieplante" => "reprise",
				);
				$boutiquePartieplanteTable = new BoutiquePartieplante();
				$boutiquePartieplanteTable->insert($data);

				$sbrute = "";
				if ($nbBrutes > 1) $sbrute = "s";
				$this->view->elementsVendus .= $this->view->partieplantes[$indice]["nom_plante"]. " : ";
				$this->view->elementsVendus .= $nbBrutes. " ".$this->view->partieplantes[$indice]["nom_type"]. $sbrute." brute".$sbrute;
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
		return $this->constructListBoxRefresh(array($boxToRefresh, "box_bpartieplantes"));
	}
}