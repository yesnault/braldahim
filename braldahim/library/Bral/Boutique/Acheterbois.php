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
class Bral_Boutique_Acheterbois extends Bral_Boutique_Boutique {
	
	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter du bois";
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Bral_Util_BoutiqueBois");
		
		$this->preparePrix();
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}
		if ($this->view->possedeCharrette !== true) {
			throw new Zend_Exception(get_class($this)."::pas de charrette");
		}
		if ($this->view->acheterPossible !== true) {
			throw new Zend_Exception(get_class($this)."::achat impossible");
		}
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Nombre invalide : ".$this->request->get("valeur_1"));
		} else {
			$this->view->quantiteAchetee = (int)$this->request->get("valeur_1");
		}
		
		if ($this->view->quantiteAchetee > $this->view->nombreMaximum) {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Nombre invalide : ".$nombre. " max:".$this->view->nombreMaximum);
		}
		
		if ($this->view->quantiteAchetee == 0) {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Nombre invalide 0");
		}
		
		$this->transfert();
	}
	
	private function preparePrix() {
		
		Zend_Loader::loadClass("Region");
		
		$regionTable = new Region();
		$idRegion = $regionTable->findIdRegionByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$tabStockPrix = Bral_Util_BoutiqueBois::construireTabStockPrix($idRegion);
		if ($tabStockPrix == null || count($tabStockPrix) != 1) {
			Bral_Util_Log::erreur()->err("Bral_Box_Bbois - Erreur de prix dans la table stock_bois, id_region=".$idRegion);
			throw new Zend_Exception(get_class($this)."::Erreur de prix dans la table stock_bois, id_region=".$idRegion);
		}
		$this->view->tabStockPrix = $tabStockPrix[0];
		
		$this->view->prixUnitaire = floor($this->view->tabStockPrix["prix_unitaire_vente_stock_bois"]);
		$this->view->nombreMaximum = floor($this->view->user->castars_hobbit / $this->view->tabStockPrix["prix_unitaire_vente_stock_bois"]);
		
		$this->view->placeDisponible = true;
		
		if ($this->view->nombreMaximum < 1 || $this->view->assezDePa == false) {
			$this->view->acheterPossible = false;
		} else {
			$this->view->acheterPossible = true;
			
			$this->view->tabPoidsRondinsCharrette = Bral_Util_Poids::calculPoidsCharretteTransportable($this->view->user->id_hobbit, $this->view->user->vigueur_base_hobbit);
			if ($this->view->tabPoidsRondinsCharrette != null) {
				$this->view->acheterPossible = true;
				$this->view->possedeCharrette = true;
				if ($this->view->nombreMaximum > $this->view->tabPoidsRondinsCharrette["nb_rondins_transportables"] - $this->view->tabPoidsRondinsCharrette["nb_rondins_presents"]) {
					$this->view->nombreMaximum = $this->view->tabPoidsRondinsCharrette["nb_rondins_transportables"] - $this->view->tabPoidsRondinsCharrette["nb_rondins_presents"];
					if ($this->view->nombreMaximum < 1) {
						$this->view->placeDisponible = false;
						$this->view->acheterPossible = false;
					}
				}
			} else {
				$this->view->acheterPossible = false;
				$this->view->possedeCharrette = false;
			}
		}
	}
	
	private function transfert() {
		Zend_Loader::loadClass("BoutiqueBois");
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Region");
		$this->view->coutCastars = floor($this->view->quantiteAchetee * $this->view->prixUnitaire);
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		
		$charretteTable = new Charrette();
		$data = array(
			'quantite_rondin_charrette' => $this->view->quantiteAchetee,
			'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
		);
		$charretteTable->updateCharrette($data);
		unset($charretteTable);
		
		$regionTable = new Region();
		$idRegion = $regionTable->findIdRegionByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$data = array(
			"date_achat_boutique_bois" => date("Y-m-d H:i:s"),
			"id_fk_lieu_boutique_bois" => $this->view->idBoutique,
			"id_fk_hobbit_boutique_bois" => $this->view->user->id_hobbit,
			"quantite_rondin_boutique_bois" => $this->view->quantiteAchetee,
			"prix_unitaire_boutique_bois" => $this->view->prixUnitaire,
			"id_fk_region_boutique_bois" => $idRegion,
			"action_hobbit_boutique_bois" => "achat",
		);
		$boutiqueBoisTable = new BoutiqueBois();
		$boutiqueBoisTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_charrette", "box_evenements");
	}
}