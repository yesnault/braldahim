<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: HistoriqueFilature.php 2019 2009-09-19 10:32:13Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-09-19 12:32:13 +0200 (Sam, 19 sep 2009) $
 * $LastChangedRevision: 2019 $
 * $LastChangedBy: yvonnickesnault $
 */
class HistoriqueFilature extends Zend_Db_Table {
	protected $_name = 'historique_filature';
	protected $_primary = "id_historique_filature";
	
	public function findByIdFilature($idFilature){
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('historique_filature', '*')
		->where('id_fk_filature_historique_filature = ?', intval($idFilature))
		->order('id_historique_filature DESC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
