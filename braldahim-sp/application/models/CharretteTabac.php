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
class CharretteTabac extends Zend_Db_Table {
	protected $_name = 'charrette_tabac';
	protected $_primary = array('id_fk_braldun_charrette_tabac', 'id_fk_type_charrette_tabac');

	function findByIdCharrette($idCharrette) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_tabac', '*')
		->from('type_tabac', '*')
		->where('id_fk_charrette_tabac = '.intval($idCharrette))
		->where('charrette_tabac.id_fk_type_charrette_tabac = type_tabac.id_type_tabac');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_tabac', 'count(*) as nombre, 
		quantite_feuille_charrette_tabac as quantiteFeuille')
		->where('id_fk_type_charrette_tabac = ?',$data["id_fk_type_charrette_tabac"])
		->where('id_fk_charrette_tabac = ?',$data["id_fk_charrette_tabac"])
		->group(array('quantiteFeuille'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteFeuille = $resultat[0]["quantiteFeuille"];
			
			$dataUpdate['quantite_feuille_charrette_tabac']  = $quantiteFeuille;
			
			if (isset($data["quantite_feuille_charrette_tabac"])) {
				$dataUpdate['quantite_feuille_charrette_tabac'] = $quantiteFeuille + $data["quantite_feuille_charrette_tabac"];
			}
			
			$where = ' id_fk_type_charrette_tabac = '.$data["id_fk_type_charrette_tabac"];
			$where .= ' AND id_fk_charrette_tabac = '.$data["id_fk_charrette_tabac"];
			
			if ($dataUpdate['quantite_feuille_charrette_tabac'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
