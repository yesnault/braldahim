<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class StatsDistinction extends Zend_Db_Table
{
	protected $_name = 'stats_distinction';
	protected $_primary = array('id_stats_distinction');

	function deleteAndInsert($data)
	{

		if (!isset($data["points_stats_distinction"])) {
			$data["points_stats_distinction"] = 0;
		}

		$where = 'id_fk_braldun_stats_distinction = ' . $data["id_fk_braldun_stats_distinction"] . ' AND mois_stats_distinction = \'' . $data["mois_stats_distinction"] . '\'';

		/* Commenter ce bloc si rattrapage*/
		$this->delete($where);
		$this->insert($data);
		/* fin bloc. */

		/*
		 * Code pour rattrapage
		 * $db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_distinction', '*');
		$select->where('id_fk_braldun_stats_distinction =  ?', $data["id_fk_braldun_stats_distinction"]);
		$select->where('mois_stats_distinction = ?', $data["mois_stats_distinction"]);
		$sql = $select->__toString();
		$res = $db->fetchAll($sql);
		if ($res != null) {
			if (count($res) > 1) {
				throw new Zend_Exception("Erreur Braldun id=".$data["id_fk_braldun_stats_distinction"]. " mois:".$data["mois_stats_distinction"]);
			} elseif (count($res) == 1) {
				// on garde le niveau
				$data["niveau_braldun_stats_distinction"] = $res[0]["niveau_braldun_stats_distinction"];
				$this->update($data, $where);
			} else {
				$this->insert($data);
			}
		}*/

	}
}