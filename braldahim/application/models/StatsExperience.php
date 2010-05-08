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
class StatsExperience extends Zend_Db_Table {
	protected $_name = 'stats_experience';
	protected $_primary = array('id_stats_experience');

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_experience', 'count(*) as nombre, nb_px_perso_gagnes_stats_experience as quantitePxPerso, nb_px_commun_gagnes_stats_experience as quantitePxCommun')
		->where('niveau_braldun_stats_experience = '.$data["niveau_braldun_stats_experience"].' AND id_fk_braldun_stats_experience = '.$data["id_fk_braldun_stats_experience"]. ' AND mois_stats_experience = \''.$data["mois_stats_experience"].'\'')
		->group(array('quantitePxPerso', 'quantitePxCommun'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
		if (!isset($data["nb_px_commun_gagnes_stats_experience"])) {
			$data["nb_px_commun_gagnes_stats_experience"] = 0; 
		}
		
		if (!isset($data["nb_px_perso_gagnes_stats_experience"])) {
			$data["nb_px_perso_gagnes_stats_experience"] = 0;
		}
		
		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePxPerso = $resultat[0]["quantitePxPerso"];
			$quantitePxCommun = $resultat[0]["quantitePxCommun"];
			$dataUpdate['nb_px_perso_gagnes_stats_experience'] = $quantitePxPerso;
			$dataUpdate['nb_px_commun_gagnes_stats_experience'] = $quantitePxCommun;
			
			if (isset($data["nb_px_perso_gagnes_stats_experience"])) {
				$dataUpdate['nb_px_perso_gagnes_stats_experience'] = $quantitePxPerso + $data["nb_px_perso_gagnes_stats_experience"];
				if ($dataUpdate['nb_px_perso_gagnes_stats_experience'] < 0) {
					$dataUpdate['nb_px_perso_gagnes_stats_experience'] = 0;
				}
			}
			
			if (isset($data["nb_px_commun_gagnes_stats_experience"])) {
				$dataUpdate['nb_px_commun_gagnes_stats_experience'] = $quantitePxCommun + $data["nb_px_commun_gagnes_stats_experience"];
				if ($dataUpdate['nb_px_commun_gagnes_stats_experience'] < 0) {
					$dataUpdate['nb_px_commun_gagnes_stats_experience'] = 0;
				}
			}
			
			$where = 'niveau_braldun_stats_experience = '.$data["niveau_braldun_stats_experience"].' AND id_fk_braldun_stats_experience = '.$data["id_fk_braldun_stats_experience"]. ' AND mois_stats_experience = \''.$data["mois_stats_experience"].'\'';
			$this->update($dataUpdate, $where);
		}
	}
}