<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: StatsReputation.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class StatsReputation extends Zend_Db_Table {
	protected $_name = 'stats_reputation';
	protected $_primary = array('id_stats_reputation');

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_reputation', 'count(*) as nombre, points_redresseur_stats_reputation as quantitePxPerso, points_gredin_stats_reputation as quantitePxCommun')
		->where('niveau_braldun_stats_reputation = '.$data["niveau_braldun_stats_reputation"].' AND id_fk_braldun_stats_reputation = '.$data["id_fk_braldun_stats_reputation"]. ' AND mois_stats_reputation = \''.$data["mois_stats_reputation"].'\'')
		->group(array('quantitePxPerso', 'quantitePxCommun'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
		if (!isset($data["points_gredin_stats_reputation"])) {
			$data["points_gredin_stats_reputation"] = 0; 
		}
		
		if (!isset($data["points_redresseur_stats_reputation"])) {
			$data["points_redresseur_stats_reputation"] = 0;
		}
		
		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePxPerso = $resultat[0]["quantitePxPerso"];
			$quantitePxCommun = $resultat[0]["quantitePxCommun"];
			$dataUpdate['points_redresseur_stats_reputation'] = $quantitePxPerso;
			$dataUpdate['points_gredin_stats_reputation'] = $quantitePxCommun;
			
			if (isset($data["points_redresseur_stats_reputation"])) {
				$dataUpdate['points_redresseur_stats_reputation'] = $data["points_redresseur_stats_reputation"];
				if ($dataUpdate['points_redresseur_stats_reputation'] < 0) {
					$dataUpdate['points_redresseur_stats_reputation'] = 0;
				}
			}
			
			if (isset($data["points_gredin_stats_reputation"])) {
				$dataUpdate['points_gredin_stats_reputation'] = $data["points_gredin_stats_reputation"];
				if ($dataUpdate['points_gredin_stats_reputation'] < 0) {
					$dataUpdate['points_gredin_stats_reputation'] = 0;
				}
			}
			
			$where = 'niveau_braldun_stats_reputation = '.$data["niveau_braldun_stats_reputation"].' AND id_fk_braldun_stats_reputation = '.$data["id_fk_braldun_stats_reputation"]. ' AND mois_stats_reputation = \''.$data["mois_stats_reputation"].'\'';
			$this->update($dataUpdate, $where);
		}
	}
}