<?php

class EchoppePotionPartiePlante extends Zend_Db_Table {
	protected $_name = 'echoppe_potion_partieplante';
	protected $_primary = array("id_fk_type_echoppe_potion_partieplante","id_fk_type_plante_echoppe_potion_partieplante", "id_fk_echoppe_potion_partieplante");

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(
		'echoppe_potion_partieplante', 
		'count(*) as nombre, prix_echoppe_potion_partieplante as prix')
		->where('id_fk_type_echoppe_potion_partieplante = ?',$data["id_fk_type_echoppe_potion_partieplante"])
		->where('id_fk_type_plante_echoppe_potion_partieplante = ?',$data["id_fk_type_plante_echoppe_potion_partieplante"])
		->where('id_fk_echoppe_potion_partieplante = ?',$data["id_fk_echoppe_potion_partieplante"])
		->group(array('prix'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$prix = $resultat[0]["prix"];
			
			$prix = $prix + $data["prix_echoppe_potion_partieplante"];
			if ($prix < 0) $prix = 0;
			
			$dataUpdate = array(
			'prix_echoppe_potion_partieplante' => $prix,
			);
			$where = ' id_fk_type_echoppe_potion_partieplante = '.$data["id_fk_type_echoppe_potion_partieplante"];
			$where .= ' AND id_fk_type_plante_echoppe_potion_partieplante = '.$data["id_fk_type_plante_echoppe_potion_partieplante"];
			$where .= ' AND id_fk_echoppe_potion_partieplante = '.$data["id_fk_echoppe_potion_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}

}
