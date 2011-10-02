<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class HistoriqueMateriel extends Zend_Db_Table
{
	protected $_name = 'historique_materiel';
	protected $_primary = "id_historique_materiel";

	public function findByIdMateriel($idMateriel, $pageMin, $pageMax, $filtre)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('historique_materiel', '*')
			->from('type_historique_materiel', '*')
			->where('historique_materiel.id_fk_type_historique_materiel = type_historique_materiel.id_type_historique_materiel')
			->where('historique_materiel.id_fk_historique_materiel = ?', intval($idMateriel))
			->order('id_historique_materiel DESC')
			->limitPage($pageMin, $pageMax);
		if ($filtre <> -1) {
			$select->where('type_historique_materiel.id_type_historique_materiel = ? ', intval($filtre));
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
