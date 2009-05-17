<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class CharretteMunition extends Zend_Db_Table {
	protected $_name = 'charrette_munition';
	protected $_primary = array('id_fk_hobbit_charrette_munition', 'id_fk_type_charrette_munition');

	function findByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_munition', '*')
		->from('type_munition', '*')
		->where('id_fk_charrette_munition = '.intval($idCharrette))
		->where('charrette_munition.id_fk_type_charrette_munition = type_munition.id_type_munition');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_munition', 'count(*) as nombre, 
		quantite_charrette_munition as quantite')
		->where('id_fk_type_charrette_munition = ?',$data["id_fk_type_charrette_munition"])
		->where('id_fk_charrette_munition = ?',$data["id_fk_charrette_munition"])
		->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			
			$dataUpdate['quantite_charrette_munition']  = $quantite;
			
			if (isset($data["quantite_charrette_munition"])) {
				$dataUpdate['quantite_charrette_munition'] = $quantite + $data["quantite_charrette_munition"];
			}
			
			$where = ' id_fk_type_charrette_munition = '.$data["id_fk_type_charrette_munition"];
			$where .= ' AND id_fk_charrette_munition = '.$data["id_fk_charrette_munition"];
			
			if ($dataUpdate['quantite_charrette_munition'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
