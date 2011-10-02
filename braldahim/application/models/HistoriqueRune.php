<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class HistoriqueRune extends Zend_Db_Table
{
	protected $_name = 'historique_rune';
	protected $_primary = "id_historique_rune";

	public function findByIdRune($idRune, $pageMin, $pageMax, $filtre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('historique_rune', '*')
			->from('type_historique_rune', '*')
			->where('historique_rune.id_fk_type_historique_rune = type_historique_rune.id_type_historique_rune')
			->where('historique_rune.id_fk_historique_rune = ?', intval($idRune))
			->order('id_historique_rune DESC')
			->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_historique_rune.id_type_historique_rune = ? ', intval($filtre));
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
