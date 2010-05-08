<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class BraldunsDistinction extends Zend_Db_Table {
	protected $_name = 'bralduns_distinction';
	protected $_primary = array('id_hdistinction');

	function findDistinctionsByBraldunId($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_distinction', '*')
		->from('type_distinction', '*')
		->from('type_categorie', '*')
		->where('id_fk_braldun_hdistinction = ? ', intval($idBraldun))
		->where('id_fk_type_distinction_hdistinction = id_type_distinction')
		->where('id_fk_type_categorie_distinction = id_type_categorie')
		->order(array('ordre_type_categorie', 'date_hdistinction'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findDistinctionsByBraldunIdAndIdTypeDistinction($idBraldun, $idTypeDistinction) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_distinction', '*')
		->from('type_distinction', '*')
		->where('id_fk_braldun_hdistinction = ? ', intval($idBraldun))
		->where('id_fk_type_distinction_hdistinction = id_type_distinction')
		->where('id_type_distinction = ?', intval($idTypeDistinction))
		->order('date_hdistinction');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findDistinctionsByBraldunIdAndIdFkLieuDistinction($idBraldun, $idLieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_distinction', '*')
		->from('type_distinction', '*')
		->where('id_fk_braldun_hdistinction = ? ', intval($idBraldun))
		->where('id_fk_type_distinction_hdistinction = id_type_distinction')
		->where('id_fk_lieu_type_distinction = ?', intval($idLieu))
		->order('date_hdistinction');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countDistinctionByIdBraldunList($listId, $idTypeDistinction) {
		if ($listId == null) {
			return null;
		}
		
		$nomChamp = "id_fk_braldun_hdistinction";
		$liste = "";
		foreach($listId as $id) {
			if ((int) $id."" == $id."") {
				if ($liste == "") {
					$liste = $id;
				} else {
					$liste = $liste." OR ".$nomChamp."=".$id;
				}
			}
		}
			
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('bralduns_distinction', array('count(*) as nombre', 'id_fk_braldun_hdistinction'))
		->where('id_fk_type_distinction_hdistinction = ?', intval($idTypeDistinction))
		->where('id_fk_braldun_hdistinction = '.$liste)
		->group('id_fk_braldun_hdistinction');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}