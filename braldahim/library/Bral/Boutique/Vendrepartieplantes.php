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
		Zend_Loader::loadClass('Laban');
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
		
		$tabPartiePlantes = null;
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$this->view->nb_valeurs = 0;
		$this->view->nb_partiePlantes = 0;
		
		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				for ($i = 1; $i <= $this->view->typePlantes["nb_valeurs"]; $i++) {
					if ($p["quantite_laban_partieplante"] > 0 && $p["id_fk_type_laban_partieplante"] == $this->view->typePlantes["valeurs"]["valeur_".$i]["id_type_partieplante"] && $p["id_fk_type_plante_laban_partieplante"] == $this->view->typePlantes["valeurs"]["valeur_".$i]["id_type_plante"]) {
							$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brute
							$tabPartiePlantes[$this->view->nb_valeurs] = array(
							"nom_type" => $p["nom_type_partieplante"],
							"nom_plante" => $p["nom_type_plante"],
							"id_fk_type_laban_partieplante" => $p["id_fk_type_laban_partieplante"],
							"id_fk_type_plante_laban_partieplante" => $p["id_fk_type_plante_laban_partieplante"],
							"id_fk_hobbit_laban_partieplante" => $p["id_fk_hobbit_laban_partieplante"],
							"quantite_laban_partieplante" => $p["quantite_laban_partieplante"],
							"quantite_preparee_laban_partieplante" => $p["quantite_preparee_laban_partieplante"],
							"indice_valeur" => $this->view->nb_valeurs,
							"prixUnitaireReprise" => $this->view->typePlantes["valeurs"]["valeur_".$i]["prixUnitaireReprise"],
						);
						$this->view->vendrePartieplantesOk = true;
						$this->view->nb_partiePlantes = $this->view->nb_partiePlantes + $p["quantite_laban_partieplante"];
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
		$labanPartiePlanteTable = new LabanPartieplante();
		
		for ($i=1; $i<=$this->view->nb_valeurs; $i++) {
			$indice = $i;
			$nbBrutes = $this->request->get("valeur_".$indice);
			
			if ((int) $nbBrutes."" != $this->request->get("valeur_".$indice)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute invalide(".$indice.")=".$nbBrutes);
			} else {
				$nbBrutes = (int)$nbBrutes;
			}
			if ($nbBrutes > $this->view->partieplantes[$indice]["quantite_laban_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute interdit(".$indice.")=".$nbBrutes);
			}
			
			// Poids restant - le poids de ce qu'on vend
			$poidsRestant = $this->view->user->poids_transportable_hobbit - ($this->view->user->poids_transporte_hobbit - ($nbBrutes * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE));
			if ($poidsRestant < 0) $poidsRestant = 0;
			$nbCastarsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CASTARS);
			$nbCastarsAGagner = $this->view->partieplantes[$indice]["prixUnitaireReprise"] * $nbBrutes;
			
			if ($nbCastarsAGagner > $nbCastarsPossible) {
				$this->view->limitePoidsCastars = true;
				$nbBrutes = 0;
			}
			
			if ($nbBrutes > 0) {
				
				$this->view->user->castars_hobbit = $this->view->user->castars_hobbit + ($this->view->partieplantes[$indice]["prixUnitaireReprise"] * $nbBrutes);
				$this->view->user->poids_transporte_hobbit = $this->view->user->poids_transporte_hobbit - ($nbBrutes * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE) + ($nbBrutes * Bral_Util_Poids::POIDS_CASTARS);
				
				$data = array(
					'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_laban_partieplante"],
					'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_laban_partieplante"],
					'id_fk_hobbit_laban_partieplante' => $this->view->user->id_hobbit,
					'quantite_laban_partieplante' => -$nbBrutes,
				);
				$labanPartiePlanteTable->insertOrUpdate($data);
				
				$data = array(
					"date_achat_boutique_partieplante" => date("Y-m-d H:i:s"),
					"id_fk_type_boutique_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_laban_partieplante"],
					"id_fk_type_plante_boutique_partieplante" => $this->view->partieplantes[$indice]["id_fk_type_plante_laban_partieplante"],
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
		}
	}
	
	function getListBoxRefresh() {
		return array("box_laban", "box_profil", "box_evenements", "box_bpartieplantes");
	}
}