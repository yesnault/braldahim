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
class Bral_Boutique_Acheterminerais extends Bral_Boutique_Boutique {
	
	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter du minerai";
	}
	
	function prepareCommun() {
		$this->view->acheterPossible = true;
		Zend_Loader::loadClass('Bral_Util_BoutiqueMinerais');
		$this->view->minerais = Bral_Util_BoutiqueMinerais::construireTabPrix(true);
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}
		
		for ($i = 1; $i <= count($this->view->minerais); $i++) {
			if (((int)$this->request->get("valeur_".$i).""!=$this->request->get("valeur_".$i)."")) {
				throw new Zend_Exception("Bral_Boutique_Acheterminerais :: Nombre invalide (".$i.") : ".$this->request->get("valeur_".$i));
			}
		}
		
		$this->transfert();
	}
	
	private function transfert() {
		Zend_Loader::loadClass("LabanMinerai");
		$this->view->coutCastars = 0;
		$this->view->poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		
		$this->view->elementsAchetes = "";
		$this->view->manquePlace = false;
		$this->view->manqueCastars = false;
		
		foreach($this->view->minerais as $m) {
			$quantite = (int)$this->request->get($m["id_champ"]);
			
			$idTypeMinerai = $m["id_type_minerai"];
			$nomTypeMinerai = $m["type"];
			
			$prixUnitaire = $m["prixUnitaire"];
			$this->transfertElement($quantite, $prixUnitaire, $idTypeMinerai, $nomTypeMinerai);
		}
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		
		if ($this->view->elementsAchetes != "") {
			$this->view->elementsAchetes = mb_substr($this->view->elementsAchetes, 0, -2);
		} else { // rien n'a pu etre achete
			$this->view->nb_pa = 0;
		}
	}
	
	private function transfertElement($quantite, $prixUnitaire, $idTypeMinerai, $nomTypeMinerai) {
		
		if ($this->view->poidsRestant < 0) $this->view->poidsRestant = 0;
		$nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_MINERAI);
		
		if ($quantite > $nbPossible) {
			$quantite = $nbPossible;
			$this->view->manquePlace = true;
		}
		
		$prixTotal = $prixUnitaire * $quantite;
		$castarsRestants = $this->view->user->castars_hobbit - $this->view->coutCastars;
		if ($prixTotal > $castarsRestants) {
			$quantite = floor($castarsRestants / $prixUnitaire);
			$prixTotal = floor($prixUnitaire * $quantite);
			$this->view->manqueCastars = true;
		}
		
		if ($quantite >= 1) {
			$this->view->coutCastars += $prixTotal;
			$this->view->poidsRestant = floor($this->view->poidsRestant - ($quantite * Bral_Util_Poids::POIDS_MINERAI));
			$this->transfertEnBase($quantite, $idTypeMinerai);
			
			if ($quantite > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= $quantite;
			$this->view->elementsAchetes .= " minerai".$s." ".$nomTypeMinerai;
			if ($prixTotal > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= " pour ".$prixTotal." castar".$s.", ";
		}
	}
	
	private function transfertEnBase($quantite, $idTypeMinerai) {
		$data = array(
			"quantite_brut_laban_minerai" => $quantite,
			"id_fk_type_laban_minerai" => $idTypeMinerai,
			"id_fk_hobbit_laban_minerai" => $this->view->user->id_hobbit,
		);
		
		$labanMineraiTable = new LabanMinerai();
		$labanMineraiTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_evenements");
	}
}