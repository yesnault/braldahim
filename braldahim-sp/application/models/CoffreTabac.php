<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CoffreTabac extends Zend_Db_Table {
	protected $_name = 'coffre_tabac';
	protected $_primary = array('id_fk_coffre_coffre_tabac', 'id_fk_type_coffre_tabac');

	function findByIdCoffre($idCoffre) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_tabac', '*')
		->from('type_tabac', '*')
		->where('id_fk_coffre_coffre_tabac = ?', intval($idCoffre))
		->where('coffre_tabac.id_fk_type_coffre_tabac = type_tabac.id_type_tabac');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_tabac', 'count(*) as nombre, 
		quantite_feuille_coffre_tabac as quantiteFeuille')
		->where('id_fk_type_coffre_tabac = ?',$data["id_fk_type_coffre_tabac"])
		->where('id_fk_coffre_coffre_tabac = ?',$data["id_fk_coffre_coffre_tabac"])
		->group(array('quantiteFeuille'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteFeuille = $resultat[0]["quantiteFeuille"];
			
			$dataUpdate['quantite_feuille_coffre_tabac']  = $quantiteFeuille;
			
			if (isset($data["quantite_feuille_coffre_tabac"])) {
				$dataUpdate['quantite_feuille_coffre_tabac'] = $quantiteFeuille + $data["quantite_feuille_coffre_tabac"];
			}
			
			$where = ' id_fk_type_coffre_tabac = '.$data["id_fk_type_coffre_tabac"];
			$where .= ' AND id_fk_coffre_coffre_tabac = '.$data["id_fk_coffre_coffre_tabac"];
			
			if ($dataUpdate['quantite_feuille_coffre_tabac'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
