<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EffetMonstre extends Zend_Db_Table
{
	protected $_name = 'effet_monstre';
	protected $_primary = array('id_effet_monstre');

	function findByIdMonstreCible($idMonstre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_monstre', '*')
			->where('id_fk_monstre_cible_effet_monstre = ?', intval($idMonstre));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function enleveUnTour($effet)
	{
		Bral_Util_Log::potion()->debug('EffetMonstre - enleveUnTour - enter');
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('effet_monstre', '*')
			->where('id_effet_monstre = ?', intval($effet["id_effet_monstre"]));

		$sql = $select->__toString();
		$resultat = $db->fetchRow($sql);

		$retour = false;

		if ($resultat != null) {
			$resultat["nb_tour_restant_effet_monstre"] = $resultat["nb_tour_restant_effet_monstre"] - 1;
			Bral_Util_Log::potion()->debug('EffetMonstre - enleveUnTour - potion ' . $effet["id_effet_monstre"] . ' tour(s) restant(s)=' . $resultat["nb_tour_restant_effet_monstre"]);

			$where = 'id_effet_monstre = ' . intval($effet["id_effet_monstre"]);
			if ($resultat["nb_tour_restant_effet_monstre"] < 0) {
				Bral_Util_Log::potion()->debug('EffetMonstre - enleveUnTour - suppression de l\'effet ' . $effet["id_effet_monstre"] . ' de la table EffetMonstre');
				$this->delete($where);
				$retour = true;
			} else {
				Bral_Util_Log::potion()->debug('EffetMonstre - enleveUnTour - mise a jour de l\'effet ' . $effet["id_effet_monstre"] . ' de la table EffetMonstre');
				$dataUpdate["nb_tour_restant_effet_monstre"] = $resultat["nb_tour_restant_effet_monstre"];
				$this->update($dataUpdate, $where);
				$retour = false;
			}
		}
		$texte = "false";
		if ($retour == true) {
			$texte = "true";
		}
		Bral_Util_Log::potion()->debug('EffetMonstre - enleveUnTour - exit - (' . $texte . ')');
		return $retour;
	}
}
