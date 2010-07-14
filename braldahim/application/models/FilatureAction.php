<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Quete.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class FilatureAction extends Zend_Db_Table {
	protected $_name = 'filature_action';
	protected $_primary = array('id_filature_action');

	function findByIdBraldunAndPosition($idBraldun, $x, $y) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filature_action', '*')
		->from('filature', '*')
		->where('id_fk_filature_action = id_filature')
		->where('id_fk_braldun_filature_action = ?', intval($idBraldun))
		->where('x_min_filature_action <= ?', $x)
		->where('x_max_filature_action >= ?', $x)
		->where('y_min_filature_action <= ?', $y)
		->where('y_max_filature_action >= ?', $y);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function countByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('filature_action', 'count(*) as nombre')
		->where('id_fk_braldun_filature_action = ?', intval($idBraldun));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}


}
