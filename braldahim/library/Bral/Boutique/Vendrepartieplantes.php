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
class Bral_Boutique_Deposerressources extends Bral_Boutique_Boutique {

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
		$this->view->laban = $tabLaban;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->vendrePartieplantesOk == false) {
			throw new Zend_Exception(get_class($this)." Retirer interdit");
		}
		
		$this->view->elementsVendus = "";
		$this->calculPartiesPlantes();
		if ($this->view->elementsVendus != "") {
			$this->view->elementsVendus = mb_substr($this->view->elementsVendus, 0, -2);
		}
	}
	
	private function calculPartiesPlantes() {
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("LabanPartieplante");
		
		$echoppePartiePlanteTable = new EchoppePartieplante();
		$labanPartiePlanteTable = new LabanPartieplante();
		
		for ($i=1; $i<=$this->view->nb_valeurs; $i = $i++) {
			$indice = $i;
			$nbBrutes = $this->request->get("valeur_".$indice);
			
			if ((int) $nbBrutes."" != $this->request->get("valeur_".$indice)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute invalide=".$nbBrutes);
			} else {
				$nbBrutes = (int)$nbBrutes;
			}
			if ($nbBrutes > $this->view->partieplantes[$indice]["quantite_laban_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute interdit=".$nbBrutes);
			}
			if ($nbBrutes > 0) {
				// TODO DOnnee vente
				
				$data = array(
					'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_laban_partieplante"],
					'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_laban_partieplante"],
					'id_fk_hobbit_laban_partieplante' => $this->view->user->id_hobbit,
					'quantite_laban_partieplante' => -$nbBrutes,
				);
				$labanPartiePlanteTable->insertOrUpdate($data);
				$sbrute = "";
				if ($nbBrutes > 1) $sbrute = "s";
				$this->view->elementsVendus .= $this->view->partieplantes[$indice]["nom_plante"]. " : ";
				$this->view->elementsVendus .= $nbBrutes. " ".$this->view->partieplantes[$indice]["nom_type"]. " brute".$sbrute;
				$this->view->elementsVendus .= ", ";
			}
		}
	}
	
	private function prepareCommunRessources() {
		Zend_Loader::loadClass("LabanPartieplante");
		Zend_Loader::loadClass("LabanMinerai");

		$tabPartiePlantes = null;
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$this->view->nb_valeurs = 0;
		$this->view->nb_partiePlantes = 0;
		
		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				if ($p["quantite_laban_partieplante"] > 0) {
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
					);
					$this->view->vendrePartieplantesOk = true;
					$this->view->nb_partiePlantes = $this->view->nb_partiePlantes + $p["quantite_laban_partieplante"];
				}
			}
		}
		
		$this->view->partieplantes = $tabPartiePlantes;
	}
	
	function getListBoxRefresh() {
		return array("box_laban", "box_charrette", "box_profil", "box_evenements");
	}
}