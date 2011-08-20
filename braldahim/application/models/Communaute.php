<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Communaute extends Zend_Db_Table {
	protected $_name = 'communaute';
	protected $_primary = array('id_communaute');

	public function findById($id) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('communaute', '*')
		->from('braldun', '*')
		->where('id_fk_braldun_gestionnaire_communaute = id_braldun')
		->where('id_communaute = ?', intval($id));

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('communaute', '*')
		->order('id_communaute ASC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findTopDistinguees($limite) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('communaute', '*')
		->where('points_communaute > 0')
		->order('points_communaute DESC')
		->limit($limite);
		
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCriteres($page = null, $nbMax = null, $ordre = null, $sens = null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('communaute')
		->from('braldun', 'count(*) as nb_membres')
		->where("id_communaute = id_fk_communaute_braldun")
		->group("id_communaute", "nom_communaute", "date_creation_communaute", "id_fk_braldun_gestionnaire_communaute", "description_communaute", "site_web_communaute" );

		if ($ordre != null && $sens != null) {
			$select->order($ordre.$sens);
		} else {
			$select->order("nom_communaute");
		}

		if ($page != null && $nbMax != null) {
			$select->limitPage($page, $nbMax);
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
