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
class StatsDistinction extends Zend_Db_Table {
	protected $_name = 'stats_distinction';
	protected $_primary = array('id_stats_distinction');

	function deleteAndInsert($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_distinction', 'count(*) as nombre, points_stats_distinction as points')
		->where('niveau_braldun_stats_distinction = '.$data["niveau_braldun_stats_distinction"].' AND id_fk_braldun_stats_distinction = '.$data["id_fk_braldun_stats_distinction"]. ' AND mois_stats_distinction = \''.$data["mois_stats_distinction"].'\'')
		->group(array('points'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (!isset($data["points_stats_distinction"])) {
			$data["points_stats_distinction"] = 0;
		}

		$where = 'id_fk_braldun_stats_distinction = '.$data["id_fk_braldun_stats_distinction"]. ' AND mois_stats_distinction = \''.$data["mois_stats_distinction"].'\'';
		$this->delete($where);
		$this->insert($data);
	}
}