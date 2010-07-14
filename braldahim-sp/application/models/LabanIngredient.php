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
class LabanIngredient extends Zend_Db_Table {
	protected $_name = 'laban_ingredient';
	protected $_primary = array('id_fk_braldun_laban_ingredient', 'id_fk_type_laban_ingredient');

	function findByIdBraldun($idBraldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_ingredient', '*')
		->from('type_ingredient', '*')
		->where('id_fk_braldun_laban_ingredient = ?', intval($idBraldun))
		->where('laban_ingredient.id_fk_type_laban_ingredient = type_ingredient.id_type_ingredient');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_ingredient', 'count(*) as nombre, quantite_laban_ingredient as quantite')
		->where('id_fk_type_laban_ingredient = ?',$data["id_fk_type_laban_ingredient"])
		->where('id_fk_braldun_laban_ingredient = ?',$data["id_fk_braldun_laban_ingredient"])
		->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			
			$dataUpdate['quantite_laban_ingredient']  = $quantite;
			
			if (isset($data["quantite_laban_ingredient"])) {
				$dataUpdate['quantite_laban_ingredient'] = $quantite + $data["quantite_laban_ingredient"];
			}
			
			$where = ' id_fk_type_laban_ingredient = '.$data["id_fk_type_laban_ingredient"];
			$where .= ' AND id_fk_braldun_laban_ingredient = '.$data["id_fk_braldun_laban_ingredient"];
			
			if ($dataUpdate['quantite_laban_ingredient'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
