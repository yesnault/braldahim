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

class Bral_Competences_Recyclage extends Bral_Competences_Competence {
	
	function prepareCommun() {
		Zend_Loader::loadClass("LabanEquipement");
		
		/*
		 * Si le hobbit n'a pas de PA, on ne fait aucun traitement
		 */
		$this->calculNbPa();
		if ($this->view->assezDePa == false) {
			return;
		}
		
		// on va chercher l'équipement présent dans le laban
		$tabEquipementLaban = null;
		$labanEquipementTable = new LabanEquipement();
		$equipementLabanRowset = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		
		foreach ($equipementLabanRowset as $e) {
			$tabEquipementLaban[] = array(
				"id_equipement" => $e["id_laban_equipement"],
				"nom" => $e["nom_type_equipement"],
				"qualite" => $e["nom_type_qualite"],
				"niveau" => $e["niveau_recette_equipement"],
				"id_type" => $e["id_type_equipement"],
			);
		}
		$this->view->tabEquipementLaban = $tabEquipementLaban;
		$this->view->nbEquipementLaban = count ($tabEquipementLaban);
		
	}
	
	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}
	
	function prepareResultat() {
		
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}
		
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Equipement invalide : ".$this->request->get("valeur_1"));
		} else {
			$idEquipement = (int)$this->request->get("valeur_1");
		}
		
		$recyclage = false;
		if (isset($this->view->tabEquipementLaban) && $this->view->nbEquipementLaban > 0) {
			foreach ($this->view->tabEquipementLaban as $e) {
				if ($e["id_equipement"] == $idEquipement) {
					$idTypeEquipement = $e["id_type"];
					$nivEquipement = $e["niveau"];
					$recyclage = true;
					break;
				}
			}
		}
		if ($recyclage === false) {
			throw new Zend_Exception(get_class($this)." Equipement invalide (".$idEquipement.")");
		}
		
		$this->calculJets();
		if ($this->view->okJet1 === true) {
			$this->calculRecyclage ($idEquipement, $idTypeEquipement, $nivEquipement);
		}
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majHobbit();
	}
	
	private function calculRecyclage($idEquipement, $idTypeEquipement, $nivEquipement){
		Zend_Loader::loadClass("RecetteCout");
		Zend_Loader::loadClass("RecetteCoutMinerai");
		Zend_Loader::loadClass("Laban");
		
		$nbCuir = 0;
		$nbFourrure = 0;
		$nbPlanche = 0;
		$tabMinerai = null;		
		
		$recetteCoutTable = new RecetteCout();
		$recetteCout = $recetteCoutTable->findByIdTypeEquipementAndNiveau($idTypeEquipement, $nivEquipement);
		
		/*
		 * Soit Ni le niveau de la pièce d'équipement. Soit Js un jet de SAG + BM.
		 * Si Js < Ni*10 alors 25% de chacun des composants arrondis à l'inférieur sont récupérés
		 * Si Ni*10 < Js < Ni*20 alors 50 % des ...
		 * Si Ni*20 < Js < Ni*30 alors 60 % des ...
		 * Si Ni*30 < Js  alors 80 % des ...
		 */
		
		$jetSag = 0;
		for ($i=1; $i<=$this->view->config->game->base_sagesse + $this->view->user->sagesse_base_hobbit; $i++) {
			$jetSag = $jetSag + Bral_Util_De::get_1d6();
		}
		$jetSag = $jetSag + $this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit;
		
		if ($jetSag < $nivEquipement*10) {
			$perte = 0.25;
		}
		elseif ($jetSag > $nivEquipement*10 && $jetSag < $nivEquipement*20) {
			$perte = 0.5;
		}
		elseif ($jetSag > $nivEquipement*20 && $jetSag < $nivEquipement*30) {
			$perte = 0.6;
		}
		elseif ($jetSag > $nivEquipement*30) {
			$perte = 0.8;
		}
		
		foreach($recetteCout as $r) {
			$nbCuir = floor($r["cuir_recette_cout"]*$perte);
			$nbFourrure = floor($r["fourrure_recette_cout"]*$perte);
			$nbPlanche = floor($r["planche_recette_cout"]*$perte);
		}
		
		$recetteCoutMineraiTable = new RecetteCoutMinerai();
		$recetteCoutMinerai = $recetteCoutMineraiTable->findByIdTypeEquipementAndNiveau($idTypeEquipement, $nivEquipement);
		if (count ($recetteCoutMinerai) > 0) {
			Zend_Loader::loadClass("LabanMinerai");
			$labanMineraiTable = new LabanMinerai();
			foreach($recetteCoutMinerai as $r){
				$quantite = floor($r["quantite_recette_cout_minerai"]*$perte);
				$tabMinerai[] = array (
					"nom" => $r["nom_type_minerai"],
					"quantite" => $quantite,
				);
				$data = array(
					'id_fk_type_laban_minerai' => $r["id_type_minerai"],
					'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
					'quantite_brut_laban_minerai' => $quantite,
				);
				$labanMineraiTable->insertOrUpdate($data);
			}
		}
		/*
		 * TODO
		 * Controler le poids
		 * Supprimer l'equipement
		 * Afficher le résultat
		 * Runes
		 * tests avec minerai
		 */
		
		// on ajoute dans le laban
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_cuir_laban' => $nbCuir,
			'quantite_fourrure_laban' => $nbFourrure,
			'quantite_planche_laban' => $nbPlanche,
		);
		$labanTable->insertOrUpdate($data);
		
		$this->view->nbCuir = $nbCuir;
		$this->view->nbFourrure = $nbFourrure;
		$this->view->nbPlanche = $nbPlanche;
		$this->view->minerai = $tabMinerai;
		
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_evenements", "box_competences_communes", "box_laban");
	}
}
