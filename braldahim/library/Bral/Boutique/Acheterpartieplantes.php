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
class Bral_Boutique_Acheterpartieplantes extends Bral_Boutique_Boutique {
	
	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter des parties de plantes";
	}
	
	function prepareCommun() {
		$this->view->acheterPossible = true;
		
		Zend_Loader::loadClass('Bral_Util_BoutiquePlantes');
		$this->view->typePlantes = Bral_Util_BoutiquePlantes::construireTabPrix(true);
		
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}
		
		for ($i = 1; $i <= $this->view->typePlantes["nb_valeurs"]; $i++) {
			if (((int)$this->request->get("valeur_".$i).""!=$this->request->get("valeur_".$i)."")) {
				throw new Zend_Exception("Bral_Boutique_Acheterpartieplantes :: Nombre invalide (".$i.") : ".$this->request->get("valeur_".$i));
			} else {
				$this->view->quantiteAchetee = (int)$this->request->get("valeur_".$i);
			}
		}
		
		$this->transfert();
	}
	
	private function transfert() {
		Zend_Loader::loadClass("LabanPartieplante");
		$this->view->coutCastars = 0;
		$this->view->poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		
		$this->view->elementsAchetes = "";
		$this->view->manquePlace = false;
		$this->view->manqueCastars = false;
		
		for ($i = 1; $i <= $this->view->typePlantes["nb_valeurs"]; $i++) {
			$quantite = (int)$this->request->get("valeur_".$i);
			$idTypePlante = $this->view->typePlantes["valeurs"]["valeur_".$i]["id_type_plante"];
			$idTypePartiePlante = $this->view->typePlantes["valeurs"]["valeur_".$i]["id_type_partieplante"];
			$nomTypePlante = $this->view->typePlantes["valeurs"]["valeur_".$i]["nom_type_plante"];
			$nomTypePartiePlante = $this->view->typePlantes["valeurs"]["valeur_".$i]["nom_type_partieplante"];
			
			$prixUnitaire = $this->view->typePlantes["valeurs"]["valeur_".$i]["prixUnitaire"];
			$this->transfertElement($quantite, $prixUnitaire, $idTypePlante, $idTypePartiePlante, $nomTypePlante, $nomTypePartiePlante);
		}
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		
		if ($this->view->elementsAchetes != "") {
			$this->view->elementsAchetes = mb_substr($this->view->elementsAchetes, 0, -2);
		} else { // rien n'a pu etre achete
			$this->view->nb_pa = 0;
		}
	}
	
	private function transfertElement($quantite, $prixUnitaire, $idTypePlante, $idTypePartiePlante, $nomTypePlante, $nomTypePartiePlante) {
		
		if ($this->view->poidsRestant < 0) $this->view->poidsRestant = 0;
		$nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);
		
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
			$this->view->poidsRestant = floor($this->view->poidsRestant - ($quantite * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE));
			$this->transfertEnBase($quantite, $idTypePlante, $idTypePartiePlante);
			
			if ($quantite > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= $quantite;
			$this->view->elementsAchetes .= " ".$nomTypePartiePlante.$s;
			$this->view->elementsAchetes .= " ".$nomTypePlante;
			if ($prixTotal > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= " pour ".$prixTotal." castar".$s.", ";
		}
	}
	
	private function transfertEnBase($quantite, $idTypePlante, $idTypePartiePlante) {
		$data = array(
			"quantite_laban_partieplante" => $quantite,
			"id_fk_type_laban_partieplante" => $idTypePartiePlante,
			"id_fk_type_plante_laban_partieplante" => $idTypePlante,
			"id_fk_hobbit_laban_partieplante" => $this->view->user->id_hobbit,
		);
		
		$labanPartiePlanteTable = new LabanPartieplante();
		$labanPartiePlanteTable->insertOrUpdate($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_evenements");
	}
}