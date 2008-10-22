<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class EffetPotionHobbit extends Zend_Db_Table {
	protected $_name = 'effet_potion_hobbit';
	protected $_primary = array('id_effet_potion_hobbit');

	function findByIdHobbitCible($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_potion_hobbit', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_potion_effet_potion_hobbit = id_type_potion')
		->where('id_fk_type_qualite_effet_potion_hobbit = id_type_qualite')
		->where('id_fk_hobbit_cible_effet_potion_hobbit = ?', intval($id_hobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function enleveUnTour($potion) {
		Bral_Util_Log::potion()->debug('EffetPotionHobbit - enleveUnTour - enter');
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_potion_hobbit', '*')
		->where('id_effet_potion_hobbit = ?', intval($potion["id_potion"]));
		
		$sql = $select->__toString();
		$resultat = $db->fetchRow($sql);
		
		if ($resultat != null) {
			$resultat["nb_tour_restant_effet_potion_hobbit"] = $resultat["nb_tour_restant_effet_potion_hobbit"] - 1;
			Bral_Util_Log::potion()->debug('EffetPotionHobbit - enleveUnTour - potion '.$potion["id_potion"].' tour(s) restant(s)='.$resultat["nb_tour_restant_effet_potion_hobbit"]);
			
			if ($resultat["nb_tour_restant_effet_potion_hobbit"] < 1) {
				Bral_Util_Log::potion()->debug('EffetPotionHobbit - enleveUnTour - suppression de la potion '.$potion["id_potion"].' de la table EffetPotionHobbit');
				$where = 'id_effet_potion_hobbit = '.intval($potion["id_potion"]);
				$this->delete($where);
			}
		}
		Bral_Util_Log::potion()->debug('EffetPotionHobbit - enleveUnTour - exit');
	}
}
