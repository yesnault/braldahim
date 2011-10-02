<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Quete extends Zend_Db_Table
{
	protected $_name = 'quete';
	protected $_primary = array('id_quete');

	function findByIdBraldun($idBraldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('quete', '*')
			->from('lieu', '*')
			->where('id_fk_lieu_quete = id_lieu')
			->where('id_fk_braldun_quete = ?', intval($idBraldun))
			->joinLeft('ville', 'id_fk_ville_lieu = id_ville');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findEnCoursByIdBraldun($idBraldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('quete', '*')
			->from('lieu', '*')
			->where('id_fk_lieu_quete = id_lieu')
			->where('id_fk_braldun_quete = ?', intval($idBraldun))
			->where('date_fin_quete is null');
		$sql = $select->__toString();

		$result = $db->fetchAll($sql);
		if (count($result) > 1) {
			throw new Zend_Exception("Quete::findEnCoursByIdBraldun nbInvalide:" . count($result) . " h:" . $idBraldun);
		} elseif (count($result) == 1) {
			return $result[0];
		} else {
			return null;
		}
	}

	function findByIdBraldunAndIdQuete($idBraldun, $idQuete)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('quete', '*')
			->from('lieu', '*')
			->where('id_fk_lieu_quete = id_lieu')
			->where('id_fk_braldun_quete = ?', intval($idBraldun))
			->where('id_quete = ?', intval($idQuete));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdBraldunAndIdLieu($idBraldun, $idLieu)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('quete', '*')
			->from('lieu', '*')
			->where('id_fk_lieu_quete = id_lieu')
			->where('id_fk_braldun_quete = ?', intval($idBraldun))
			->where('id_fk_lieu_quete = ?', intval($idLieu));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
