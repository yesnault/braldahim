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
	}

	function findByCase($x, $y) {
		return $this->selectVue($x, $y, $x, $y);
	}
	
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_partieplante', 'count(*) as nombre, quantite_element_partieplante as quantite')
		->where('id_fk_type_element_partieplante = ?',$data["id_fk_type_element_partieplante"])
		->where('id_fk_hobbit_element_partieplante = ?',$data["id_fk_hobbit_element_partieplante"])
		->where('id_fk_type_plante_element_partieplante = ?',$data["id_fk_type_plante_element_partieplante"])
		->group('quantite');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate = array('quantite_element_partieplante' => $quantite + $data["quantite_element_partieplante"]);
			$where = ' id_fk_type_element_partieplante = '.$data["id_fk_type_element_partieplante"];
			$where .= ' AND id_fk_hobbit_element_partieplante = '.$data["id_fk_hobbit_element_partieplante"];
			$where .= ' AND id_fk_type_plante_element_partieplante = '.$data["id_fk_type_plante_element_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
