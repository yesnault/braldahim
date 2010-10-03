<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class InfoJeu extends Zend_Db_Table {
	protected $_name = 'info_jeu';
	protected $_primary = 'id_info_jeu';

	public function findAll($type = null, $dateDebut = null, $dateFin = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('info_jeu', '*')
		->order('date_info_jeu DESC');

		if ($type != null && $type == "histoire" || $type == "annonce") {
			$select->where("type_info_jeu = ?", $type);
		}

		if ($dateDebut != null && $dateFin != null) {
			$select->where('date_info_jeu >= ?', $dateDebut);
			$select->where('date_info_jeu < ?', $dateFin);
		} else {
			$select->where("info_jeu.est_sur_accueil_info_jeu like 'oui'");
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_info_jeu = ?',(int)$id);
		return $this->fetchRow($where);
	}
}