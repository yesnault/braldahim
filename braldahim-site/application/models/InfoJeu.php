<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: InfoJeu.php 715 2008-12-10 22:24:06Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-12-10 23:24:06 +0100 (Wed, 10 Dec 2008) $
 * $LastChangedRevision: 715 $
 * $LastChangedBy: yvonnickesnault $
 */
class InfoJeu extends Zend_Db_Table {
	protected $_name = 'info_jeu';
	protected $_primary = 'id_info_jeu';

	public function findAllAccueil() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('info_jeu', '*')
		->where("info_jeu.est_sur_accueil_info_jeu like 'oui'")
		->order('date_info_jeu DESC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_info_jeu = ?',(int)$id);
		return $this->fetchRow($where);
	}
}