<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EffetPotionBraldun extends Zend_Db_Table {
	protected $_name = 'effet_potion_braldun';
	protected $_primary = array('id_effet_potion_braldun');

	function findByIdBraldunCible($id_braldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_potion_braldun', '*')
		->from('type_potion')
		->from('type_qualite')
		->from('potion')
		->where('id_effet_potion_braldun = id_potion')
		->where('id_fk_type_potion = id_type_potion')
		->where('id_fk_type_qualite_potion = id_type_qualite')
		->where('id_fk_braldun_cible_effet_potion_braldun = ?', intval($id_braldun));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function enleveUnTour($potion) {
		Bral_Util_Log::potion()->debug('EffetPotionBraldun - enleveUnTour - enter');
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_potion_braldun', '*')
		->where('id_effet_potion_braldun = ?', intval($potion["id_potion"]));
		
		$sql = $select->__toString();
		$resultat = $db->fetchRow($sql);
		
		$retour = false;
		
		if ($resultat != null) {
			$resultat["nb_tour_restant_effet_potion_braldun"] = $resultat["nb_tour_restant_effet_potion_braldun"] - 1;
			Bral_Util_Log::potion()->debug('EffetPotionBraldun - enleveUnTour - potion '.$potion["id_potion"].' tour(s) restant(s)='.$resultat["nb_tour_restant_effet_potion_braldun"]);
			
			$where = 'id_effet_potion_braldun = '.intval($potion["id_potion"]);
			if ($resultat["nb_tour_restant_effet_potion_braldun"] < 0) {
				Bral_Util_Log::potion()->debug('EffetPotionBraldun - enleveUnTour - suppression de la potion '.$potion["id_potion"].' de la table EffetPotionBraldun');
				$this->delete($where);
				$retour = true;
			} else {
				Bral_Util_Log::potion()->debug('EffetPotionBraldun - enleveUnTour - mise a jour de la potion '.$potion["id_potion"].' de la table EffetPotionBraldun');
				$dataUpdate["nb_tour_restant_effet_potion_braldun"] = $resultat["nb_tour_restant_effet_potion_braldun"];
				$this->update($dataUpdate, $where);
				$retour = false;
			}
		}
		$texte = "false";
		if ($retour == true) {
			$texte = "true";
		}
		Bral_Util_Log::potion()->debug('EffetPotionBraldun - enleveUnTour - exit - ('.$texte.')');
		return $retour;
	}
}
