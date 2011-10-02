<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class HistoriqueFilature extends Zend_Db_Table
{
	protected $_name = 'historique_filature';
	protected $_primary = "id_historique_filature";

	public function findByIdFilature($idFilature)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('historique_filature', '*')
			->where('id_fk_filature_historique_filature = ?', intval($idFilature))
			->order('id_historique_filature DESC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
