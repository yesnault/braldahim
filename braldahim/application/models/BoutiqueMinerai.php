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
class BoutiqueMinerai extends Zend_Db_Table {
	protected $_name = 'boutique_minerai';
	protected $_primary = array('id_fk_lieu_boutique_minerai', 'id_fk_type_boutique_minerai');

	function findByIdLieu($id_lieu) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_minerai', '*')
		->from('type_minerai', '*')
		->where('id_fk_lieu_boutique_minerai = '.intval($id_lieu))
		->where('boutique_minerai.id_fk_type_boutique_minerai = type_minerai.id_type_minerai');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('boutique_minerai', 'count(*) as nombre, 
		quantite_brut_boutique_minerai as quantiteBrut')
		->where('id_fk_type_boutique_minerai = ?',$data["id_fk_type_boutique_minerai"])
		->where('id_fk_lieu_boutique_minerai = ?',$data["id_fk_lieu_boutique_minerai"])
		->group(array('quantiteBrut'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrut = $resultat[0]["quantiteBrut"];
			
			$dataUpdate['quantite_brut_boutique_minerai']  = $quantiteBrut;
			
			if (isset($data["quantite_brut_boutique_minerai"])) {
				$dataUpdate['quantite_brut_boutique_minerai'] = $quantiteBrut + $data["quantite_brut_boutique_minerai"];
			}
			
			$where = ' id_fk_type_boutique_minerai = '.$data["id_fk_type_boutique_minerai"];
			$where .= ' AND id_fk_lieu_boutique_minerai = '.$data["id_fk_lieu_boutique_minerai"];
			
			if ($dataUpdate['quantite_brut_boutique_minerai'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
