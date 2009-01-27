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
class ElementMunition extends Zend_Db_Table {
	protected $_name = 'element_munition';
	protected $_primary = array('x_element_munition',  'y_element_munition', 'id_fk_type_element_munition');

	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_munition', '*')
		->from('type_munition', '*')
		->where('element_munition.id_fk_type_element_munition = type_munition.id_type_munition')
		->where('x_element_munition <= ?',$x_max)
		->where('x_element_munition >= ?',$x_min)
		->where('y_element_munition <= ?',$y_max)
		->where('y_element_munition >= ?',$y_min);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		return $this->selectVue($x, $y, $x, $y);
	}
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_munition', 'count(*) as nombre, 
		quantite_element_munition as quantite')
		->where('id_fk_type_element_munition = ?',$data["id_fk_type_element_munition"])
		->where('x_element_munition = ?',$data["x_element_munition"])
		->where('y_element_munition = ?',$data["y_element_munition"])
		->group(array('quantite'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_2d10();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		$data["date_fin_element_munition"] = $dateFin;
		$dataUpdate["date_fin_element_munition"] = $dateFin;
		
		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			
			$dataUpdate['quantite_element_munition'] = $quantite;
			
			if (isset($data["quantite_element_munition"])) {
				$dataUpdate['quantite_element_munition'] = $quantite + $data["quantite_element_munition"];
			}
			
			$where = ' id_fk_type_element_munition = '.$data["id_fk_type_element_munition"];
			$where .= ' AND x_element_munition = '.$data["x_element_munition"];
			$where .= ' AND y_element_munition = '.$data["y_element_munition"];
			
			if ($dataUpdate['quantite_element_munition'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
