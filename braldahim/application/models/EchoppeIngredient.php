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
class EchoppeIngredient extends Zend_Db_Table {
	protected $_name = 'echoppe_ingredient';
	protected $_primary = array('id_fk_echoppe_echoppe_ingredient', 'id_fk_type_echoppe_ingredient');

	function findByIdEchoppe($id_echoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_ingredient', '*')
		->from('type_ingredient', '*')
		->where('id_fk_echoppe_echoppe_ingredient = '.intval($id_echoppe))
		->where('echoppe_ingredient.id_fk_type_echoppe_ingredient = type_ingredient.id_type_ingredient');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
		'echoppe_ingredient', 
		'count(*) as nombre, quantite_caisse_echoppe_ingredient as quantiteCaisse'
		.', quantite_arriere_echoppe_ingredient as quantiteArriere')
		->where('id_fk_type_echoppe_ingredient = ?',$data["id_fk_type_echoppe_ingredient"])
		->where('id_fk_echoppe_echoppe_ingredient = ?',$data["id_fk_echoppe_echoppe_ingredient"])
		->group(array('quantiteCaisse', 'quantiteArriere'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteCaisse = $resultat[0]["quantiteCaisse"];
			$quantiteArriere = $resultat[0]["quantiteArriere"];
			
			if (isset($data["quantite_caisse_echoppe_ingredient"])) {
				$quantiteCaisse = $quantiteCaisse + $data["quantite_caisse_echoppe_ingredient"];
			}
			if (isset($data["quantite_arriere_echoppe_ingredient"])) {
				$quantiteArriere = $quantiteArriere + $data["quantite_arriere_echoppe_ingredient"];
			}
			
			if ($quantiteCaisse < 0) $quantiteCaisse = 0;
			if ($quantiteArriere < 0) $quantiteArriere = 0;
			
			$dataUpdate = array(
				'quantite_caisse_echoppe_ingredient' => $quantiteCaisse,
				'quantite_arriere_echoppe_ingredient' => $quantiteArriere,
			);
			$where = ' id_fk_type_echoppe_ingredient = '.$data["id_fk_type_echoppe_ingredient"];
			$where .= ' AND id_fk_echoppe_echoppe_ingredient = '.$data["id_fk_echoppe_echoppe_ingredient"];
			
			if ($quantiteCaisse == 0 && $quantiteArriere == 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
			
			
		}
	}

}
