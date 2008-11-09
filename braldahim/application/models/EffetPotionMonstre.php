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
class EffetPotionMonstre extends Zend_Db_Table {
	protected $_name = 'effet_potion_monstre';
	protected $_primary = array('id_effet_potion_monstre');

	function findByIdMonstreCible($id_monstre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_potion_monstre', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_potion_effet_potion_monstre = id_type_potion')
		->where('id_fk_type_qualite_effet_potion_monstre = id_type_qualite')
		->where('id_fk_monstre_cible_effet_potion_monstre = ?', intval($id_monstre));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function enleveUnTour($potion) {
		Bral_Util_Log::potion()->debug('EffetPotionMonstre - enleveUnTour - enter');
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_potion_monstre', '*')
		->where('id_effet_potion_monstre = ?', intval($potion["id_potion"]));
		
		$sql = $select->__toString();
		$resultat = $db->fetchRow($sql);
		
		if ($resultat != null) {
			$resultat["nb_tour_restant_effet_potion_monstre"] = $resultat["nb_tour_restant_effet_potion_monstre"] - 1;
			Bral_Util_Log::potion()->debug('EffetPotionMonstre - enleveUnTour - potion '.$potion["id_potion"].' tour(s) restant(s)='.$resultat["nb_tour_restant_effet_potion_monstre"]);

			$where = 'id_effet_potion_monstre = '.intval($potion["id_potion"]);
			if ($resultat["nb_tour_restant_effet_potion_monstre"] < 1) {
				Bral_Util_Log::potion()->debug('EffetPotionMonstre - enleveUnTour - suppression de la potion '.$potion["id_potion"].' de la table EffetPotionMonstre');
				$this->delete($where);
			} else {
				Bral_Util_Log::potion()->debug('EffetPotionHobbit - enleveUnTour - mise a jour de la potion '.$potion["id_potion"].' de la table EffetPotionMonstre');
				$dataUpdate["nb_tour_restant_effet_potion_monstre"] = $resultat["nb_tour_restant_effet_potion_monstre"];
				$this->update($dataUpdate, $where);
			}
		}
		Bral_Util_Log::potion()->debug('EffetPotionMonstre - enleveUnTour - exit');
	}
}
