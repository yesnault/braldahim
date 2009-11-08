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
class CharretteGraine extends Zend_Db_Table {
	protected $_name = 'charrette_graine';
	protected $_primary = array('id_fk_hobbit_charrette_graine', 'id_fk_type_charrette_graine');

	function findByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_graine', '*')
		->from('type_graine', '*')
		->where('id_fk_charrette_graine = '.intval($idCharrette))
		->where('charrette_graine.id_fk_type_charrette_graine = type_graine.id_type_graine');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_graine', 'count(*) as nombre, quantite_charrette_graine as quantite')
		->where('id_fk_type_charrette_graine = ?',$data["id_fk_type_charrette_graine"])
		->where('id_fk_charrette_graine = ?',$data["id_fk_charrette_graine"])
		->group(array('quantite', 'quantiteLingots'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			
			$dataUpdate['quantite_charrette_graine'] = $quantite;
			
			if (isset($data["quantite_charrette_graine"])) {
				$dataUpdate['quantite_charrette_graine'] = $quantite + $data["quantite_charrette_graine"];
			}
			
			$where = ' id_fk_type_charrette_graine = '.$data["id_fk_type_charrette_graine"];
			$where .= ' AND id_fk_charrette_graine = '.$data["id_fk_charrette_graine"];
			
			if ($dataUpdate['quantite_charrette_graine'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
