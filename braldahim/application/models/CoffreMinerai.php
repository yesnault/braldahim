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
class CoffreMinerai extends Zend_Db_Table {
	protected $_name = 'coffre_minerai';
	protected $_primary = array('id_fk_hobbit_coffre_minerai', 'id_fk_type_coffre_minerai');

	function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_hobbit_coffre_minerai = '.intval($id_hobbit))
		->where('coffre_minerai.id_fk_type_coffre_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_minerai', 'count(*) as nombre, 
		quantite_brut_coffre_minerai as quantiteBrut, 
		quantite_lingots_coffre_minerai as quantiteLingots')
		->where('id_fk_type_coffre_minerai = ?',$data["id_fk_type_coffre_minerai"])
		->where('id_fk_hobbit_coffre_minerai = ?',$data["id_fk_hobbit_coffre_minerai"])
		->group(array('quantiteBrut', 'quantiteLingots'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrut = $resultat[0]["quantiteBrut"];
			$quantiteLingots = $resultat[0]["quantiteLingots"];
			
			$dataUpdate['quantite_brut_coffre_minerai']  = $quantiteBrut;
			$dataUpdate['quantite_lingots_coffre_minerai']  = $quantiteLingots;
			
			if (isset($data["quantite_brut_coffre_minerai"])) {
				$dataUpdate['quantite_brut_coffre_minerai'] = $quantiteBrut + $data["quantite_brut_coffre_minerai"];
			}
			if (isset($data["quantite_lingots_coffre_minerai"])) {
				$dataUpdate['quantite_lingots_coffre_minerai'] = $quantiteLingots + $data["quantite_lingots_coffre_minerai"];
			}
			
			$where = ' id_fk_type_coffre_minerai = '.$data["id_fk_type_coffre_minerai"];
			$where .= ' AND id_fk_hobbit_coffre_minerai = '.$data["id_fk_hobbit_coffre_minerai"];
			
			if ($dataUpdate['quantite_brut_coffre_minerai'] <= 0 && $dataUpdate['quantite_lingots_coffre_minerai'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
