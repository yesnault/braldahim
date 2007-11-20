<?php

class CaissePartieplante extends Zend_Db_Table {
	protected $_name = 'caisse_partieplante';
	protected $_primary = array('id_fk_type_caisse_partieplante', 'id_fk_echoppe_caisse_partieplante');
	
    function findByIdEchoppe($id_fk_echoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('caisse_partieplante', '*')
		->from('type_partieplante', '*')
		->where('id_fk_echoppe_caisse_partieplante = '.intval($id_fk_echoppe))
		->where('caisse_partieplante.id_fk_type_caisse_partieplante = type_partieplante.id_type_partieplante');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('caisse_partieplante', 'count(*) as nombre, quantite_caisse_partieplante as quantite')
		->where('id_fk_type_caisse_partieplante = ?',$data["id_fk_type_caisse_partieplante"])
		->where('id_fk_echoppe_caisse_partieplante = ?',$data["id_fk_echoppe_caisse_partieplante"])
		->group('quantite');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantite = $resultat[0]["quantite"];
			$dataUpdate = array('quantite_caisse_partieplante' => $quantite + $data["quantite_caisse_partieplante"]);
			$where = ' id_fk_type_caisse_partieplante = '.$data["id_fk_type_caisse_partieplante"];
			$where .= ' AND id_fk_echoppe_caisse_partieplante = '.$data["id_fk_echoppe_caisse_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
