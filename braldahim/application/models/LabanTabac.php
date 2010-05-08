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
class LabanTabac extends Zend_Db_Table {
	protected $_name = 'laban_tabac';
	protected $_primary = array('id_fk_braldun_laban_tabac', 'id_fk_type_laban_tabac');

	function findByIdBraldun($id_braldun) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_tabac', '*')
		->from('type_tabac', '*')
		->where('id_fk_braldun_laban_tabac = '.intval($id_braldun))
		->where('laban_tabac.id_fk_type_laban_tabac = type_tabac.id_type_tabac');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_tabac', 'count(*) as nombre, 
		quantite_feuille_laban_tabac as quantiteFeuille')
		->where('id_fk_type_laban_tabac = ?',$data["id_fk_type_laban_tabac"])
		->where('id_fk_braldun_laban_tabac = ?',$data["id_fk_braldun_laban_tabac"])
		->group(array('quantiteFeuille'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteFeuille = $resultat[0]["quantiteFeuille"];
			
			$dataUpdate['quantite_feuille_laban_tabac']  = $quantiteFeuille;
			
			if (isset($data["quantite_feuille_laban_tabac"])) {
				$dataUpdate['quantite_feuille_laban_tabac'] = $quantiteFeuille + $data["quantite_feuille_laban_tabac"];
			}
			
			$where = ' id_fk_type_laban_tabac = '.$data["id_fk_type_laban_tabac"];
			$where .= ' AND id_fk_braldun_laban_tabac = '.$data["id_fk_braldun_laban_tabac"];
			
			if ($dataUpdate['quantite_feuille_laban_tabac'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
