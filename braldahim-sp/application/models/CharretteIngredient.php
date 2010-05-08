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
class CharretteIngredient extends Zend_Db_Table {
	protected $_name = 'charrette_ingredient';
	protected $_primary = array('id_fk_braldun_charrette_ingredient', 'id_fk_type_charrette_ingredient');

	function findByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_ingredient', '*')
		->from('type_ingredient', '*')
		->where('id_fk_charrette_ingredient = ?', intval($idCharrette))
		->where('charrette_ingredient.id_fk_type_charrette_ingredient = type_ingredient.id_type_ingredient');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function countByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_ingredient', 'count(*) as nombre')
		->where('id_fk_charrette_ingredient = '.intval($idCharrette));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_ingredient', 'count(*) as nombre, quantite_charrette_ingredient as quantite')
		->where('id_fk_type_charrette_ingredient = ?',$data["id_fk_type_charrette_ingredient"])
		->where('id_fk_charrette_ingredient = ?',$data["id_fk_charrette_ingredient"])
		->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
				
			$dataUpdate['quantite_charrette_ingredient'] = $quantite;
				
			if (isset($data["quantite_charrette_ingredient"])) {
				$dataUpdate['quantite_charrette_ingredient'] = $quantite + $data["quantite_charrette_ingredient"];
			}
				
			$where = ' id_fk_type_charrette_ingredient = '.$data["id_fk_type_charrette_ingredient"];
			$where .= ' AND id_fk_charrette_ingredient = '.$data["id_fk_charrette_ingredient"];
				
			if ($dataUpdate['quantite_charrette_ingredient'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
