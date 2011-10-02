<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Filature extends Zend_Db_Table
{
	protected $_name = 'filature';
	protected $_primary = array('id_filature');

	function findByIdBraldun($idBraldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filature', '*')
			->from('braldun', '*')
			->where('id_fk_braldun_filature = ?', intval($idBraldun))
			->where('id_fk_cible_braldun_filature = id_braldun')
			->order('date_creation_filature DESC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findEnCoursByIdBraldun($idBraldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filature', '*')
			->from('braldun', '*')
			->where('id_fk_cible_braldun_filature = id_braldun')
			->where('id_fk_braldun_filature = ?', intval($idBraldun))
			->where('date_fin_filature is null');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldunAndIdFilature($idBraldun, $idFilature)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filature', '*')
			->where('id_fk_braldun_filature = ?', intval($idBraldun))
			->where('id_filature = ?', intval($idFilature));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

}
