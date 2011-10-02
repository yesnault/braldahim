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
class StatsRunes extends Zend_Db_Table
{
	protected $_name = 'stats_runes';
	protected $_primary = array('id_stats_runes');

	function findByType($dateDebut, $dateFin)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_rune', 'nom_type_rune as nom');
		$select->from('stats_runes', 'sum(nb_rune_stats_runes) as nombre');
		$select->where('id_fk_type_rune_stats_runes = id_type_rune');
		$select->where('mois_stats_runes >= ?', $dateDebut);
		$select->where('mois_stats_runes < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCategorie($dateDebut, $dateFin)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('type_rune', 'niveau_type_rune as niveau');
		$select->from('stats_runes', 'sum(nb_rune_stats_runes) as nombre');
		$select->where('id_fk_type_rune_stats_runes = id_type_rune');
		$select->where('mois_stats_runes >= ?', $dateDebut);
		$select->where('mois_stats_runes < ?', $dateFin);
		$select->order("niveau ASC");
		$select->group(array('niveau'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}