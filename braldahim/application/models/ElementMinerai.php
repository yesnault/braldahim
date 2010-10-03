<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class ElementMinerai extends Zend_Db_Table {
	protected $_name = 'element_minerai';
	protected $_primary = array('x_element_minerai',  'y_element_minerai', 'id_fk_type_element_minerai');

	function selectVue($x_min, $y_min, $x_max, $y_max, $z) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_minerai', '*')
		->from('type_minerai', '*')
		->where('element_minerai.id_fk_type_element_minerai = type_minerai.id_type_minerai')
		->where('x_element_minerai <= ?',$x_max)
		->where('x_element_minerai >= ?',$x_min)
		->where('y_element_minerai <= ?',$y_max)
		->where('y_element_minerai >= ?',$y_min)
		->where('z_element_minerai = ?',$z);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z) {
		return $this->selectVue($x, $y, $x, $y, $z);
	}
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_minerai', 'count(*) as nombre, 
		quantite_brut_element_minerai as quantiteBrut, 
		quantite_lingots_element_minerai as quantiteLingots')
		->where('id_fk_type_element_minerai = ?',$data["id_fk_type_element_minerai"])
		->where('x_element_minerai = ?',$data["x_element_minerai"])
		->where('y_element_minerai = ?',$data["y_element_minerai"])
		->group(array('quantiteBrut', 'quantiteLingots'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$dateCreation = date("Y-m-d H:i:s");
		$nbJours = Bral_Util_De::get_2d10();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);
		$data["date_fin_element_minerai"] = $dateFin;
		$dataUpdate["date_fin_element_minerai"] = $dateFin;
		
		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrut = $resultat[0]["quantiteBrut"];
			$quantiteLingots = $resultat[0]["quantiteLingots"];
			
			$dataUpdate['quantite_brut_element_minerai']  = $quantiteBrut;
			$dataUpdate['quantite_lingots_element_minerai']  = $quantiteLingots;
			
			if (isset($data["quantite_brut_element_minerai"])) {
				$dataUpdate['quantite_brut_element_minerai'] = $quantiteBrut + $data["quantite_brut_element_minerai"];
			}
			if (isset($data["quantite_lingots_element_minerai"])) {
				$dataUpdate['quantite_lingots_element_minerai'] = $quantiteLingots + $data["quantite_lingots_element_minerai"];
			}
			
			$where = ' id_fk_type_element_minerai = '.$data["id_fk_type_element_minerai"];
			$where .= ' AND x_element_minerai = '.$data["x_element_minerai"];
			$where .= ' AND y_element_minerai = '.$data["y_element_minerai"];
			
			if ($dataUpdate['quantite_brut_element_minerai'] <= 0 && $dataUpdate['quantite_lingots_element_minerai'] <= 0) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}

}
