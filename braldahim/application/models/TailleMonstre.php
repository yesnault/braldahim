<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class TailleMonstre extends Zend_Db_Table
{
	protected $_name = 'taille_monstre';
	protected $_primary = "id_taille_monstre";

	const ID_TAILLE_PETIT = 1;
	const ID_TAILLE_NORMAL = 2;
	const ID_TAILLE_GRAND = 3;
	const ID_TAILLE_GIGANTESQUE = 4;
	const ID_TAILLE_BOSS = 5;

    public function fetchAllQuete()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('taille_monstre', '*')
			->where('est_dans_quete_taille_monstre = ?', "oui");
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
