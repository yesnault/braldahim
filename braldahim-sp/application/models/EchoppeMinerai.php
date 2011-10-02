<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: EchoppeMinerai.php 1767 2009-06-22 18:05:02Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-06-22 20:05:02 +0200 (Lun, 22 jui 2009) $
 * $LastChangedRevision: 1767 $
 * $LastChangedBy: yvonnickesnault $
 */
class EchoppeMinerai extends Zend_Db_Table
{
	protected $_name = 'echoppe_minerai';
	protected $_primary = array('id_fk_echoppe_echoppe_minerai', 'id_fk_type_echoppe_minerai');

	function findByIdEchoppe($idEchoppe)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_minerai', '*')
			->from('type_minerai', '*')
			->where('id_fk_echoppe_echoppe_minerai = ?', intval($idEchoppe))
			->where('echoppe_minerai.id_fk_type_echoppe_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
