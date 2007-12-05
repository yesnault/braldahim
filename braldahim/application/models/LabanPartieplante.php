<?php

class LabanPartieplante extends Zend_Db_Table {
	protected $_name = 'laban_partieplante';
	protected $_primary = array('id_fk_type_laban_partieplante', 'id_hobbit_laban_partieplante');
	
    function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('id_fk_hobbit_laban_partieplante = '.intval($id_hobbit))
		->where('laban_partieplante.id_fk_type_laban_partieplante = type_partieplante.id_type_partieplante')
		->where('laban_partieplante.id_fk_type_plante_laban_partieplante = type_plante.id_type_plante')
		->order(array('nom_type_plante', 'nom_type_partieplante'));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_partieplante', 'count(*) as nombre, quantite_laban_partieplante as quantite')
		->where('id_fk_type_laban_partieplante = ?',$data["id_fk_type_laban_partieplante"])
		->where('id_fk_hobbit_laban_partieplante = ?',$data["id_fk_hobbit_laban_partieplante"])
		->where('id_fk_type_plante_laban_partieplante = ?',$data["id_fk_type_plante_laban_partieplante"])
		->group('quantite');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate = array('quantite_laban_partieplante' => $quantite + $data["quantite_laban_partieplante"]);
			$where = ' id_fk_type_laban_partieplante = '.$data["id_fk_type_laban_partieplante"];
			$where .= ' AND id_fk_hobbit_laban_partieplante = '.$data["id_fk_hobbit_laban_partieplante"];
			$where .= ' AND id_fk_type_plante_laban_partieplante = '.$data["id_fk_type_plante_laban_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
