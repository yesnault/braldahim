<?php

class ElementPartieplante extends Zend_Db_Table {
	protected $_name = 'element_partieplante';
	protected $_primary = array('id_fk_type_element_partieplante', 'id_fk_type_plante_element_partieplante', 'x_element_partieplante', 'y_element_partieplante');
	
	function selectVue($x_min, $y_min, $x_max, $y_max) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('element_partieplante.id_fk_type_element_partieplante = type_partieplante.id_type_partieplante')
		->where('element_partieplante.id_fk_type_plante_element_partieplante = type_plante.id_type_plante')
		->where('x_element_partieplante <= ?', $x_max)
		->where('x_element_partieplante >= ?', $x_min)
		->where('y_element_partieplante <= ?', $y_max)
		->where('y_element_partieplante >= ?', $y_min)
		->order(array('nom_type_plante', 'nom_type_partieplante'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y) {
		return $this->selectVue($x, $y, $x, $y);
	}
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_partieplante', 'count(*) as nombre,  quantite_element_partieplante as quantiteBrute,  quantite_preparee_element_partieplante as quantitePreparee')
		->where('id_fk_type_element_partieplante = ?',$data["id_fk_type_element_partieplante"])
		->where('x_element_partieplante = ?',$data["x_element_partieplante"])
		->where('y_element_partieplante = ?',$data["y_element_partieplante"])
		->where('id_fk_type_plante_element_partieplante = ?',$data["id_fk_type_plante_element_partieplante"])
		->group(array('quantiteBrute', 'quantitePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrute = $resultat[0]["quantiteBrute"];
			$quantitePreparee = $resultat[0]["quantitePreparee"];
			
			$dataUpdate['quantite_element_partieplante']  = $quantiteBrute;
			$dataUpdate['quantite_preparee_element_partieplante']  = $quantitePreparee;
			
			if (isset($data["quantite_element_partieplante"])) {
				$dataUpdate = array('quantite_element_partieplante' => $quantiteBrute + $data["quantite_element_partieplante"]);
			};
			
			if (isset($data["quantite_preparee_element_partieplante"])) {
				$dataUpdate = array('quantite_preparee_element_partieplante' => $quantitePreparee + $data["quantite_preparee_element_partieplante"]);
			};
			
			$where = ' id_fk_type_element_partieplante = '.$data["id_fk_type_element_partieplante"];
			$where .= ' AND x_element_partieplante = '.$data["x_element_partieplante"];
			$where .= ' AND y_element_partieplante = '.$data["y_element_partieplante"];
			$where .= ' AND id_fk_type_plante_element_partieplante = '.$data["id_fk_type_plante_element_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
