<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class EchoppeMunition extends Zend_Db_Table {
	protected $_name = 'echoppe_munition';
	protected $_primary = array('id_fk_echoppe_echoppe_munition', 'id_fk_type_echoppe_munition');

	function findByIdEchoppe($idEchoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_munition', '*')
		->from('type_munition', '*')
		->where('id_fk_echoppe_echoppe_munition = ? ', intval($idEchoppe))
		->where('echoppe_munition.id_fk_type_echoppe_munition = type_munition.id_type_munition');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_munition', 'count(*) as nombre, 
		quantite_echoppe_munition as quantite')
		->where('id_fk_type_echoppe_munition = ?',$data["id_fk_type_echoppe_munition"])
		->where('id_fk_echoppe_echoppe_munition = ?',$data["id_fk_echoppe_echoppe_munition"])
		->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			
			$dataUpdate['quantite_echoppe_munition']  = $quantite;
			
			if (isset($data["quantite_echoppe_munition"])) {
				$dataUpdate['quantite_echoppe_munition'] = $quantite + $data["quantite_echoppe_munition"];
			}
			
			$where = ' id_fk_type_echoppe_munition = '.$data["id_fk_type_echoppe_munition"];
			$where .= ' AND id_fk_echoppe_echoppe_munition = '.$data["id_fk_echoppe_echoppe_munition"];
			
			if ($dataUpdate['quantite_echoppe_munition'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
