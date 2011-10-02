<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Etape.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class Etape extends Zend_Db_Table
{
	protected $_name = 'etape';
	protected $_primary = array('id_etape');

	function findByIdBraldun($idBraldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('etape', '*')
			->where('id_fk_braldun_etape = ?', intval($idBraldun))
			->order('ordre_etape ASC');

		$sql = $select->__toString();

		$result = $db->fetchAll($sql);
		return $db->fetchAll($sql);
	}
}