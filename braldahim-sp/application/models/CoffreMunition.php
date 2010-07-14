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
class CoffreMunition extends Zend_Db_Table {
	protected $_name = 'coffre_munition';
	protected $_primary = array('id_fk_braldun_coffre_munition', 'id_fk_type_coffre_munition');

	function findByIdBraldun($id_braldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_munition', '*')
		->from('type_munition', '*')
		->where('id_fk_braldun_coffre_munition = '.intval($id_braldun))
		->where('coffre_munition.id_fk_type_coffre_munition = type_munition.id_type_munition');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_munition', 'count(*) as nombre, 
		quantite_coffre_munition as quantite')
		->where('id_fk_type_coffre_munition = ?',$data["id_fk_type_coffre_munition"])
		->where('id_fk_braldun_coffre_munition = ?',$data["id_fk_braldun_coffre_munition"])
		->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			
			$dataUpdate['quantite_coffre_munition']  = $quantite;
			
			if (isset($data["quantite_coffre_munition"])) {
				$dataUpdate['quantite_coffre_munition'] = $quantite + $data["quantite_coffre_munition"];
			}
			
			$where = ' id_fk_type_coffre_munition = '.$data["id_fk_type_coffre_munition"];
			$where .= ' AND id_fk_braldun_coffre_munition = '.$data["id_fk_braldun_coffre_munition"];
			
			if ($dataUpdate['quantite_coffre_munition'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
