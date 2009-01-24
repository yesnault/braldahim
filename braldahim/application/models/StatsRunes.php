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
class StatsRunes extends Zend_Db_Table {
	protected $_name = 'stats_runes';
	protected $_primary = array('id_stats_runes');

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_runes', 
			'count(*) as nombre, 
			nb_rune_stats_runes as quantiteRune')
		->where('id_fk_type_rune_stats_runes = '.$data["id_fk_type_rune_stats_runes"]
				.' AND mois_stats_runes = \''.$data["mois_stats_runes"].'\'')
		->group(array('quantiteRune'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
		if (!isset($data["nb_rune_stats_runes"])) {
			$data["nb_rune_stats_runes"] = 0; 
		}
		
		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteRune = $resultat[0]["quantiteRune"];
			
			$dataUpdate['nb_rune_stats_runes'] = $quantiteRune;
			
			if (isset($data["nb_rune_stats_runes"])) {
				$dataUpdate['nb_rune_stats_runes'] = $quantiteRune + $data["nb_rune_stats_runes"];
				if ($dataUpdate['nb_rune_stats_runes'] < 0) {
					$dataUpdate['nb_rune_stats_runes'] = 0;
				}
			}
			
			$where = 'id_fk_type_rune_stats_runes = '.$data["id_fk_type_rune_stats_runes"]
					.' AND mois_stats_runes = \''.$data["mois_stats_runes"].'\'';
			$this->update($dataUpdate, $where);
		}
	}
}