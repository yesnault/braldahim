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
		
		$this->view->deposerRessourcesOk = false;
		$this->prepareCommunRessources();
		$this->view->laban = $tabLaban;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->vendreMineraisOk == false) {
			throw new Zend_Exception(get_class($this)." Retirer interdit");
		}
		
		$this->view->elementsVendus = "";
		$this->calculMinerais();
		if ($this->view->elementsVendus != "") {
			$this->view->elementsVendus = mb_substr($this->view->elementsVendus, 0, -2);
		}
	}
	
	private function calculMinerais() {
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass('LabanMinerai');
		
		$echoppeMineraiTable = new EchoppeMinerai();
		$labanMineraiTable = new LabanMinerai();
		
		for ($i=1; $i<=$this->view->nb_valeurs; $i = $i++) {
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
			
			if ($nbBrut > 0) {
				//TODO Données vente
				
				$data = array(
					'id_fk_type_laban_minerai' => $this->view->minerais[$indice]["id_fk_type_laban_minerai"],
					'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
					'quantite_brut_laban_minerai' => -$nbBrut,
				);
		
				$labanMineraiTable->insertOrUpdate($data);
				
				$sbrut = "";
				if ($nbBrut > 1) $sbrut = "s";
				$this->view->elementsVendus .= $this->view->minerais[$indice]["type"]. " : ".$nbBrut. " minerai.".$sbrut." brut".$sbrut;
				$this->view->elementsVendus .= ", ";
			}
		}
	}
	
	private function prepareCommunRessources() {
		Zend_Loader::loadClass("LabanMinerai");

		$tabMinerais = null;
		$labanMineraiTable = new labanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);

		$this->view->nb_valeurs = 0;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				if ($m["quantite_brut_laban_minerai"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brut
					$tabMinerais[$this->view->nb_valeurs] = array(
						"type" => $m["nom_type_minerai"],
						"id_fk_type_laban_minerai" => $m["id_fk_type_laban_minerai"],
						"id_fk_hobbit_laban_minerai" => $m["id_fk_hobbit_laban_minerai"],
						"quantite_brut_laban_minerai" => $m["quantite_brut_laban_minerai"],
						"quantite_lingots_laban_minerai" => $m["quantite_lingots_laban_minerai"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
					$this->view->vendreMineraisOk = true;
					$this->view->nb_minerai_brut = $this->view->nb_minerai_brut + $m["quantite_brut_laban_minerai"];
				}
			}
		}

		$this->view->minerais = $tabMinerais;
	}
	
	function getListBoxRefresh() {
		return array("box_laban", "box_charrette", "box_profil", "box_evenements");
	}
}