<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: LabanMunition.php 595 2008-11-09 11:21:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-09 12:21:27 +0100 (Sun, 09 Nov 2008) $
 * $LastChangedRevision: 595 $
 * $LastChangedBy: yvonnickesnault $
 */
class LabanMunition extends Zend_Db_Table {
	protected $_name = 'laban_munition';
	protected $_primary = array('id_fk_hobbit_laban_munition', 'id_fk_type_laban_munition');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_munition', '*')
		->from('type_munition', '*')
		->where('id_fk_hobbit_laban_munition = '.intval($id_hobbit))
		->where('laban_munition.id_fk_type_laban_munition = type_munition.id_type_munition');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_munition', 'count(*) as nombre, 
		quantite_laban_munition as quantite')
		->where('id_fk_type_laban_munition = ?',$data["id_fk_type_laban_munition"])
		->where('id_fk_hobbit_laban_munition = ?',$data["id_fk_hobbit_laban_munition"])
		->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			
			$dataUpdate['quantite_laban_munition']  = $quantite;
			
			if (isset($data["quantite_laban_munition"])) {
				$dataUpdate['quantite_laban_munition'] = $quantite + $data["quantite_laban_munition"];
			}
			
			$where = ' id_fk_type_laban_munition = '.$data["id_fk_type_laban_munition"];
			$where .= ' AND id_fk_hobbit_laban_munition = '.$data["id_fk_hobbit_laban_munition"];
			
			if ($dataUpdate['quantite_laban_munition'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
