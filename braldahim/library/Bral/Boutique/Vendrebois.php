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
class Bral_Boutique_Vendrebois extends Bral_Boutique_Boutique {

	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Vendre du bois";
	}

	function prepareCommun() {
		Zend_Loader::loadClass('Charrette');
		
		$tabCharrette["nb_rondin"] = 0;
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);
		if ($charrette != null && count($charrette) > 0) {
			$this->view->charretteOk = true;
			foreach ($charrette as $c) {
				$tabCharrette = array(
					"nb_rondin" => $c["quantite_rondin_charrette"],
				);
			}
		}
		
		$this->view->vendreBoisOk = false;
		if ($tabCharrette["nb_rondin"] > 0) {
			$this->view->vendreBoisOk = true;
		}
		
		$this->view->charrette = $tabCharrette;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->vendreBoisOk == false) {
			throw new Zend_Exception(get_class($this)." Retirer interdit");
		}
		
		$nb_rondins = $this->request->get("valeur_1");
		
		if ((int) $nb_rondins."" != $nb_rondins."") {
			throw new Zend_Exception(get_class($this)." NB Rondins invalide=".$nb_rondins);
		} else {
			$nb_rondins = (int)$nb_rondins;
		}
		
		$this->view->elementsVendus = "";
		$this->calculVendre($nb_rondins);
		if ($this->view->elementsVendus != "") {
			$this->view->elementsVendus = mb_substr($this->view->elementsVendus, 0, -2);
		}
	}
	
	private function calculVendre($nb_rondins) {
		Zend_Loader::loadClass("Charrette");
		
		if ($nb_rondins > 0) {
			// on retire de la charette
			if ($this->view->charretteOk === true) {
				$charretteTable = new Charrette();
				$data = array(
					'quantite_rondin_charrette' => -$nb_rondins,
					'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
				);
				$charretteTable->updateCharrette($data);
				
				$this->view->elementsVendus .= $nb_rondins. " rondin";
				if ($nb_rondins > 1) $this->view->elementsVendus .= "s";
				$this->view->elementsVendus .= ", ";
				
				$nb_rondins = $this->view->echoppe["quantite_rondin_arriere_echoppe"] + $nb_rondins;
			} else {
				$nb_rondins = $this->view->echoppe["quantite_rondin_arriere_echoppe"];
			}
		}
		// TODO donnees vente
	}
	
	function getListBoxRefresh() {
		return array("box_laban", "box_charrette", "box_profil", "box_evenements");
	}
}