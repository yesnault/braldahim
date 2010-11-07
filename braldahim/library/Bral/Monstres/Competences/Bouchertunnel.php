<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Bouchertunnel extends Bral_Monstres_Competences_Prereperage {

	// prereperage
	public function actionSpecifique() {

		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - enter - (idm:".$this->monstre["id_monstre"].")");

		$retour = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD;

		//abat un tunnel et disparait s'il n'a personne (Braldûn) dans sa vue.
		$braldunTable = new Braldun();
		$vue = $this->monstre["vue_monstre"] + $this->monstre["vue_malus_monstre"];
		if ($vue < 0) {
			$vue = 0;
		}

		$cible = $braldunTable->findBraldunAvecRayon($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], $vue, null, false);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], $this->monstre["id_fk_groupe_monstre"]);

		Zend_Loader::loadClass("Nid");
		$nidTable = new Nid();
		$nids = $nidTable->findByCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"]);

		if ($cible == null || count($cible) < 1) {

			if ($nids != null && count($nids) > 0) { // S'il y a des nids sur la case
				$retour = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD;
			} elseif ($monstres == null || count($monstres) < 1) { // S'il n'y a pas d'autres groupes ou monstres hors du groupe sur la case
				Zend_Loader::loadClass("Tunnel");
				$tunnelTable = new Tunnel();
				$tunnelTable->delete("x_tunnel = ".$this->monstre["x_monstre"]." and y_tunnel = ".$this->monstre["y_monstre"]." and z_tunnel = ".$this->monstre["z_monstre"]);
				$this->supprimeElementCase($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"]);
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - (idm:".$this->monstre["id_monstre"].") - braldun non vue, suppression de tunnel x:".$this->monstre["x_monstre"]. " y:".$this->monstre["y_monstre"]." z:".$this->monstre["z_monstre"]);
				$retour = Bral_Monstres_Competences_Prereperage::SUITE_DISPARITION;
			} else {
				// on ne bouche pas le tunnel, il y a d'autres type de monstre sur la case
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - (idm:".$this->monstre["id_monstre"].") - braldun non vue, pas suppression de tunnel. Nb monstre sur case hors groupe :".count($monstres). " x:".$this->monstre["x_monstre"]. " y:".$this->monstre["y_monstre"]." z:".$this->monstre["z_monstre"]);
				$retour = Bral_Monstres_Competences_Prereperage::SUITE_DISPARITION;
			}

		} else {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - (idm:".$this->monstre["id_monstre"].") - braldun en vue, pas de suppression de tunnel");
			$retour = Bral_Monstres_Competences_Prereperage::SUITE_REPERAGE_STANDARD;
		}
			
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - exit - (idm:".$this->monstre["id_monstre"].")");
		return $retour;
	}

	private function supprimeElementCase($x, $y, $z) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - supprimeElementCase - (idm:".$this->monstre["id_monstre"].") - enter");
		
		Zend_Loader::loadClass("Route");
		$routeTable = new Route();
		$where = "x_route=".$x." and y_route=".$y. " and z_route=$z and type_route like 'balise'"; // suppression des balises
		$routeTable->delete($where);
		
		Zend_Loader::loadClass("Monstre");
		$monstreTable = new Monstre();
		$where = "x_monstre=".$x." and y_monstre=".$y. " and z_monstre=$z and est_mort_monstre like 'oui'";
		$monstreTable->delete($where);
		
		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();
		$where = "x_charrette=".$x." and y_charrette=".$y. " and z_charrette=$z and id_fk_braldun_charrette is null"; 
		$charretteTable->delete($where);
		
		Zend_Loader::loadClass("Element");
		$elementTable = new Element();
		$where = "x_element=".$x." and y_element=".$y. " and z_element=$z "; 
		$elementTable->delete($where);
		
		Zend_Loader::loadClass("ElementAliment");
		$elementAlimentTable = new ElementAliment();
		$where = "x_element_aliment=".$x." and y_element_aliment=".$y. " and z_element_aliment=$z "; 
		$elementAlimentTable->delete($where);
		
		Zend_Loader::loadClass("ElementEquipement");
		$elementEquipementTable = new ElementEquipement();
		$where = "x_element_equipement=".$x." and y_element_equipement=".$y. " and z_element_equipement=$z "; 
		$elementEquipementTable->delete($where);
		
		Zend_Loader::loadClass("ElementGraine");
		$elementGraineTable = new ElementGraine();
		$where = "x_element_graine=".$x." and y_element_graine=".$y. " and z_element_graine=$z "; 
		$elementGraineTable->delete($where);
		
		Zend_Loader::loadClass("ElementIngredient");
		$elementIngredientTable = new ElementIngredient();
		$where = "x_element_ingredient=".$x." and y_element_ingredient=".$y. " and z_element_ingredient=$z "; 
		$elementIngredientTable->delete($where);
		
		Zend_Loader::loadClass("ElementMateriel");
		$elementMaterielTable = new ElementMateriel();
		$where = "x_element_materiel=".$x." and y_element_materiel=".$y. " and z_element_materiel=$z "; 
		$elementMaterielTable->delete($where);
		
		Zend_Loader::loadClass("ElementMinerai");
		$elementMineraiTable = new ElementMinerai();
		$where = "x_element_minerai=".$x." and y_element_minerai=".$y. " and z_element_minerai=$z "; 
		$elementMineraiTable->delete($where);
		
		Zend_Loader::loadClass("ElementMunition");
		$elementMunitionTable = new ElementMunition();
		$where = "x_element_munition=".$x." and y_element_munition=".$y. " and z_element_munition=$z "; 
		$elementMunitionTable->delete($where);
		
		Zend_Loader::loadClass("ElementPartieplante");
		$elementPartieplanteTable = new ElementPartieplante();
		$where = "x_element_partieplante=".$x." and y_element_partieplante=".$y. " and z_element_partieplante=$z "; 
		$elementPartieplanteTable->delete($where);
		
		Zend_Loader::loadClass("ElementPotion");
		$elementPotionTable = new ElementPotion();
		$where = "x_element_potion=".$x." and y_element_potion=".$y. " and z_element_potion=$z "; 
		$elementPotionTable->delete($where);
		
		Zend_Loader::loadClass("ElementRune");
		$elementRuneTable = new ElementRune();
		$where = "x_element_rune=".$x." and y_element_rune=".$y. " and z_element_rune=$z "; 
		$elementRuneTable->delete($where);
		
		Zend_Loader::loadClass("ElementTabac");
		$elementTabacTable = new ElementTabac();
		$where = "x_element_tabac=".$x." and y_element_tabac=".$y. " and z_element_tabac=$z "; 
		$elementTabacTable->delete($where);
		
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - Bouchertunnel - supprimeElementCase - (idm:".$this->monstre["id_monstre"].") - exit");
	}

	public function enchainerAvecReperageStandard() {
		return false;
	}
}