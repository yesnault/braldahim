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
		Zend_Loader::loadClass('Laban');
		Zend_Loader::loadClass('BoutiqueMinerai');
		
		$this->view->deposerRessourcesOk = false;
		$this->prepareCommunRessources();
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->vendreMineraisOk == false) {
			throw new Zend_Exception(get_class($this)." Retirer interdit");
		}
		
		$this->view->limitePoidsCastars = false;
		$this->view->elementsVendus = "";
		$this->calculMinerais();
		if ($this->view->elementsVendus != "") {
			$this->view->elementsVendus = mb_substr($this->view->elementsVendus, 0, -2);
		}
	}
	
	private function prepareCommunRessources() {
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass('Bral_Util_BoutiqueMinerais');

		$tabMinerais = null;
		$labanMineraiTable = new labanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);

		$this->view->mineraisPrix = Bral_Util_BoutiqueMinerais::construireTabPrix(true, $this->idRegion);
		
		$this->view->nb_valeurs = 0;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				foreach($this->view->mineraisPrix as $mp) {
					if ($m["quantite_brut_laban_minerai"] > 0 && $m["id_fk_type_laban_minerai"] == $mp["id_type_minerai"]) {
						$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brut
						$tabMinerais[$this->view->nb_valeurs] = array(
							"type" => $m["nom_type_minerai"],
							"id_fk_type_laban_minerai" => $m["id_fk_type_laban_minerai"],
							"id_fk_hobbit_laban_minerai" => $m["id_fk_hobbit_laban_minerai"],
							"quantite_brut_laban_minerai" => $m["quantite_brut_laban_minerai"],
							"quantite_lingots_laban_minerai" => $m["quantite_lingots_laban_minerai"],
							"prixUnitaireReprise" => $mp["prixUnitaireReprise"],
							"indice_valeur" => $this->view->nb_valeurs,
						);
						$this->view->vendreMineraisOk = true;
						$this->view->nb_minerai_brut = $this->view->nb_minerai_brut + $m["quantite_brut_laban_minerai"];
						break;
					}
				}
			}
		}

		$this->view->minerais = $tabMinerais;
		
	}
	
	private function calculMinerais() {
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass('LabanMinerai');
		
		$echoppeMineraiTable = new EchoppeMinerai();
		$labanMineraiTable = new LabanMinerai();
		
		for ($i=1; $i<=$this->view->nb_valeurs; $i++) {
			$indice = $i;
			$nbBrut = $this->request->get("valeur_".$indice);
			
			if ((int) $nbBrut."" != $this->request->get("valeur_".$indice)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai brut invalide=".$nbBrut. " indice=".$indice);
			} else {
				$nbBrut = (int)$nbBrut;
			}
			if ($nbBrut > $this->view->minerais[$indice]["quantite_brut_laban_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai brut interdit=".$nbBrut);
			}
			
			// Poids restant - le poids de ce qu'on vend
			$poidsRestant = $this->view->user->poids_transportable_hobbit - ($this->view->user->poids_transporte_hobbit - ($nbBrut * Bral_Util_Poids::POIDS_MINERAI));
			if ($poidsRestant < 0) $poidsRestant = 0;
			$nbCastarsPossible = floor($poidsRestant / Bral_Util_Poids::POIDS_CASTARS);
			$nbCastarsAGagner = $this->view->minerais[$indice]["prixUnitaireReprise"] * $nbBrut;
			
			if ($nbCastarsAGagner > $nbCastarsPossible) {
				$this->view->limitePoidsCastars = true;
				$nbBrut = 0;
			}
			
			if ($nbBrut > 0) {
				
				$this->view->user->castars_hobbit = $this->view->user->castars_hobbit + ($this->view->minerais[$indice]["prixUnitaireReprise"] * $nbBrut);
				$this->view->user->poids_transporte_hobbit = $this->view->user->poids_transporte_hobbit - ($nbBrut * Bral_Util_Poids::POIDS_MINERAI) + ($nbBrut * Bral_Util_Poids::POIDS_CASTARS);
				
				$data = array(
					'id_fk_type_laban_minerai' => $this->view->minerais[$indice]["id_fk_type_laban_minerai"],
					'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
					'quantite_brut_laban_minerai' => -$nbBrut,
				);
		
				$labanMineraiTable->insertOrUpdate($data);
				
				$data = array(
					"date_achat_boutique_minerai" => date("Y-m-d H:i:s"),
					"id_fk_type_boutique_minerai" => $this->view->minerais[$indice]["id_fk_type_laban_minerai"],
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
		}
	}
	
	function getListBoxRefresh() {
		return array("box_laban", "box_charrette", "box_profil", "box_evenements", "box_bminerais");
	}
}